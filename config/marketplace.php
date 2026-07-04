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
];
