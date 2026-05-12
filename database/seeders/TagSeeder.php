<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'Cushioned', 'Lightweight', 'Breathable', 'Waterproof',
            'Gore-Tex', 'Arch Support', 'Slip-On', 'High Top',
            'Wide Fit', 'Sustainable', 'Trail', 'Street',
            'Carbon Plate', 'Knit Upper', 'Leather', 'Suede',
            'Canvas', 'Vegan', 'New Arrival', 'Best Seller',
        ];

        foreach ($tags as $name) {
            Tag::firstOrCreate(['name' => $name]);
        }
    }
}
