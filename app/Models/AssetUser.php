<?php

namespace App\Models;

use App\Database;
use DateTime;

class AssetUser extends Database
{
    protected $table = 'asset_user';

    /**
     * Adquiere un asset para un usuario.
     *
     * Para assets UGC (public_assets con submitter_user_id):
     *   - source='hype' → debita acquisition_hype_cost al comprador.
     *   - source='ad'   → sin débito de hype al comprador.
     *   - En ambos casos: mintea creator_hype_mint al creador (generado, no P2P).
     *
     * Para assets del sistema (private): verifica hype del comprador y debita.
     *
     * @throws \Exception Si el asset ya es propiedad del usuario.
     * @throws \Exception Si el comprador no tiene hype suficiente (ruta hype).
     *
     * @return int|null El id del creador a notificar (D4.1), o null para
     *                   assets del sistema o cuando `submitter_user_id` es null.
     */
    public function buyAsset(int $assetId, int $userId, string $source = 'hype'): ?int
    {
        $assetIdInt = (int) $assetId;
        $userIdInt  = (int) $userId;

        // Verificar que no está duplicado
        $owned = $this->query(
            "SELECT COUNT(*) AS c FROM {$this->table}
             WHERE asset_id = {$assetIdInt} AND user_id = {$userIdInt}"
        )->fetch_assoc();
        if ((int) $owned['c'] > 0) {
            throw new \Exception('El asset ya pertenece a este usuario');
        }

        // Determinar si es un asset UGC (public_assets) o del sistema (assets)
        $publicAsset = $this->query(
            "SELECT submitter_user_id FROM public_assets WHERE id = {$assetIdInt}"
        )->fetch_assoc();

        if ($publicAsset !== false && $publicAsset !== null) {
            // Ruta UGC: usar users.hype
            $creatorId = $publicAsset['submitter_user_id'] !== null
                ? (int) $publicAsset['submitter_user_id']
                : null;
            $this->_buyUgcAsset($assetIdInt, $userIdInt, $creatorId, $source);
            return $creatorId;
        }

        // Ruta sistema/privado: usar users.hype (corregido desde users.coins)
        $this->_buySystemAsset($assetIdInt, $userIdInt, $source);
        return null;
    }

    /**
     * Adquisición de asset UGC (de public_assets).
     * Débito al comprador + mint al creador.
     *
     * All mutations run inside a single transaction. If the ledger INSERT into
     * asset_acquisitions fails, the entire transaction is rolled back so the
     * buyer's hype is never debited without a matching acquisition record.
     */
    private function _buyUgcAsset(int $assetId, int $userId, ?int $creatorId, string $source): void
    {
        $cost = (int) config('marketplace.acquisition_hype_cost', 20);
        $mint = (int) config('marketplace.creator_hype_mint', 15);

        if ($source === 'hype') {
            // Verify buyer balance before opening the transaction
            $buyerRow  = $this->query("SELECT hype FROM users WHERE id = {$userId}")->fetch_assoc();
            $buyerHype = (int) ($buyerRow['hype'] ?? 0);
            if ($buyerHype < $cost) {
                throw new \Exception('No tenés suficiente hype para adquirir este diseño', 402);
            }
        }

        $this->dbConnection->begin_transaction();

        try {
            if ($source === 'hype') {
                // Debit buyer hype
                $stmtDebit = $this->dbConnection->prepare(
                    "UPDATE users SET hype = hype - ? WHERE id = ?"
                );
                $stmtDebit->bind_param('ii', $cost, $userId);
                $stmtDebit->execute();
                $stmtDebit->close();
            }

            // Mint hype to creator (platform-generated, not P2P)
            if ($creatorId !== null) {
                $stmtMint = $this->dbConnection->prepare(
                    "UPDATE users SET hype = hype + ? WHERE id = ?"
                );
                $stmtMint->bind_param('ii', $mint, $creatorId);
                $stmtMint->execute();
                $stmtMint->close();
            }

            // Increment download counter
            $this->query(
                "UPDATE public_assets SET downloads_count = downloads_count + 1 WHERE id = {$assetId}"
            );

            // Register acquisition in asset_user
            $this->_insertAssetUser($assetId, $userId);

            // Insert ledger record — additive only, never touches existing mint logic
            $stmtLedger = $this->dbConnection->prepare(
                "INSERT INTO asset_acquisitions (asset_id, buyer_user_id, source, hype_minted)
                 VALUES (?, ?, ?, ?)"
            );
            // Creator always receives hype_minted regardless of source (hype or ad)
            $stmtLedger->bind_param('iisi', $assetId, $userId, $source, $mint);
            $stmtLedger->execute();
            $stmtLedger->close();

            $this->dbConnection->commit();
        } catch (\Throwable $e) {
            $this->dbConnection->rollback();
            throw $e;
        }
    }

    /**
     * Adquisición de asset del sistema (de la tabla assets).
     * Usa users.hype (reemplaza el campo users.coins que no existe).
     */
    private function _buySystemAsset(int $assetId, int $userId, string $source): void
    {
        $assetRows = $this->query("SELECT price FROM assets WHERE id = {$assetId}")->fetch_all(MYSQLI_ASSOC);
        if (empty($assetRows)) {
            throw new \Exception('Asset no encontrado', 404);
        }
        $price = (int) $assetRows[0]['price'];

        if ($source === 'hype' && $price > 0) {
            $buyerRow  = $this->query("SELECT hype FROM users WHERE id = {$userId}")->fetch_assoc();
            $buyerHype = (int) ($buyerRow['hype'] ?? 0);
            if ($buyerHype < $price) {
                throw new \Exception('No tenés suficiente hype para comprar este objeto', 402);
            }

            $stmtDebit = $this->dbConnection->prepare(
                "UPDATE users SET hype = hype - ? WHERE id = ?"
            );
            $stmtDebit->bind_param('ii', $price, $userId);
            $stmtDebit->execute();
            $stmtDebit->close();
        }

        $this->_insertAssetUser($assetId, $userId);
    }

    /** Inserta la fila de adquisición en asset_user. */
    private function _insertAssetUser(int $assetId, int $userId): void
    {
        $now  = (new DateTime())->format('Y-m-d H:i:s');
        $stmt = $this->dbConnection->prepare(
            "INSERT INTO {$this->table} (asset_id, user_id, created_at) VALUES (?, ?, ?)"
        );
        $stmt->bind_param('iis', $assetId, $userId, $now);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * IDs de `public_assets` (diseños UGC) que el usuario realmente posee,
     * usados para construir el selector `owned_assets` (official-brand-designs
     * PR3). Regla de posesión, en orden de precedencia, fail-open ante
     * colisión de namespace (nunca cerrado — misma filosofía que `buyAsset()`,
     * que ya resuelve la ambigüedad de ids probando `public_assets` antes que
     * `assets`):
     *
     *   1. Ledger `asset_acquisitions` (adquisiciones posteriores a su creación,
     *      siempre referencian `public_assets` — ver FK en la migración).
     *   2. Diseños propios en `public_assets` (submitter_user_id = $userId),
     *      en CUALQUIER status — la autoría nunca se bloquea.
     *   3. Filas legacy en `asset_user` sin match en el ledger (compras
     *      previas a la creación de `asset_acquisitions`): se sondean contra
     *      `public_assets` primero (misma precedencia que `buyAsset()`); si
     *      resuelven ahí, se consideran UGC poseída. Si no resuelven en
     *      `public_assets`, son assets de sistema — ya cubiertos por la
     *      consulta existente de `assets` en `getUserAssetsByUserId()`, no se
     *      agregan acá para no duplicar el namespace.
     *
     * @return int[] IDs únicos de `public_assets`, sin orden garantizado más
     *               allá del de aparición por precedencia.
     */
    public function ownedAssetIds(int $userId): array
    {
        $userIdInt = (int) $userId;
        $ids       = [];

        // 1. Ledger — siempre apunta a public_assets (FK asset_acquisitions.asset_id -> public_assets.id)
        $ledgerRows = $this->query(
            "SELECT DISTINCT asset_id FROM asset_acquisitions WHERE buyer_user_id = {$userIdInt}"
        )->fetch_all(MYSQLI_ASSOC);
        foreach ($ledgerRows as $row) {
            $ids[] = (int) $row['asset_id'];
        }

        // 2. Diseños propios, cualquier status
        $ownRows = $this->query(
            "SELECT id FROM public_assets WHERE submitter_user_id = {$userIdInt}"
        )->fetch_all(MYSQLI_ASSOC);
        foreach ($ownRows as $row) {
            $ids[] = (int) $row['id'];
        }

        // 3. Filas legacy en asset_user sin match en el ledger — sondeo fail-open
        $legacyRows = $this->query(
            "SELECT au.asset_id
             FROM {$this->table} au
             LEFT JOIN asset_acquisitions aq
                    ON aq.asset_id = au.asset_id AND aq.buyer_user_id = au.user_id
             WHERE au.user_id = {$userIdInt} AND aq.id IS NULL"
        )->fetch_all(MYSQLI_ASSOC);

        foreach ($legacyRows as $row) {
            $legacyId = (int) $row['asset_id'];
            $existsPublic = $this->query(
                "SELECT id FROM public_assets WHERE id = {$legacyId}"
            )->fetch_assoc();
            if ($existsPublic !== false && $existsPublic !== null) {
                $ids[] = $legacyId;
            }
            // else: resuelve en `assets` (sistema) — ya servido por la
            // consulta existente de `assets` en getUserAssetsByUserId().
        }

        return array_values(array_unique($ids));
    }

    public function checkAssetExpired($userId)
    {
        $now = (new DateTime)->modify('-3 days')->format('Y-m-d H:i:s');
        $expiredAssets = $this->query(
            "SELECT * FROM {$this->table} WHERE user_id = '{$userId}' AND created_at >= '{$now}'"
        )->fetch_all(MYSQLI_ASSOC);
        if (!empty($expiredAssets)) {
            $this->query(
                "DELETE FROM {$this->table} WHERE user_id = '{$userId}' AND created_at >= '{$now}'"
            );
            return $expiredAssets;
        }
    }
}
