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

}
