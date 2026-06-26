<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Filtro anti-bot / prefetch de redes sociales
    |--------------------------------------------------------------------------
    |
    | User-Agent strings que identifican bots o crawlers de previsualizaciones.
    | Si el UA del visitante contiene cualquiera de estos (case-insensitive),
    | la visita no se contabiliza. Extender la lista sin release.
    |
    */
    'bot_user_agents' => [
        'facebookexternalhit',
        'WhatsApp',
        'Instagram',
        'TelegramBot',
        'Googlebot',
        'bingbot',
        'Twitterbot',
        'LinkedInBot',
        'Slackbot',
        'Discordbot',
    ],

    /*
    |--------------------------------------------------------------------------
    | Salt diario para visitor_hash
    |--------------------------------------------------------------------------
    |
    | Rota automáticamente al cambiar el día (date('Y-m-d') se evalúa en cada
    | request). Sobreescribible vía .env para entornos con config cacheada.
    | Tras la rotación el hash de cualquier visitante cambia, haciendo el hash
    | irreconstruible retroactivamente.
    |
    */
    'daily_salt' => env('VISIT_TRACKING_DAILY_SALT', date('Y-m-d')),
];
