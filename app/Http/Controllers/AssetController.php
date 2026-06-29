<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\PersonalAccessToken;

class AssetController extends Controller
{
    public function getAllAssets(Request $request): JsonResponse
    {
        $q            = $request->query('q');
        $categorySlug = $request->query('category');
        $sort         = $request->query('sort');
        $featured     = (bool) $request->query('featured', false);

        // When no filters are requested, use the existing method for backward
        // compatibility (same response shape and ordering as before).
        if ($q === null && $categorySlug === null && $sort === null && !$featured) {
            return response()->json((new Asset)->getAllAssets());
        }

        $assets = (new Asset)->getPublicCatalog(
            $q ?? null,
            $categorySlug ?? null,
            $sort ?? null,
            $featured
        );

        return response()->json([
            'success' => true,
            'assets'  => $assets,
        ]);
    }

    public function getCategories(): JsonResponse
    {
        $categories = (new Asset)->getCategories();
        return response()->json([
            'success'    => true,
            'categories' => $categories,
        ]);
    }

    public function getCreatorStats(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token requerido'], 401);
        }

        // Resolve the authenticated user from token (self-scoped endpoint)
        $pat    = new \App\Models\PersonalAccessToken;
        $result = $pat->getIdByToken($token);

        // getIdByToken returns a JsonResponse when token is not found
        if ($result instanceof \Illuminate\Http\JsonResponse) {
            return response()->json(['message' => 'Token invalido'], 401);
        }

        $userId = (int) $result;

        $stats = (new Asset)->getCreatorStats($userId);

        return response()->json([
            'success' => true,
            'stats'   => $stats,
        ]);
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
