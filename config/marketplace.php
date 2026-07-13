<?php

/*
|--------------------------------------------------------------------------
| Economía del Marketplace UGC
|--------------------------------------------------------------------------
| Valores ajustables sin release. El hype es GENERADO (la plataforma lo
| acredita al creador); nunca es una transferencia P2P entre usuarios.
*/

return [
    // Costo en hype para adquirir un diseño UGC (camino hype)
    'acquisition_hype_cost' => 20,

    // Hype que la plataforma mintea al creador por cada adquisición
    'creator_hype_mint' => 15,

    /*
    |--------------------------------------------------------------------------
    | Push al creador por adquisición (creator-acquisition-push)
    |--------------------------------------------------------------------------
    | Ventana de quiet-hours (HH:MM, reloj de 24h, huso horario de la app —
    | UTC por defecto). Los push individuales dentro de esta ventana se
    | difieren (nunca se descartan), y se envían al cerrar la ventana vía
    | el comando `push:flush-quiet-hours`.
    */
    'push_quiet_start' => '22:00',
    'push_quiet_end'   => '08:00',

    // Umbral de adquisiciones/día para disparar el digest diario
    // (`push:creator-digest`, corre a las 20:30).
    'push_digest_threshold' => 3,

    /*
    |--------------------------------------------------------------------------
    | Catalog pagination (marketplace-pagination)
    |--------------------------------------------------------------------------
    | Page size is server-authoritative: the client only sends `offset` and
    | echoes back the `next_offset` the server returns. `rail_cap` bounds the
    | Featured/Trending discovery rails, which never paginate. Both are
    | ajustables sin release.
    */
    'catalog_page_size' => 20,
    'rail_cap'          => 10,

    /*
    |--------------------------------------------------------------------------
    | Trending momentum window (marketplace-trending-momentum)
    |--------------------------------------------------------------------------
    | The trending rail ranks items by acquisitions within the last N days,
    | not lifetime downloads_count. Cold-start (few weekly movers) pads the
    | rail with all-time favorites (downloads_count DESC) instead of a
    | separate query — see Asset::getPublicCatalog().
    */
    'trending_window_days' => 7,
];
