<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\PersonalAccessToken;

class AssetController extends Controller
{
    public function getAllAssets(): JsonResponse
    {
        return response()->json((new Asset)->getAllAssets());
    }

    public function getUserAssetsByUserId(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        $res = (new Asset)->getUserAssetsByUserId($request->id);
        return response()->json(['Assets pertenecientes al usuario con ID '.$request->id, $res]);
    }

    public function buyAsset(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->user_id);
        (new AssetUser)->buyAsset($request->asset_id, $request->user_id);
        return response()->json('Asset comprado con éxito');
    }

    public function checkAssetExpired(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        $res = (new AssetUser)->checkAssetExpired($request->id);
        return empty($res)  ? response()->json('El usuario no tiene assets expirados')
                            : response()->json(['Se eliminaron los siguientes assets expirados del usuario con ID '.$request->id, $res]);
    }

    /**
     * Crea un asset público (plantilla v3 con canvas de capas).
     * Requiere token válido.
     *
     * Body esperado:
     *   user_id    int
     *   title      string
     *   colors     array  — [[r,g,b,a], ...]
     *   icon       string — clave de imagen del sticker principal
     *   background string — clave de imagen de fondo (puede ser '')
     *   canvas     object — CanvasDesign JSON (share card 1:1)
     */
    public function createPublicAsset(Request $request): JsonResponse
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->user_id);

        $id = (new Asset)->createPublicAsset(
            $request->title,
            $request->colors,
            $request->icon ?? '',
            $request->background ?? '',
            $request->canvas
        );

        return response()->json([
            'message' => 'Asset creado con éxito',
            'id'      => (int) $id,
        ], 201);
    }

    /**
     * Actualiza un asset público existente (editar diseño).
     * Body: user_id, asset_id, title, colors, icon, background, canvas
     */
    public function updatePublicAsset(Request $request): JsonResponse
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->user_id);

        $id = (new Asset)->updatePublicAsset(
            $request->asset_id,
            $request->title,
            $request->colors,
            $request->icon ?? '',
            $request->background ?? '',
            $request->canvas
        );

        return response()->json([
            'message' => 'Asset actualizado con éxito',
            'id'      => (int) $id,
        ]);
    }

}
