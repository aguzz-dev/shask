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

    public function getAllAssets()
    {
        $publicAssets = $this->query("SELECT * FROM public_assets")->fetch_all(MYSQLI_ASSOC);
        $privateAssets = $this->query("SELECT * FROM {$this->table}")->fetch_all(MYSQLI_ASSOC);
        $assetsData = [
            'public_assets' => $publicAssets,
            'assets' => $privateAssets
        ];
        return $assetsData;
    }

    public function getUserAssetsByUserId($id)
    {
        $userAssets = $this->query(
                "SELECT a.*
                FROM assets a
                INNER JOIN asset_user ua ON a.id = ua.asset_id
                WHERE ua.user_id = '{$id}'"
                )->fetch_all(MYSQLI_ASSOC);

        $publicAssets = $this->query("SELECT * FROM public_assets")->fetch_all(MYSQLI_ASSOC);
        $assetsData = [
            'public_assets' => $publicAssets,
            'assets' => $userAssets
        ];
        return $assetsData;
    }

    /**
     * Crea un asset público (plantilla base del sistema de diseño v3).
     *
     * @param string     $title      Nombre del diseño
     * @param array      $colors     RGBA arrays: [[r,g,b,a], ...]
     * @param string     $icon       Clave de la imagen del sticker principal
     * @param string     $background Clave de la imagen de fondo
     * @param array|null $canvas     CanvasDesign serializado (share card 1:1)
     * @return int  ID del registro creado
     */
    public function createPublicAsset($title, $colors, $icon, $background, $canvas = null)
    {
        $colorsJson = $this->dbConnection->real_escape_string(json_encode($colors));
        $titleEsc   = $this->dbConnection->real_escape_string($title ?? '');
        $iconEsc    = $this->dbConnection->real_escape_string($icon ?? '');
        $bgEsc      = $this->dbConnection->real_escape_string($background ?? '');

        if ($canvas !== null) {
            $canvasJson = $this->dbConnection->real_escape_string(json_encode($canvas));
            $this->query(
                "INSERT INTO public_assets (title, color, icon, background, canvas)
                 VALUES ('{$titleEsc}', '{$colorsJson}', '{$iconEsc}', '{$bgEsc}', '{$canvasJson}')"
            );
        } else {
            $this->query(
                "INSERT INTO public_assets (title, color, icon, background)
                 VALUES ('{$titleEsc}', '{$colorsJson}', '{$iconEsc}', '{$bgEsc}')"
            );
        }

        return $this->query("SELECT LAST_INSERT_ID() as id")->fetch_assoc()['id'];
    }

    /**
     * Actualiza un asset público existente (editar diseño).
     *
     * @param int        $id
     * @param string     $title
     * @param array      $colors
     * @param string     $icon
     * @param string     $background
     * @param array|null $canvas
     */
    public function updatePublicAsset($id, $title, $colors, $icon, $background, $canvas = null)
    {
        $idEsc      = (int) $id;
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
                 icon = '{$iconEsc}',
                 background = '{$bgEsc}',
                 canvas = {$canvasSql}
             WHERE id = {$idEsc}"
        );

        return $idEsc;
    }
}
