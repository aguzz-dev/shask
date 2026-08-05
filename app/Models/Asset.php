<?php

namespace App\Models;

use App\Database;

class Asset extends Database
{
    protected $table = 'assets';

    /**
     * Brand attribution mask: brand-creator submissions display as "Shhask"
     * instead of the real handle. Computed in the SQL projection — no fake
     * "Shhask" user row is ever created; submitter_user_id always points at
     * the real creator's id.
     */
    private const SUBMITTER_USERNAME_MASK_SQL =
        "CASE WHEN u.is_brand_creator = 1 THEN 'Shhask' ELSE u.username END AS submitter_username";

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
     * $forceStatus (additivo, official-brand-designs / PR2): cuando se pasa,
     * omite por completo la regla "primer asset → pending" y usa ese status
     * directo. Pensado para el import de diseños de marca (brand:import-designs),
     * que necesita insertar como 'approved' aunque sea el primer asset del
     * submitter de marca. Se verifica con un assert post-insert: si la fila
     * no quedó exactamente en $forceStatus, se lanza una excepción — la
     * auditoría de una importación nunca debe reportar éxito silencioso sobre
     * un status que en realidad no se aplicó.
     *
     * @return int  ID del registro creado
     */
    public function createPublicAsset(
        string $title,
        array  $colors,
        string $icon,
        string $background,
        ?array $canvas = null,
        ?int   $submitterUserId = null,
        ?string $forceStatus = null
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

            if ($forceStatus === null) {
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
        }

        if ($forceStatus !== null) {
            $status = $forceStatus;
        }

        // NEW assets get a real created_at stamp (NOW() — no user input,
        // safe to interpolate as a literal SQL function call). Legacy rows
        // predating the created_at migration stay permanently NULL because
        // the column has no schema-level default (see marketplace-item-showcase
        // migration 2026_07_13_191321) — this INSERT is the only place a
        // value is ever written for NEW rows.
        if ($canvasJson !== null) {
            $this->query(
                "INSERT INTO public_assets
                    (title, color, icon, background, canvas, submitter_user_id, status, created_at)
                 VALUES
                    ('{$titleEsc}', '{$colorsJson}', '{$iconEsc}', '{$bgEsc}',
                     '{$canvasJson}', {$submitterSql}, '{$status}', NOW())"
            );
        } else {
            $this->query(
                "INSERT INTO public_assets
                    (title, color, icon, background, submitter_user_id, status, created_at)
                 VALUES
                    ('{$titleEsc}', '{$colorsJson}', '{$iconEsc}', '{$bgEsc}',
                     {$submitterSql}, '{$status}', NOW())"
            );
        }

        $newId = (int) $this->query("SELECT LAST_INSERT_ID() as id")->fetch_assoc()['id'];

        if ($forceStatus !== null) {
            $actualStatus = $this->query(
                "SELECT status FROM public_assets WHERE id = {$newId}"
            )->fetch_assoc()['status'];

            if ($actualStatus !== $forceStatus) {
                throw new \Exception(
                    "createPublicAsset(): forced status '{$forceStatus}' did not persist for asset #{$newId} (got '{$actualStatus}')"
                );
            }
        }

        return $newId;
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
        $mask = self::SUBMITTER_USERNAME_MASK_SQL;

        return $this->query(
            "SELECT pa.*, {$mask}
             FROM public_assets pa
             LEFT JOIN users u ON u.id = pa.submitter_user_id
             WHERE pa.status IN ('reported', 'pending')
             ORDER BY pa.id DESC"
        )->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * All categories ordered by position then id.
     */
    public function getCategories(): array
    {
        $stmt = $this->dbConnection->prepare(
            "SELECT id, slug, name FROM categories ORDER BY position ASC, id ASC"
        );
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    /**
     * Public catalog with optional filters.
     * All filter values are bound — SQL injection is impossible.
     *
     * @param string|null $q           Title LIKE search (case-insensitive).
     * @param string|null $categorySlug Category slug to filter by (JOIN on categories).
     * @param string|null $sort         'trending' → ranked by 7-day acquisition momentum
     *                                  (recent_acquisitions DESC), falling back to
     *                                  downloads_count DESC for cold-start padding.
     * @param bool        $featured     true → only is_featured = 1.
     * @param int|null    $limit        Bound int, applied after ORDER BY. Null → no LIMIT (current behavior).
     * @param int|null    $offset       Bound int, applied after LIMIT. Ignored if $limit is null.
     */
    public function getPublicCatalog(
        ?string $q = null,
        ?string $categorySlug = null,
        ?string $sort = null,
        bool    $featured = false,
        ?int    $limit = null,
        ?int    $offset = null
    ): array {
        $trending = $sort === 'trending';
        $mask     = self::SUBMITTER_USERNAME_MASK_SQL;

        if ($trending) {
            // Momentum branch: LEFT JOIN a 7-day acquisition sub-count onto
            // public_assets. COALESCE pads assets with zero recent
            // acquisitions to 0 instead of NULL. `recent_acquisitions` is
            // additive JSON — it only appears on THIS branch's response.
            // The window param binds FIRST (it sits inside the subquery,
            // which appears before WHERE in the SQL text).
            $windowDays = (int) config('marketplace.trending_window_days', 7);

            $sql    = "SELECT pa.*, COALESCE(m.recent_acquisitions, 0) AS recent_acquisitions, {$mask}
                       FROM public_assets pa
                       LEFT JOIN (
                           SELECT asset_id, COUNT(*) AS recent_acquisitions
                           FROM asset_acquisitions
                           WHERE created_at >= (NOW() - INTERVAL ? DAY)
                           GROUP BY asset_id
                       ) m ON m.asset_id = pa.id
                       LEFT JOIN users u ON u.id = pa.submitter_user_id";
            $types  = 'i';
            $params = [$windowDays];

            if ($categorySlug !== null) {
                $sql .= ' INNER JOIN categories c ON c.id = pa.category_id';
            }

            $sql .= " WHERE pa.status IN ('pending','approved') AND pa.submitter_user_id IS NOT NULL";

            if ($categorySlug !== null) {
                $sql      .= ' AND c.slug = ?';
                $types    .= 's';
                $params[] = $categorySlug;
            }
        } elseif ($categorySlug !== null) {
            // Use INNER JOIN to filter by category slug.
            // Only user-submitted designs (submitter_user_id IS NOT NULL); system presets are excluded.
            $sql    = "SELECT pa.*, {$mask}
                       FROM public_assets pa
                       INNER JOIN categories c ON c.id = pa.category_id
                       LEFT JOIN users u ON u.id = pa.submitter_user_id
                       WHERE pa.status IN ('pending','approved')
                         AND pa.submitter_user_id IS NOT NULL
                         AND c.slug = ?";
            $types  = 's';
            $params = [$categorySlug];
        } else {
            // System presets (submitter_user_id IS NULL) are editor ingredients, not catalog items.
            $sql    = "SELECT pa.*, {$mask}
                       FROM public_assets pa
                       LEFT JOIN users u ON u.id = pa.submitter_user_id
                       WHERE pa.status IN ('pending','approved') AND pa.submitter_user_id IS NOT NULL";
            $types  = '';
            $params = [];
        }

        // All three branches now select from an aliased `pa` table uniformly
        // (normalized so the LEFT JOIN users mask applies the same way
        // regardless of which filter combination is active).
        $pa = 'pa.';

        if ($featured) {
            $sql    .= " AND {$pa}is_featured = 1";
        }

        if ($q !== null && $q !== '') {
            $col     = "{$pa}title";
            $sql    .= " AND {$col} LIKE ?";
            $types  .= 's';
            $params[] = '%' . $q . '%';
        }

        // ORDER BY — whitelisted, never interpolated from user input
        $orderCol = "{$pa}downloads_count";
        $idCol    = "{$pa}id";
        if ($trending) {
            // Momentum first; downloads_count DESC is the cold-start padding
            // fallback that fills the rail when few items moved this week.
            $sql .= " ORDER BY recent_acquisitions DESC, {$orderCol} DESC, {$idCol} DESC";
        } else {
            $sql .= " ORDER BY {$idCol} DESC";
        }

        // Pagination — always bound int, never interpolated. Appended after
        // ORDER BY so the offset is computed against the final, deterministic
        // ordering. $limit === null preserves the current (unbounded) behavior.
        if ($limit !== null) {
            $sql      .= ' LIMIT ?';
            $types    .= 'i';
            $params[] = (int) $limit;

            if ($offset !== null) {
                $sql      .= ' OFFSET ?';
                $types    .= 'i';
                $params[] = (int) $offset;
            }
        }

        $stmt = $this->dbConnection->prepare($sql);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Server-driven price (marketplace-surface / Price Is Always
        // Server-Driven): public_assets has no `price` column, so stamp it
        // from config on every row instead of exposing a client-side
        // fallback constant.
        $price = (int) config('marketplace.acquisition_hype_cost', 20);
        foreach ($result as &$row) {
            $row['price'] = $price;
        }
        unset($row);

        return $result;
    }

    /**
     * Creator stats aggregated from public_assets and the acquisitions ledger.
     *
     * Returns downloads, hype earned per design, and the creator's current
     * hype balance from the users table.
     */
    public function getCreatorStats(int $userId): array
    {
        // Per-design breakdown: downloads and hype earned from ledger
        $stmt = $this->dbConnection->prepare(
            "SELECT pa.id,
                    pa.title,
                    pa.status,
                    pa.downloads_count,
                    COALESCE(SUM(acq.hype_minted), 0) AS hype_earned
             FROM public_assets pa
             LEFT JOIN asset_acquisitions acq ON acq.asset_id = pa.id
             WHERE pa.submitter_user_id = ?
             GROUP BY pa.id, pa.title, pa.status, pa.downloads_count
             ORDER BY pa.id DESC"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $designs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Current hype balance
        $hypeStmt = $this->dbConnection->prepare(
            "SELECT hype FROM users WHERE id = ?"
        );
        $hypeStmt->bind_param('i', $userId);
        $hypeStmt->execute();
        $hypeRow = $hypeStmt->get_result()->fetch_assoc();
        $hypeStmt->close();

        $totalDownloads = (int) array_sum(array_column($designs, 'downloads_count'));
        $totalHype      = $hypeRow ? (int) $hypeRow['hype'] : 0;

        // Normalize types
        $designs = array_map(static function (array $d): array {
            return [
                'id'             => (int) $d['id'],
                'title'          => $d['title'],
                'status'         => $d['status'],
                'downloads_count' => (int) $d['downloads_count'],
                'hype_earned'    => (int) $d['hype_earned'],
            ];
        }, $designs);

        return [
            'total_downloads' => $totalDownloads,
            'total_hype'      => $totalHype,
            'designs_count'   => count($designs),
            'designs'         => $designs,
        ];
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
