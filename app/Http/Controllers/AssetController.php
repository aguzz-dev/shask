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

    public function getUserAssetsByUserId(Request $request): JsonResponse
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        $res = (new Asset)->getUserAssetsByUserId((int) $request->id);
        return response()->json(['Assets pertenecientes al usuario con ID ' . $request->id, $res]);
    }

    public function buyAsset(Request $request): JsonResponse
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->user_id);

        $source = in_array($request->source, ['hype', 'ad'], true) ? $request->source : 'hype';

        try {
            (new AssetUser)->buyAsset(
                (int) $request->asset_id,
                (int) $request->user_id,
                $source
            );
        } catch (\Exception $e) {
            $code = $e->getCode();
            if ($code === 402) {
                return response()->json(['message' => $e->getMessage()], 402);
            }
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Asset adquirido con éxito']);
    }

    public function checkAssetExpired(Request $request): JsonResponse
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        $res = (new AssetUser)->checkAssetExpired($request->id);
        return empty($res)
            ? response()->json('El usuario no tiene assets expirados')
            : response()->json(['Se eliminaron los siguientes assets expirados del usuario con ID ' . $request->id, $res]);
    }

    /**
     * Crea un asset público (plantilla v3 con canvas de capas).
     * Establece ownership y status según si es el primer asset del creador.
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
            $request->colors ?? [],
            $request->icon ?? '',
            $request->background ?? '',
            $request->canvas,
            (int) $request->user_id
        );

        return response()->json([
            'message' => 'Asset enviado a revisión',
            'id'      => (int) $id,
        ], 201);
    }

    /**
     * Actualiza un asset público existente.
     * Verifica que el caller sea el propietario; responde 403 si no lo es.
     *
     * Body: user_id, asset_id, title, colors, icon, background, canvas
     */
    public function updatePublicAsset(Request $request): JsonResponse
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->user_id);

        try {
            $id = (new Asset)->updatePublicAsset(
                (int) $request->asset_id,
                $request->title,
                $request->colors ?? [],
                $request->icon ?? '',
                $request->background ?? '',
                $request->canvas,
                (int) $request->user_id
            );
        } catch (\Exception $e) {
            if ($e->getCode() === 403) {
                return response()->json(['message' => $e->getMessage()], 403);
            }
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Asset actualizado con éxito',
            'id'      => (int) $id,
        ]);
    }

    /**
     * Reporte one-tap: oculta el asset del catálogo público pendiente de
     * revisión de moderación.
     *
     * Body: asset_id, user_id
     */
    public function reportPublicAsset(Request $request): JsonResponse
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->user_id);

        (new Asset)->reportAsset((int) $request->asset_id);

        return response()->json(['message' => 'Reporte recibido, el diseño será revisado']);
    }
}
