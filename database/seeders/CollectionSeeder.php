<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\StoreFile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CollectionSeeder extends Seeder
{
    public function run(): void
    {
        $collections = [
            [
                'title'        => 'New Arrivals',
                'handle'       => 'new-arrivals',
                'description'  => '<p>The freshest drops — just landed. Be the first to step into our newest collection of premium footwear.</p>',
                'sort_order'   => 'created-descending',
                'published_at' => now()->subDays(1),
                'image_id'     => '1542291026-7eec264c27ff',
                'image_alt'    => 'New arrivals collection — red Nike running sneaker',
            ],
            [
                'title'        => 'Best Sellers',
                'handle'       => 'best-sellers',
                'description'  => '<p>Our most-loved styles, tried and trusted by thousands of customers. Crowd-pleasing shoes that never go out of style.</p>',
                'sort_order'   => 'best-selling',
                'published_at' => now()->subDays(2),
                'image_id'     => '1552346154-21d32810aba3',
                'image_alt'    => 'Best sellers collection — classic running shoes',
            ],
            [
                'title'        => 'Running Performance',
                'handle'       => 'running-performance',
                'description'  => '<p>Built for every stride. Engineered running shoes from the world\'s leading brands to help you go faster, further, and more comfortably.</p>',
                'sort_order'   => 'manual',
                'published_at' => now()->subDays(3),
                'image_id'     => '1491553895911-0055eca6402d',
                'image_alt'    => 'Running performance collection — yellow and gray running shoes',
            ],
            [
                'title'        => 'Street Style',
                'handle'       => 'street-style',
                'description'  => '<p>Everyday icons for everyday life. Classic silhouettes and modern lifestyle sneakers that pair with anything in your wardrobe.</p>',
                'sort_order'   => 'manual',
                'published_at' => now()->subDays(4),
                'image_id'     => '1460353581641-37baddab0fa2',
                'image_alt'    => 'Street style collection — white classic sneakers',
            ],
            [
                'title'        => 'Premium Collection',
                'handle'       => 'premium-collection',
                'description'  => '<p>No compromises. Our premium selection features the finest materials, cutting-edge technology, and elite craftsmanship.</p>',
                'sort_order'   => 'price-descending',
                'published_at' => now()->subDays(5),
                'image_id'     => '1525966222134-fcfa99b8ae77',
                'image_alt'    => 'Premium collection — white leather lifestyle sneakers',
            ],
            [
                'title'        => 'Trail & Adventure',
                'handle'       => 'trail-adventure',
                'description'  => '<p>Conquer any terrain. Purpose-built trail running and hiking footwear designed to keep you moving when the path gets tough.</p>',
                'sort_order'   => 'manual',
                'published_at' => now()->subDays(6),
                'image_id'     => '1603487742131-4160ec999306',
                'image_alt'    => 'Trail and adventure collection — trail hiking boots',
            ],
        ];

        $disk = Storage::disk('public');

        foreach ($collections as $data) {
            $imageId  = $data['image_id'];
            $imageAlt = $data['image_alt'];
            unset($data['image_id'], $data['image_alt']);

            $collection = Collection::firstOrCreate(
                ['handle' => $data['handle']],
                $data
            );

            // Skip if this collection already has an image
            if ($collection->store_file_id) {
                continue;
            }

            $path = "store-files/seeds/{$imageId}.jpg";

            // Reuse existing StoreFile if already downloaded by ProductSeeder
            $storeFile = StoreFile::where('path', $path)->where('disk', 'public')->first();

            if (! $storeFile) {
                $url      = "https://images.unsplash.com/photo-{$imageId}?w=1200&h=600&fit=crop&q=80";
                $response = Http::timeout(20)->get($url);

                if ($response->successful()) {
                    $disk->put($path, $response->body());

                    $fullPath  = $disk->path($path);
                    $storeFile = StoreFile::create([
                        'filename'  => basename($path),
                        'disk'      => 'public',
                        'path'      => $path,
                        'url'       => $disk->url($path),
                        'mime_type' => mime_content_type($fullPath) ?: 'image/jpeg',
                        'size'      => $disk->size($path),
                        'alt'       => $imageAlt,
                    ]);
                }
            }

            if ($storeFile) {
                $collection->update(['store_file_id' => $storeFile->id]);
            }
        }
    }
}
