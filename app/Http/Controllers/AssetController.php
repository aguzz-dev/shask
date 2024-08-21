<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Asset;
use App\Models\AssetUser;
use App\Helpers\JsonRequest;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Middleware\VerifyToken;

class AssetController extends Controller
{
    public function getAllAssets()
    {
        VerifyToken::jwt();
        return response()->json(['Solicitud exitosa, assets existentes:', (new Asset)->getAllAssets()]);
    }

    public function getUserAssetsByUserId(Request $request)
    {
        VerifyToken::jwt();
        $res = (new Asset)->getUserAssetsByUserId($request->id);
        return response()->json(['Assets pertenecientes al usuario con ID '.$request->id, $res]);
    }

    public function buyAsset(Request $request)
    {
        VerifyToken::jwt();
        (new AssetUser)->buyAsset($request->asset_id, $request->user_id);
        return response()->json('Asset comprado con éxito');
    }

    public function checkAssetExpired(Request $request)
    {
        VerifyToken::jwt();
        $res = (new AssetUser)->checkAssetExpired($request->id);
        return empty($res)  ? response()->json('El usuario no tiene assets expirados')
                            : response()->json(['Se eliminaron los siguientes assets expirados del usuario con ID '.$request->id, $res]);
    }

}
