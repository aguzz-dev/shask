<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            [
                'icon'  => 'fire',
                'price' => 0,
                'color' => [[220, 38,  38,  255], [234, 88,  12,  255], [254, 215, 170, 255]],
            ],
            [
                'icon'  => 'ghost',
                'price' => 0,
                'color' => [[51,  65,  85,  255], [71,  85,  105, 255], [226, 232, 240, 255]],
            ],
            [
                'icon'  => 'crown',
                'price' => 0,
                'color' => [[202, 138, 4,   255], [234, 179, 8,   255], [254, 249, 195, 255]],
            ],
        ];

        foreach ($assets as $asset) {
            DB::table('assets')->insert([
                'icon'       => $asset['icon'],
                'background' => $asset['icon'],
                'price'      => $asset['price'],
                'color'      => json_encode($asset['color']),
            ]);
        }
    }
}
