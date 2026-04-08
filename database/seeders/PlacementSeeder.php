<?php

namespace Database\Seeders;

use App\Models\Placement;
use App\Models\Platform;
use App\PlacementType;
use Illuminate\Database\Seeder;

class PlacementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lexpress = Platform::where('name', 'lexpress.mu')->firstOrFail();
        $fivePlus = Platform::where('name', '5plus.mu')->firstOrFail();

        $placements = [
            ['name' => 'Top Billboard Banner Homepage', 'price' => 18000, 'type' => PlacementType::Web, 'platform_id' => $lexpress->id],
            ['name' => 'Centralised Pop-Up', 'price' => 10000, 'type' => PlacementType::Web, 'platform_id' => $lexpress->id],
            ['name' => 'Slider Image', 'price' => 7000, 'type' => PlacementType::Web, 'platform_id' => $lexpress->id],
            ['name' => 'Facebook Post', 'price' => 5000, 'type' => PlacementType::SocialMedia, 'platform_id' => $lexpress->id],
            ['name' => 'LinkedIn Post', 'price' => 2500, 'type' => PlacementType::SocialMedia, 'platform_id' => $lexpress->id],
            ['name' => 'Top Billboard Banner Homepage', 'price' => 10000, 'type' => PlacementType::Web, 'platform_id' => $fivePlus->id],
            ['name' => 'Facebook Post', 'price' => 3000, 'type' => PlacementType::SocialMedia, 'platform_id' => $fivePlus->id],
        ];

        foreach ($placements as $placement) {
            Placement::firstOrCreate(
                [
                    'name' => $placement['name'],
                    'platform_id' => $placement['platform_id'],
                ],
                $placement,
            );
        }
    }
}
