<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MediaCatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Evitar duplicar si ya se corrió.
        if (DB::table('media_images')->count() > 0) {
            return;
        }

        // ── Packs ──────────────────────────────────────────────────────────
        $freeId = DB::table('sticker_packs')->insertGetId([
            'name'       => 'Básicos',
            'slug'       => 'basicos',
            'is_premium' => false,
            'price'      => 0,
            'cover'      => 'fire',
            'sort'       => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gothicId = DB::table('sticker_packs')->insertGetId([
            'name'       => 'Gótico',
            'slug'       => 'gotico',
            'is_premium' => true,
            'price'      => 150,
            'cover'      => 'crow',
            'sort'       => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ── Categorización manual de las imágenes existentes ─────────────────
        // [name => [type, category, tags, premium?]]
        $catalog = [
            'fire'            => ['sticker', 'reacciones', ['fuego', 'hot'], false],
            'hot_chilli'      => ['sticker', 'reacciones', ['picante', 'hot'], false],
            'love_mail'       => ['sticker', 'amor', ['amor', 'carta'], false],
            'love_letter'     => ['sticker', 'amor', ['amor', 'carta'], false],
            'megaphoe'        => ['sticker', 'social', ['megafono', 'aviso'], false],
            'snake'           => ['sticker', 'animales', ['serpiente'], false],
            'bunny'           => ['sticker', 'animales', ['conejo'], false],
            'cup'             => ['sticker', 'fiesta', ['trago', 'cafe'], false],
            'sakura'          => ['sticker', 'naturaleza', ['flor', 'sakura'], false],
            'sakura_ramita'   => ['sticker', 'naturaleza', ['flor', 'sakura'], false],
            'brindis'         => ['sticker', 'fiesta', ['brindis', 'trago'], false],
            'money_splash'    => ['sticker', 'dinero', ['dinero', 'plata'], false],
            'ribbon'          => ['sticker', 'deco', ['cinta'], false],
            'palm_trees'      => ['sticker', 'naturaleza', ['palmera', 'verano'], false],
            'plane'           => ['sticker', 'viaje', ['avion', 'viaje'], false],
            'cats'            => ['sticker', 'animales', ['gato'], false],
            'cat'             => ['sticker', 'animales', ['gato'], false],
            'arg'             => ['sticker', 'paises', ['argentina'], false],
            'graffiti_arrow'  => ['sticker', 'urbano', ['flecha', 'graffiti'], false],
            'branches'        => ['sticker', 'naturaleza', ['ramas'], false],

            // Pack premium gótico
            'crow'            => ['sticker', 'gotico', ['cuervo', 'oscuro'], true],
            'ghost'           => ['sticker', 'gotico', ['fantasma', 'oscuro'], true],
            'poison'          => ['sticker', 'gotico', ['veneno', 'oscuro'], true],
            'smoke'           => ['sticker', 'gotico', ['humo', 'oscuro'], true],

            // Fondos / transparentes
            'background_transparent' => ['background', 'fondos', ['transparente'], false],
            'icon_transparent'       => ['background', 'fondos', ['transparente'], false],
        ];

        $sort = 0;
        foreach ($catalog as $name => $meta) {
            [$type, $category, $tags, $premium] = $meta;
            DB::table('media_images')->insert([
                'name'       => $name,
                'type'       => $type,
                'category'   => $category,
                'tags'       => json_encode($tags),
                'pack_id'    => $premium ? $gothicId : $freeId,
                'sort'       => $sort++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
