<?php

/*
|--------------------------------------------------------------------------
| AdMob rewarded Server-Side Verification (SSV)
|--------------------------------------------------------------------------
|
| Premium unlocks are gated on a signature-verified AdMob SSV callback. Set
| the SSV callback URL of each rewarded ad unit (AdMob console) to:
|
|     https://YOUR_API_HOST/api/ad-reward/ssv
|
| `ssv_enforce` MUST stay true in production — turning it off lets clients
| claim ad watches without proof. Only disable it in local/dev where no real
| SSV callback can reach the server.
|
*/

return [
    'ssv_enforce' => (bool) env('ADMOB_SSV_ENFORCE', true),

    // Google's rotating public keys used to verify SSV callback signatures.
    'verifier_keys_url' => env(
        'ADMOB_VERIFIER_KEYS_URL',
        'https://gstatic.com/admob/reward/verifier-keys.json'
    ),
    'verifier_keys_ttl' => (int) env('ADMOB_VERIFIER_KEYS_TTL', 3600),

    // How long an issued nonce stays spendable before it is considered stale.
    'nonce_ttl_minutes' => (int) env('ADMOB_NONCE_TTL_MINUTES', 30),

    // Creator reputation minted per premium asset use (non-spendable score).
    'creator_reward_mint' => (int) env('ADMOB_CREATOR_REWARD_MINT', 15),

    /*
    | Rollout grace: legacy app builds (pre-SSV) can't send a verified nonce.
    | While true, a premium/paid request without a nonce is let through so
    | existing installs keep working during the coordinated release. Flip it
    | back to false once the new client is widely adopted — leaving it true
    | reopens the fake-ad hole for anyone on an old build.
    */
    'legacy_ad_grace' => (bool) env('ADMOB_LEGACY_AD_GRACE', false),
];
