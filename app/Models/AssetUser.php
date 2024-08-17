<?php

namespace App\Models;
use App\Database;
use DateTime;

class AssetUser extends Database
{
    protected $table = 'asset_user';

    public function buyAsset($assetId, $userId)
    {
        $isProductPurchased = $this->query("SELECT * FROM {$this->table} WHERE asset_id = '{$assetId}' AND user_id = '{$userId}'")->fetch_all(MYSQLI_ASSOC);
        if (!empty($isProductPurchased)){
            throw new \Exception('El asset ya pertenece a este usuario');
        }
        $assetPrice = (new Asset)->findById($assetId)[0]['price'];
        $userCoins = (new User)->findById($userId)[0]['coins'];
        if($assetPrice > $userCoins){
            throw new \Exception('No tienes monedas suficientes para comprar este objeto');
        }
        $this->query("UPDATE users SET `price` = " . $userCoins - $assetPrice . " WHERE id = '{$userId}'");
        $currentDateTime = (new DateTime())->format('Y-m-d H:i:s');
        $this->query("INSERT INTO {$this->table} (`asset_id`, `user_id`, `created_at`) VALUES ('{$assetId}', '{$userId}', '{$currentDateTime}')");
    }

    public function checkAssetExpired($userId)
    {
        $now = (new DateTime)->modify('-3 days')->format('Y-m-d H:i:s');
        $expiredAssets = $this->query("SELECT * FROM {$this->table} WHERE user_id = '{$userId}' AND created_at >= '{$now}'")->fetch_all(MYSQLI_ASSOC);
        if(!empty($expiredAssets)){
            $this->query("DELETE FROM {$this->table} WHERE user_id = '{$userId}' AND created_at >= '{$now}'");
            return $expiredAssets;
        }
    }
}
