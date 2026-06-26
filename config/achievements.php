<?php

/*
|--------------------------------------------------------------------------
| Achievement thresholds
|--------------------------------------------------------------------------
| Cada umbral se puede ajustar sin un release. Los valores iniciales son
| conservadores y se calibrarán con datos reales de producción.
*/

return [
    // Alcance total de vistas (total_views)
    'reach_100_views'   => 100,
    'reach_1k_views'    => 1000,
    'reach_10k_views'   => 10000,

    // Vistas únicas totales (total_unique_views)
    'unique_50_views'   => 50,
    'unique_500_views'  => 500,

    // Pico de vistas únicas en un solo buzón (max_unique_views_in_a_mailbox)
    'viral_post_views'  => 100,

    // Conversión: ratio preguntas recibidas / visitas únicas en el mejor buzón.
    // Se requiere un mínimo de visitas únicas para que el dato sea significativo.
    'conversion_ace_ratio'      => 0.10,   // 10 % de conversión
    'conversion_ace_min_views'  => 50,

    // Racha de días consecutivos (streak_days)
    'streak_3_days'  => 3,
    'streak_7_days'  => 7,
    'streak_30_days' => 30,
];
