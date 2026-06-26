<?php

namespace App\Models;

use App\Database;

class Asset extends Database
{
    protected $table = 'assets';

    public function findById($id)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE id = {$id}")->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Catálogo público: solo assets con status visible (pending o approved).
     * Los assets rejected/reported/removed quedan excluidos.
     */
    public function getAllAssets(): array
    {
        $publicAssets = $this->query(
            "SELECT * FROM public_assets
             WHERE status IN ('pending', 'approved')
             ORDER BY id DESC"
        )->fetch_all(MYSQLI_ASSOC);

        $privateAssets = $this->query("SELECT * FROM {$this->table}")->fetch_all(MYSQLI_ASSOC);

        return [
            'public_assets' => $publicAssets,
            'assets'        => $privateAssets,
        ];
    }

    /**
     * Assets del usuario: assets privados adquiridos + assets públicos visibles
     * + los propios diseños UGC del usuario en todos sus estados.
     */
    public function getUserAssetsByUserId(int $id): array
    {
        $userAssets = $this->query(
            "SELECT a.*
             FROM assets a
             INNER JOIN asset_user ua ON a.id = ua.asset_id
             WHERE ua.user_id = '{$id}'"
        )->fetch_all(MYSQLI_ASSOC);

        // Catálogo público visible para el selector de temas
        $publicAssets = $this->query(
            "SELECT * FROM public_assets
             WHERE status IN ('pending', 'approved')
             ORDER BY id DESC"
        )->fetch_all(MYSQLI_ASSOC);

        // Propios diseños UGC del usuario con todos los estados (para "mis diseños")
        $stmt = $this->dbConnection->prepare(
            "SELECT * FROM public_assets WHERE submitter_user_id = ? ORDER BY id DESC"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $userDesigns = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return [
            'public_assets' => $publicAssets,
            'user_designs'  => $userDesigns,
            'assets'        => $userAssets,
        ];
    }

    /**
     * Crea un asset público.
     *
     * Si $submitterUserId no es null, el asset es UGC. El status depende de si
     * es el primer asset del creador: primer asset → pending (revisión prioritaria),
     * assets siguientes → approved (post-moderación visible inmediatamente).
     *
     * Si $submitterUserId es null, el asset es del sistema (admin) → approved.
     *
     * @return int  ID del registro creado
     */
    public function createPublicAsset(
        string $title,
        array  $colors,
        string $icon,
        string $background,
        ?array $canvas = null,
        ?int   $submitterUserId = null
    ): int {
        $colorsJson  = $this->dbConnection->real_escape_string(json_encode($colors));
        $titleEsc    = $this->dbConnection->real_escape_string($title ?? '');
        $iconEsc     = $this->dbConnection->real_escape_string($icon ?? '');
        $bgEsc       = $this->dbConnection->real_escape_string($background ?? '');
        $canvasJson  = $canvas !== null
            ? $this->dbConnection->real_escape_string(json_encode($canvas))
            : null;

        // Determina el status inicial según el tipo de submitter
        $status          = 'approved';
        $submitterSql    = 'NULL';

        if ($submitterUserId !== null) {
            $submitterInt = (int) $submitterUserId;
            $submitterSql = (string) $submitterInt;

            // Primer asset del creador → pending (flag de revisión prioritaria)
            $countStmt = $this->dbConnection->prepare(
                "SELECT COUNT(*) AS c FROM public_assets WHERE submitter_user_id = ?"
            );
            $countStmt->bind_param('i', $submitterInt);
            $countStmt->execute();
            $existing = (int) $countStmt->get_result()->fetch_assoc()['c'];
            $countStmt->close();

            $status = $existing === 0 ? 'pending' : 'approved';
        }

        if ($canvasJson !== null) {
            $this->query(
                "INSERT INTO public_assets
                    (title, color, icon, background, canvas, submitter_user_id, status)
                 VALUES
                    ('{$titleEsc}', '{$colorsJson}', '{$iconEsc}', '{$bgEsc}',
                     '{$canvasJson}', {$submitterSql}, '{$status}')"
            );
        } else {
            $this->query(
                "INSERT INTO public_assets
                    (title, color, icon, background, submitter_user_id, status)
                 VALUES
                    ('{$titleEsc}', '{$colorsJson}', '{$iconEsc}', '{$bgEsc}',
                     {$submitterSql}, '{$status}')"
            );
        }

        return (int) $this->query("SELECT LAST_INSERT_ID() as id")->fetch_assoc()['id'];
    }

    /**
     * Actualiza un asset público. Solo el propietario puede modificarlo.
     *
     * @throws \Exception Con código 403 si $callerUserId no es el propietario.
     * @return int ID del asset actualizado
     */
    public function updatePublicAsset(
        int    $id,
        string $title,
        array  $colors,
        string $icon,
        string $background,
        ?array $canvas = null,
        ?int   $callerUserId = null
    ): int {
        $idInt = (int) $id;

        // Verificación de ownership cuando hay un caller identificado
        if ($callerUserId !== null) {
            $row = $this->query(
                "SELECT submitter_user_id FROM public_assets WHERE id = {$idInt}"
            )->fetch_assoc();

            if ($row && $row['submitter_user_id'] !== null
                && (int) $row['submitter_user_id'] !== (int) $callerUserId
            ) {
                throw new \Exception('No autorizado: no sos el propietario de este asset', 403);
            }
        }

        $colorsJson = $this->dbConnection->real_escape_string(json_encode($colors));
        $titleEsc   = $this->dbConnection->real_escape_string($title ?? '');
        $iconEsc    = $this->dbConnection->real_escape_string($icon ?? '');
        $bgEsc      = $this->dbConnection->real_escape_string($background ?? '');
        $canvasSql  = $canvas !== null
            ? "'" . $this->dbConnection->real_escape_string(json_encode($canvas)) . "'"
            : 'NULL';

        $this->query(
            "UPDATE public_assets
             SET title = '{$titleEsc}',
                 color = '{$colorsJson}',
                 icon  = '{$iconEsc}',
                 background = '{$bgEsc}',
                 canvas = {$canvasSql}
             WHERE id = {$idInt}"
        );

        return $idInt;
    }

    /**
     * Marca un asset como reportado. El asset deja de aparecer en el catálogo
     * público hasta que un admin lo revise.
     */
    public function reportAsset(int $assetId): void
    {
        $id   = (int) $assetId;
        $stmt = $this->dbConnection->prepare(
            "UPDATE public_assets SET status = 'reported' WHERE id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Takedown administrativo: el asset queda removido de todos los listados.
     */
    public function takedownAsset(int $assetId): void
    {
        $id   = (int) $assetId;
        $stmt = $this->dbConnection->prepare(
            "UPDATE public_assets SET status = 'removed' WHERE id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Aprueba un asset (restaura su visibilidad en el catálogo).
     */
    public function approveAsset(int $assetId): void
    {
        $id   = (int) $assetId;
        $stmt = $this->dbConnection->prepare(
            "UPDATE public_assets SET status = 'approved' WHERE id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Assets en cola de moderación (reportados + pendientes de revisión).
     */
    public function getModerationQueue(): array
    {
        return $this->query(
            "SELECT pa.*, u.username AS submitter_username
             FROM public_assets pa
             LEFT JOIN users u ON u.id = pa.submitter_user_id
             WHERE pa.status IN ('reported', 'pending')
             ORDER BY pa.id DESC"
        )->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Designs aprobados de un creador específico (para el perfil público).
     */
    public function getApprovedBySubmitter(int $userId): array
    {
        $stmt = $this->dbConnection->prepare(
            "SELECT * FROM public_assets
             WHERE submitter_user_id = ? AND status = 'approved'
             ORDER BY id DESC"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }
}
