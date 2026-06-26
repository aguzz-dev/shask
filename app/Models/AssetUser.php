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
     */
    public function buyAsset(int $assetId, int $userId, string $source = 'hype'): void
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
            $this->_buyUgcAsset($assetIdInt, $userIdInt, $publicAsset['submitter_user_id'], $source);
        } else {
            // Ruta sistema/privado: usar users.hype (corregido desde users.coins)
            $this->_buySystemAsset($assetIdInt, $userIdInt, $source);
        }
    }

    /**
     * Adquisición de asset UGC (de public_assets).
     * Débito al comprador + mint al creador.
     */
    private function _buyUgcAsset(int $assetId, int $userId, ?int $creatorId, string $source): void
    {
        $cost = (int) config('marketplace.acquisition_hype_cost', 20);
        $mint = (int) config('marketplace.creator_hype_mint', 15);

        if ($source === 'hype') {
            // Verificar saldo del comprador
            $buyerRow = $this->query("SELECT hype FROM users WHERE id = {$userId}")->fetch_assoc();
            $buyerHype = (int) ($buyerRow['hype'] ?? 0);
            if ($buyerHype < $cost) {
                throw new \Exception('No tenés suficiente hype para adquirir este diseño', 402);
            }

            // Debitar hype del comprador
            $stmtDebit = $this->dbConnection->prepare(
                "UPDATE users SET hype = hype - ? WHERE id = ?"
            );
            $stmtDebit->bind_param('ii', $cost, $userId);
            $stmtDebit->execute();
            $stmtDebit->close();
        }

        // Mintear hype al creador (generado por la plataforma, no P2P)
        if ($creatorId !== null) {
            $stmtMint = $this->dbConnection->prepare(
                "UPDATE users SET hype = hype + ? WHERE id = ?"
            );
            $stmtMint->bind_param('ii', $mint, $creatorId);
            $stmtMint->execute();
            $stmtMint->close();
        }

        // Incrementar contador de descargas
        $this->query("UPDATE public_assets SET downloads_count = downloads_count + 1 WHERE id = {$assetId}");

        // Registrar adquisición
        $this->_insertAssetUser($assetId, $userId);
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
