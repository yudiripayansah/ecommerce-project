<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreFile;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /** @var StoreFile[] */
    private array $imagePool = [];

    // ---------------------------------------------------------------------------
    // Unsplash photo IDs — curated shoe photos
    // ---------------------------------------------------------------------------
    private array $unsplashImages = [
        ['id' => '1542291026-7eec264c27ff', 'alt' => 'Red Nike running sneaker'],
        ['id' => '1491553895911-0055eca6402d', 'alt' => 'Yellow and gray running shoes'],
        ['id' => '1460353581641-37baddab0fa2', 'alt' => 'White classic sneakers'],
        ['id' => '1539185441755-769473a23570', 'alt' => 'White lifestyle sneakers'],
        ['id' => '1595950653106-6c9ebd614d3a', 'alt' => 'Clean white sneaker side view'],
        ['id' => '1606107557195-0e29a4b5b4aa', 'alt' => 'High-top basketball sneakers'],
        ['id' => '1584735175315-9d5df23be7fe', 'alt' => 'Black sneakers on white background'],
        ['id' => '1556906781-9dcd19c31cc0', 'alt' => 'Colorful performance running shoes'],
        ['id' => '1552346154-21d32810aba3', 'alt' => 'Classic running shoes pair'],
        ['id' => '1512374382149-233c42b6a83b', 'alt' => 'Blue and white sport shoes'],
        ['id' => '1518002171953-a080ee817e1f', 'alt' => 'Canvas sneakers lifestyle'],
        ['id' => '1525966222134-fcfa99b8ae77', 'alt' => 'White leather lifestyle sneakers'],
        ['id' => '1511556532299-8f662fc26c06', 'alt' => 'Nike React running shoes'],
        ['id' => '1543508282-6319a3e2621f', 'alt' => 'Brown leather boots'],
        ['id' => '1560769629-975ec94e6a86', 'alt' => 'Orange and green running shoes'],
        ['id' => '1571945153237-4929e783af4a', 'alt' => 'Red orange running shoes'],
        ['id' => '1486926560569-52a29af62db4', 'alt' => 'Blue casual sneakers'],
        ['id' => '1607893694052-2c81d432ceeb', 'alt' => 'White Chelsea boots'],
        ['id' => '1608231387042-66d1773d3028', 'alt' => 'Minimal white sneaker'],
        ['id' => '1614252234891-bfd47513a8b2', 'alt' => 'Retro colorful sneakers'],
        ['id' => '1603808033192-082d6919d3e1', 'alt' => 'Yellow Nike Blazer sneaker'],
        ['id' => '1603487742131-4160ec999306', 'alt' => 'Trail hiking boots outdoor'],
        ['id' => '1600185365483-26d7a4cc7519', 'alt' => 'White leather sneakers side'],
        ['id' => '1587563871167-1ee9c731aefb', 'alt' => 'Jordan style high-top sneakers'],
        ['id' => '1626379961798-54f819ee896a', 'alt' => 'Nike Air Max style sneaker'],
        ['id' => '1600181516264-3ea807ff44b9', 'alt' => 'Classic leather Oxford shoes'],
        ['id' => '1573600073955-f15b3b6caab7', 'alt' => 'Running shoes on track'],
        ['id' => '1542280756-74b2f55e73ab', 'alt' => 'Nike shoes pair white background'],
        ['id' => '1600269452121-4f2416e55c28', 'alt' => 'Brown leather formal shoes'],
        ['id' => '1572635196237-14b3f281503f', 'alt' => 'Colorful sneaker collection'],
    ];

    // ---------------------------------------------------------------------------
    // Product catalogue — 100 shoes across 10 brands
    // ---------------------------------------------------------------------------
    private function catalogue(): array
    {
        return [
            // ── Nike ─────────────────────────────────────────────────────────
            ['title' => 'Nike Air Max 270 React',         'vendor' => 'Nike', 'price' => 1_650_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Breathable','New Arrival'], 'cols' => ['New Arrivals','Running Performance'], 'img' => 0],
            ['title' => 'Nike React Infinity Run Flyknit 3','vendor'=> 'Nike', 'price' => 2_099_000, 'compare' => 2_499_000,  'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Knit Upper','Cushioned','Lightweight'], 'cols' => ['Running Performance','Premium Collection'], 'img' => 12],
            ['title' => 'Nike Air Pegasus 40',             'vendor' => 'Nike', 'price' => 1_549_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Breathable','Cushioned'],               'cols' => ['Running Performance','Best Sellers'],  'img' => 8],
            ['title' => 'Nike Air Force 1 \'07',           'vendor' => 'Nike', 'price' => 1_349_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street','Best Seller'],        'cols' => ['Street Style','Best Sellers'],          'img' => 2],
            ['title' => 'Nike Dunk Low Retro',             'vendor' => 'Nike', 'price' => 1_349_000, 'compare' => 1_599_000,  'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Leather','Street'],              'cols' => ['Street Style','New Arrivals'],          'img' => 11],
            ['title' => 'Nike Blazer Mid \'77 Vintage',    'vendor' => 'Nike', 'price' => 1_049_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Street','High Top'],             'cols' => ['Street Style'],                        'img' => 20],
            ['title' => 'Nike Zoom Fly 5',                 'vendor' => 'Nike', 'price' => 1_999_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Lightweight','Cushioned'],'cols' => ['Running Performance','New Arrivals'],  'img' => 27],
            ['title' => 'Nike Metcon 8',                   'vendor' => 'Nike', 'price' => 1_799_000, 'compare' => null,       'type' => 'Training Shoes', 'cat' => 'Training & Gym',     'tags' => ['Cushioned','Breathable'],               'cols' => ['New Arrivals'],                        'img' => 7],
            ['title' => 'Nike Air Jordan 1 Retro High OG', 'vendor' => 'Nike', 'price' => 3_499_000, 'compare' => null,       'type' => 'Basketball',     'cat' => 'Basketball',         'tags' => ['Leather','High Top','Best Seller'],      'cols' => ['Best Sellers','Premium Collection'],   'img' => 5],
            ['title' => 'Nike Air Zoom Alphafly Next% 2',  'vendor' => 'Nike', 'price' => 3_999_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Lightweight','Cushioned'],'cols' => ['Premium Collection','Running Performance'],'img' => 0],

            // ── Adidas ────────────────────────────────────────────────────────
            ['title' => 'Adidas Ultraboost 23',            'vendor' => 'Adidas', 'price' => 2_799_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Knit Upper','Breathable'],   'cols' => ['Running Performance','Premium Collection','Best Sellers'], 'img' => 1],
            ['title' => 'Adidas NMD R1',                   'vendor' => 'Adidas', 'price' => 1_699_000, 'compare' => 1_999_000,  'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Street','Knit Upper'],       'cols' => ['Street Style','New Arrivals'],          'img' => 3],
            ['title' => 'Adidas Stan Smith',               'vendor' => 'Adidas', 'price' => 999_000,   'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street','Best Seller','Vegan'], 'cols' => ['Street Style','Best Sellers'],          'img' => 4],
            ['title' => 'Adidas Samba OG',                 'vendor' => 'Adidas', 'price' => 1_299_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Suede','Street'],              'cols' => ['Street Style','New Arrivals'],          'img' => 11],
            ['title' => 'Adidas Gazelle Indoor',           'vendor' => 'Adidas', 'price' => 1_099_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Street'],                        'cols' => ['Street Style'],                        'img' => 3],
            ['title' => 'Adidas Forum Low',                'vendor' => 'Adidas', 'price' => 1_099_000, 'compare' => 1_299_000,  'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street'],                      'cols' => ['Street Style'],                        'img' => 2],
            ['title' => 'Adidas Superstar',                'vendor' => 'Adidas', 'price' => 1_049_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street','Best Seller'],        'cols' => ['Street Style','Best Sellers'],          'img' => 4],
            ['title' => 'Adidas ZX 22 Boost',             'vendor' => 'Adidas', 'price' => 1_549_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Street','Breathable'],       'cols' => ['Street Style','New Arrivals'],          'img' => 29],
            ['title' => 'Adidas Terrex Swift R3 GTX',     'vendor' => 'Adidas', 'price' => 2_199_000, 'compare' => null,       'type' => 'Hiking Shoes',   'cat' => 'Trail & Hiking',     'tags' => ['Waterproof','Gore-Tex','Trail'],         'cols' => ['Trail & Adventure'],                   'img' => 21],
            ['title' => 'Adidas Adizero Adios Pro 3',     'vendor' => 'Adidas', 'price' => 3_499_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Lightweight'],            'cols' => ['Premium Collection','Running Performance'], 'img' => 27],

            // ── New Balance ───────────────────────────────────────────────────
            ['title' => 'New Balance 990v5 Made in USA',  'vendor' => 'New Balance', 'price' => 3_299_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Leather','Cushioned'],          'cols' => ['Premium Collection','Best Sellers'],   'img' => 8],
            ['title' => 'New Balance 574 Core',           'vendor' => 'New Balance', 'price' => 1_199_000, 'compare' => 1_399_000,  'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Street'],                        'cols' => ['Street Style'],                        'img' => 3],
            ['title' => 'New Balance Fresh Foam X 1080v13','vendor'=> 'New Balance', 'price' => 2_599_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Breathable','Knit Upper'],  'cols' => ['Running Performance','Premium Collection'], 'img' => 1],
            ['title' => 'New Balance 327',                'vendor' => 'New Balance', 'price' => 1_099_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Street','New Arrival'],          'cols' => ['Street Style','New Arrivals'],          'img' => 14],
            ['title' => 'New Balance 9060',               'vendor' => 'New Balance', 'price' => 1_899_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Street','New Arrival'],      'cols' => ['Street Style','New Arrivals'],          'img' => 8],
            ['title' => 'New Balance Fuel Cell Rebel v3', 'vendor' => 'New Balance', 'price' => 1_999_000, 'compare' => 2_299_000,  'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Lightweight','Carbon Plate','Breathable'],'cols' => ['Running Performance'],                 'img' => 15],
            ['title' => 'New Balance 2002R',              'vendor' => 'New Balance', 'price' => 1_699_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Street','New Arrival'],          'cols' => ['Street Style','New Arrivals'],          'img' => 8],
            ['title' => 'New Balance 997H',               'vendor' => 'New Balance', 'price' => 1_299_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Street'],                        'cols' => ['Street Style'],                        'img' => 3],
            ['title' => 'New Balance 1906D Protection Pack','vendor'=> 'New Balance', 'price' => 1_899_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Leather','New Arrival'],         'cols' => ['New Arrivals','Street Style'],          'img' => 14],
            ['title' => 'New Balance FuelCell SuperComp Elite v3','vendor'=>'New Balance','price'=>3_999_000,'compare'=>null,        'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Lightweight','Cushioned'],'cols' => ['Running Performance','Premium Collection'], 'img' => 27],

            // ── Puma ──────────────────────────────────────────────────────────
            ['title' => 'Puma Suede Classic XXI',         'vendor' => 'Puma', 'price' => 799_000,   'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Street','Best Seller'],          'cols' => ['Street Style','Best Sellers'],          'img' => 6],
            ['title' => 'Puma RS-X',                      'vendor' => 'Puma', 'price' => 1_299_000, 'compare' => 1_499_000,  'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Street'],                    'cols' => ['Street Style'],                        'img' => 29],
            ['title' => 'Puma Cali Sport Mix',            'vendor' => 'Puma', 'price' => 1_099_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street'],                      'cols' => ['Street Style'],                        'img' => 4],
            ['title' => 'Puma Velocity Nitro 2',          'vendor' => 'Puma', 'price' => 1_499_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Breathable','Lightweight'],  'cols' => ['Running Performance'],                 'img' => 15],
            ['title' => 'Puma Deviate Nitro Elite 2',     'vendor' => 'Puma', 'price' => 2_799_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Lightweight','Cushioned'],'cols' => ['Running Performance','Premium Collection'], 'img' => 0],
            ['title' => 'Puma Cell Venom',                'vendor' => 'Puma', 'price' => 1_199_000, 'compare' => 1_399_000,  'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Street'],                    'cols' => ['Street Style'],                        'img' => 29],
            ['title' => 'Puma Smash v2 Leather',         'vendor' => 'Puma', 'price' => 549_000,   'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street'],                      'cols' => ['Street Style'],                        'img' => 11],
            ['title' => 'Puma Fast-R Nitro Elite',        'vendor' => 'Puma', 'price' => 3_299_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Lightweight'],            'cols' => ['Running Performance','Premium Collection'], 'img' => 27],
            ['title' => 'Puma Faster-R Nitro Elite 2',   'vendor' => 'Puma', 'price' => 3_699_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Cushioned','New Arrival'],'cols' => ['Running Performance','Premium Collection','New Arrivals'], 'img' => 15],
            ['title' => 'Puma Anzarun 2.0',              'vendor' => 'Puma', 'price' => 699_000,   'compare' => 899_000,   'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Lightweight','Breathable'],              'cols' => ['Running Performance'],                 'img' => 1],

            // ── Reebok ────────────────────────────────────────────────────────
            ['title' => 'Reebok Classic Leather',         'vendor' => 'Reebok', 'price' => 899_000,   'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street','Best Seller'],        'cols' => ['Street Style','Best Sellers'],          'img' => 11],
            ['title' => 'Reebok Club C 85',               'vendor' => 'Reebok', 'price' => 849_000,   'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street'],                      'cols' => ['Street Style'],                        'img' => 4],
            ['title' => 'Reebok Nano X3',                 'vendor' => 'Reebok', 'price' => 1_499_000, 'compare' => null,       'type' => 'Training Shoes', 'cat' => 'Training & Gym',     'tags' => ['Cushioned','Breathable','Arch Support'],  'cols' => ['New Arrivals'],                        'img' => 7],
            ['title' => 'Reebok Floatride Energy 5',      'vendor' => 'Reebok', 'price' => 1_299_000, 'compare' => 1_499_000,  'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Lightweight'],              'cols' => ['Running Performance'],                 'img' => 8],
            ['title' => 'Reebok BB4500 Hi2',              'vendor' => 'Reebok', 'price' => 1_299_000, 'compare' => null,       'type' => 'Basketball',     'cat' => 'Basketball',         'tags' => ['High Top','Leather'],                    'cols' => ['Best Sellers'],                        'img' => 5],
            ['title' => 'Reebok Question Low',            'vendor' => 'Reebok', 'price' => 1_899_000, 'compare' => null,       'type' => 'Basketball',     'cat' => 'Basketball',         'tags' => ['Leather','Cushioned'],                   'cols' => ['Best Sellers'],                        'img' => 5],
            ['title' => 'Reebok Instapump Fury 95',       'vendor' => 'Reebok', 'price' => 1_999_000, 'compare' => 2_399_000,  'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Street'],                    'cols' => ['Street Style'],                        'img' => 16],
            ['title' => 'Reebok Freestyle Hi',            'vendor' => 'Reebok', 'price' => 849_000,   'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['High Top','Leather'],                    'cols' => ['Street Style'],                        'img' => 23],
            ['title' => 'Reebok Forever Floatride Grow',  'vendor' => 'Reebok', 'price' => 1_599_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Sustainable','Cushioned','Lightweight'], 'cols' => ['Running Performance'],                 'img' => 26],
            ['title' => 'Reebok Lifter PR II',            'vendor' => 'Reebok', 'price' => 1_199_000, 'compare' => null,       'type' => 'Training Shoes', 'cat' => 'Training & Gym',     'tags' => ['Arch Support'],                          'cols' => ['New Arrivals'],                        'img' => 7],

            // ── Vans ──────────────────────────────────────────────────────────
            ['title' => 'Vans Old Skool',                 'vendor' => 'Vans', 'price' => 799_000,   'compare' => null,       'type' => 'Skate Shoes',    'cat' => 'Skate',              'tags' => ['Canvas','Suede','Street','Best Seller'],  'cols' => ['Street Style','Best Sellers'],          'img' => 10],
            ['title' => 'Vans Authentic',                 'vendor' => 'Vans', 'price' => 649_000,   'compare' => null,       'type' => 'Skate Shoes',    'cat' => 'Skate',              'tags' => ['Canvas','Street'],                        'cols' => ['Street Style'],                        'img' => 10],
            ['title' => 'Vans Slip-On',                   'vendor' => 'Vans', 'price' => 699_000,   'compare' => null,       'type' => 'Skate Shoes',    'cat' => 'Skate',              'tags' => ['Canvas','Slip-On','Street'],              'cols' => ['Street Style'],                        'img' => 10],
            ['title' => 'Vans Era',                       'vendor' => 'Vans', 'price' => 699_000,   'compare' => 849_000,   'type' => 'Skate Shoes',    'cat' => 'Skate',              'tags' => ['Canvas','Street'],                        'cols' => ['Street Style'],                        'img' => 10],
            ['title' => 'Vans Sk8-Hi',                   'vendor' => 'Vans', 'price' => 899_000,   'compare' => null,       'type' => 'Skate Shoes',    'cat' => 'Skate',              'tags' => ['Canvas','High Top','Street'],             'cols' => ['Street Style','Best Sellers'],          'img' => 5],
            ['title' => 'Vans ComfyCush Old Skool',      'vendor' => 'Vans', 'price' => 999_000,   'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Canvas','Street'],            'cols' => ['Street Style'],                        'img' => 10],
            ['title' => 'Vans UltraRange EXO Hi MTE-1',  'vendor' => 'Vans', 'price' => 1_499_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Waterproof','High Top','New Arrival'],   'cols' => ['New Arrivals'],                        'img' => 21],
            ['title' => 'Vans Rowley Classic LX',        'vendor' => 'Vans', 'price' => 1_299_000, 'compare' => null,       'type' => 'Skate Shoes',    'cat' => 'Skate',              'tags' => ['Leather','Street'],                      'cols' => ['Street Style'],                        'img' => 11],
            ['title' => 'Vans Half Cab',                 'vendor' => 'Vans', 'price' => 999_000,   'compare' => null,       'type' => 'Skate Shoes',    'cat' => 'Skate',              'tags' => ['Suede','High Top','Street'],             'cols' => ['Street Style'],                        'img' => 23],
            ['title' => 'Vans Knu Skool',                'vendor' => 'Vans', 'price' => 1_099_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Canvas','Street','New Arrival'],         'cols' => ['Street Style','New Arrivals'],          'img' => 10],

            // ── Converse ──────────────────────────────────────────────────────
            ['title' => 'Converse Chuck Taylor All Star Classic','vendor'=>'Converse','price'=>649_000,'compare'=>null,        'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Canvas','Street','Best Seller'],         'cols' => ['Street Style','Best Sellers'],          'img' => 10],
            ['title' => 'Converse Run Star Hike Hi',      'vendor' => 'Converse', 'price' => 1_399_000, 'compare' => null,   'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['High Top','Canvas','New Arrival'],       'cols' => ['Street Style','New Arrivals'],          'img' => 19],
            ['title' => 'Converse One Star Pro',          'vendor' => 'Converse', 'price' => 999_000,   'compare' => null,   'type' => 'Skate Shoes',    'cat' => 'Skate',              'tags' => ['Suede','Street'],                        'cols' => ['Street Style'],                        'img' => 6],
            ['title' => 'Converse Pro Leather',           'vendor' => 'Converse', 'price' => 1_199_000, 'compare' => null,   'type' => 'Basketball',     'cat' => 'Basketball',         'tags' => ['Leather','High Top'],                    'cols' => ['Best Sellers'],                        'img' => 5],
            ['title' => 'Converse Jack Purcell',          'vendor' => 'Converse', 'price' => 849_000,   'compare' => null,   'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Canvas','Leather','Street'],             'cols' => ['Street Style'],                        'img' => 4],
            ['title' => 'Converse Chuck 70 High Top',    'vendor' => 'Converse', 'price' => 899_000,   'compare' => 1_099_000,'type' => 'Casual Shoes',  'cat' => 'Casual / Lifestyle', 'tags' => ['Canvas','High Top','Street'],            'cols' => ['Street Style'],                        'img' => 10],
            ['title' => 'Converse Star Player 76',       'vendor' => 'Converse', 'price' => 849_000,   'compare' => null,   'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street'],                      'cols' => ['Street Style'],                        'img' => 11],
            ['title' => 'Converse Chuck All Star Crater', 'vendor' => 'Converse', 'price' => 799_000,   'compare' => null,   'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Canvas','Sustainable','Street'],         'cols' => ['Street Style'],                        'img' => 10],
            ['title' => 'Converse Run Star Legacy CX Hi','vendor' => 'Converse', 'price' => 1_299_000, 'compare' => null,   'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['High Top','New Arrival'],                'cols' => ['New Arrivals'],                        'img' => 23],
            ['title' => 'Converse Louie Lopez Pro',       'vendor' => 'Converse', 'price' => 1_099_000, 'compare' => 1_299_000,'type'=> 'Skate Shoes',   'cat' => 'Skate',              'tags' => ['Suede','Street'],                        'cols' => ['Street Style'],                        'img' => 6],

            // ── ASICS ─────────────────────────────────────────────────────────
            ['title' => 'ASICS Gel-Nimbus 25',            'vendor' => 'ASICS', 'price' => 2_899_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Breathable','Knit Upper'],   'cols' => ['Running Performance','Premium Collection'], 'img' => 1],
            ['title' => 'ASICS Gel-Kayano 30',            'vendor' => 'ASICS', 'price' => 2_699_000, 'compare' => 2_999_000,  'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Arch Support','Wide Fit'],   'cols' => ['Running Performance','Premium Collection'], 'img' => 8],
            ['title' => 'ASICS GT-2000 12',               'vendor' => 'ASICS', 'price' => 1_799_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Arch Support','Cushioned','Breathable'], 'cols' => ['Running Performance'],                 'img' => 15],
            ['title' => 'ASICS Gel-Cumulus 25',           'vendor' => 'ASICS', 'price' => 1_899_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Breathable'],               'cols' => ['Running Performance'],                 'img' => 26],
            ['title' => 'ASICS Gel-Pulse 15',             'vendor' => 'ASICS', 'price' => 1_399_000, 'compare' => 1_599_000,  'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Lightweight'],              'cols' => ['Running Performance'],                 'img' => 9],
            ['title' => 'ASICS Gel-Venture 9',            'vendor' => 'ASICS', 'price' => 999_000,   'compare' => null,       'type' => 'Trail Shoes',    'cat' => 'Trail & Hiking',     'tags' => ['Trail','Cushioned'],                    'cols' => ['Trail & Adventure'],                   'img' => 21],
            ['title' => 'ASICS Gel-Excite 10',            'vendor' => 'ASICS', 'price' => 949_000,   'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Lightweight'],              'cols' => ['Running Performance'],                 'img' => 26],
            ['title' => 'ASICS Gel-Quantum 360 8',        'vendor' => 'ASICS', 'price' => 2_299_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Street','New Arrival'],      'cols' => ['Street Style','New Arrivals'],          'img' => 29],
            ['title' => 'ASICS Gel-Lyte III OG',          'vendor' => 'ASICS', 'price' => 1_699_000, 'compare' => null,       'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Leather','Street'],              'cols' => ['Street Style'],                        'img' => 14],
            ['title' => 'ASICS Metaspeed Sky+',           'vendor' => 'ASICS', 'price' => 4_499_000, 'compare' => null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Lightweight','New Arrival'],'cols'=>['Running Performance','Premium Collection','New Arrivals'],'img'=>27],

            // ── Skechers ──────────────────────────────────────────────────────
            ['title' => 'Skechers Go Walk 7',             'vendor' => 'Skechers', 'price' => 749_000,   'compare' => null,    'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Breathable','Slip-On','Arch Support'],   'cols' => ['Best Sellers'],                        'img' => 18],
            ['title' => 'Skechers Max Cushioning Elite 2.0','vendor'=>'Skechers', 'price' => 999_000,  'compare' => null,    'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Breathable'],               'cols' => ['Running Performance'],                 'img' => 15],
            ['title' => 'Skechers D\'Lites 1.0',          'vendor' => 'Skechers', 'price' => 849_000,   'compare' => 999_000, 'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Street'],                    'cols' => ['Street Style'],                        'img' => 16],
            ['title' => 'Skechers Arch Fit Paradyme',     'vendor' => 'Skechers', 'price' => 1_099_000, 'compare' => null,    'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Arch Support','Cushioned','Wide Fit'],   'cols' => ['New Arrivals'],                        'img' => 18],
            ['title' => 'Skechers Go Run Consistent 2.0', 'vendor' => 'Skechers', 'price' => 849_000,   'compare' => null,    'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Breathable','Lightweight'], 'cols' => ['Running Performance'],                 'img' => 26],
            ['title' => 'Skechers Ultra Flex 3.0',       'vendor' => 'Skechers', 'price' => 799_000,   'compare' => null,    'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Slip-On','Cushioned','Breathable'],      'cols' => ['Best Sellers'],                        'img' => 18],
            ['title' => 'Skechers Relaxed Fit Equalizer 5.0','vendor'=>'Skechers','price'=> 799_000,  'compare' => null,    'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Wide Fit','Cushioned'],                  'cols' => ['Best Sellers'],                        'img' => 18],
            ['title' => 'Skechers GOwalk Joy',            'vendor' => 'Skechers', 'price' => 749_000,   'compare' => 899_000, 'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Breathable','Slip-On','Arch Support'],   'cols' => ['Best Sellers'],                        'img' => 18],
            ['title' => 'Skechers Summits Suited',        'vendor' => 'Skechers', 'price' => 899_000,   'compare' => null,    'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Breathable','Lightweight'],              'cols' => ['Street Style'],                        'img' => 19],
            ['title' => 'Skechers GOrun Speed Elite Hyper','vendor'=>'Skechers', 'price' => 2_299_000, 'compare' => null,    'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Lightweight','Carbon Plate','New Arrival'],'cols'=>['Running Performance','New Arrivals'],  'img' => 27],

            // ── Salomon ───────────────────────────────────────────────────────
            ['title' => 'Salomon Speedcross 6',           'vendor' => 'Salomon', 'price' => 2_299_000, 'compare' => null,      'type' => 'Trail Shoes',    'cat' => 'Trail & Hiking',     'tags' => ['Trail','Waterproof','Cushioned'],        'cols' => ['Trail & Adventure','Best Sellers'],    'img' => 21],
            ['title' => 'Salomon Ultra Glide 2',          'vendor' => 'Salomon', 'price' => 1_999_000, 'compare' => null,      'type' => 'Trail Shoes',    'cat' => 'Trail & Hiking',     'tags' => ['Trail','Cushioned','Lightweight'],       'cols' => ['Trail & Adventure'],                   'img' => 26],
            ['title' => 'Salomon XA Pro 3D v9 GTX',       'vendor' => 'Salomon', 'price' => 2_499_000, 'compare' => null,      'type' => 'Trail Shoes',    'cat' => 'Trail & Hiking',     'tags' => ['Gore-Tex','Waterproof','Trail'],         'cols' => ['Trail & Adventure','Premium Collection'],'img' => 21],
            ['title' => 'Salomon Sense Ride 5',           'vendor' => 'Salomon', 'price' => 1_899_000, 'compare' => 2_199_000, 'type' => 'Trail Shoes',    'cat' => 'Trail & Hiking',     'tags' => ['Trail','Lightweight','Breathable'],      'cols' => ['Trail & Adventure'],                   'img' => 26],
            ['title' => 'Salomon Pulsar Trail Pro',        'vendor' => 'Salomon', 'price' => 2_699_000, 'compare' => null,      'type' => 'Trail Shoes',    'cat' => 'Trail & Hiking',     'tags' => ['Carbon Plate','Trail','Lightweight'],    'cols' => ['Trail & Adventure','Premium Collection'],'img' => 15],
            ['title' => 'Salomon X-Adventure',            'vendor' => 'Salomon', 'price' => 1_999_000, 'compare' => null,      'type' => 'Trail Shoes',    'cat' => 'Trail & Hiking',     'tags' => ['Trail','Waterproof','New Arrival'],      'cols' => ['Trail & Adventure','New Arrivals'],    'img' => 21],
            ['title' => 'Salomon Quest 4 GTX',            'vendor' => 'Salomon', 'price' => 3_299_000, 'compare' => null,      'type' => 'Hiking Shoes',   'cat' => 'Trail & Hiking',     'tags' => ['Gore-Tex','Waterproof','Leather'],       'cols' => ['Trail & Adventure','Premium Collection'],'img' => 13],
            ['title' => 'Salomon Predict Hike Mid GTX',   'vendor' => 'Salomon', 'price' => 2_799_000, 'compare' => null,      'type' => 'Hiking Shoes',   'cat' => 'Trail & Hiking',     'tags' => ['Gore-Tex','Waterproof','Cushioned'],     'cols' => ['Trail & Adventure','Premium Collection'],'img' => 13],
            ['title' => 'Salomon Outline Mid GTX',        'vendor' => 'Salomon', 'price' => 2_099_000, 'compare' => null,      'type' => 'Hiking Shoes',   'cat' => 'Trail & Hiking',     'tags' => ['Gore-Tex','Waterproof'],                'cols' => ['Trail & Adventure'],                   'img' => 13],
            ['title' => 'Salomon S/LAB Ultra 3',          'vendor' => 'Salomon', 'price' => 4_999_000, 'compare' => null,      'type' => 'Trail Shoes',    'cat' => 'Trail & Hiking',     'tags' => ['Carbon Plate','Trail','New Arrival'],    'cols' => ['Trail & Adventure','Premium Collection','New Arrivals'],'img' => 27],
        ];
    }

    // ---------------------------------------------------------------------------

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('📦 Building image pool...');
        $this->buildImagePool();
        $this->command->info('✓ ' . count($this->imagePool) . ' images ready.');

        $categories  = Category::pluck('id', 'name');
        $tags        = Tag::pluck('id', 'name');
        $collections = Collection::pluck('id', 'title');

        $products = $this->catalogue();

        $this->command->info('🥾 Seeding ' . count($products) . ' shoe products...');
        $bar = $this->command->getOutput()->createProgressBar(count($products));
        $bar->start();

        $euSizes   = ['36', '37', '38', '39', '40', '41', '42', '43', '44', '45'];
        $position  = 1;

        foreach ($products as $idx => $data) {
            $handle = Str::slug($data['vendor'] . ' ' . $data['title']);

            // Skip if product already exists
            if (Product::where('handle', $handle)->exists()) {
                $bar->advance();
                continue;
            }

            // Pick featured image from pool
            $featuredFile = $this->imagePool[$data['img']] ?? ($this->imagePool[0] ?? null);

            $product = Product::create([
                'title'                 => $data['title'],
                'handle'                => $handle,
                'vendor'                => $data['vendor'],
                'product_type'          => $data['type'],
                'price'                 => $data['price'],
                'compare_at_price'      => $data['compare'],
                'description'           => $this->description($data),
                'status'                => 'active',
                'option1_name'          => 'Size (EU)',
                'published_at'          => now()->subDays($idx),
                'featured_store_file_id'=> $featuredFile?->id,
            ]);

            // Size variants
            foreach ($euSizes as $i => $size) {
                ProductVariant::create([
                    'product_id'         => $product->id,
                    'title'              => 'EU ' . $size,
                    'option1'            => 'EU ' . $size,
                    'price'              => $data['price'],
                    'compare_at_price'   => $data['compare'],
                    'sku'                => strtoupper(Str::limit($data['vendor'], 3, '')) . '-' . ($idx + 1) . '-EU' . $size,
                    'inventory_quantity' => rand(0, 25),
                    'position'           => $i + 1,
                    'requires_shipping'  => true,
                    'taxable'            => true,
                    'weight'             => round(rand(280, 450) / 100, 2),
                    'weight_unit'        => 'kg',
                ]);
            }

            // Attach featured image as media too
            if ($featuredFile) {
                $product->media()->syncWithoutDetaching([$featuredFile->id => ['position' => 1]]);
            }

            // Attach categories
            if (isset($categories[$data['cat']])) {
                $product->categories()->syncWithoutDetaching([$categories[$data['cat']]]);
            }

            // Attach tags
            $tagIds = [];
            foreach ($data['tags'] as $tagName) {
                if (isset($tags[$tagName])) {
                    $tagIds[] = $tags[$tagName];
                }
            }
            if ($tagIds) {
                $product->tags()->syncWithoutDetaching($tagIds);
            }

            // Attach collections
            foreach ($data['cols'] as $collectionTitle) {
                if (isset($collections[$collectionTitle])) {
                    $collectionId = $collections[$collectionTitle];
                    $collection   = Collection::find($collectionId);
                    if ($collection && ! $collection->products()->where('product_id', $product->id)->exists()) {
                        $collection->products()->attach($product->id, ['position' => $position++]);
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->info('');
        $this->command->info('✅ Done! 100 shoe products seeded.');
    }

    // ---------------------------------------------------------------------------

    private function buildImagePool(): void
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $dir  = 'store-files/seeds';
        $disk->makeDirectory($dir);

        foreach ($this->unsplashImages as $img) {
            $path = "{$dir}/{$img['id']}.jpg";

            if (! $disk->exists($path)) {
                try {
                    $response = Http::timeout(20)
                        ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                        ->get("https://images.unsplash.com/photo-{$img['id']}?w=800&h=800&fit=crop&q=80&auto=format");

                    if ($response->successful() && strlen($response->body()) > 5_000) {
                        $disk->put($path, $response->body());
                    } else {
                        $this->command->warn(" ✗ Skipped: {$img['id']}");
                        continue;
                    }
                } catch (\Throwable $e) {
                    $this->command->warn(" ✗ Failed: {$img['id']} — {$e->getMessage()}");
                    continue;
                }
            }

            $fullPath  = $disk->path($path);
            $storeFile = StoreFile::firstOrCreate(
                ['path' => $path, 'disk' => 'public'],
                [
                    'filename'  => basename($path),
                    'url'       => $disk->url($path),
                    'mime_type' => (file_exists($fullPath) ? (mime_content_type($fullPath) ?: 'image/jpeg') : 'image/jpeg'),
                    'size'      => $disk->exists($path) ? $disk->size($path) : 0,
                    'alt'       => $img['alt'],
                ]
            );

            $this->imagePool[$this->findIndex($img['id'])] = $storeFile;
        }
    }

    private function findIndex(string $id): int
    {
        foreach ($this->unsplashImages as $i => $img) {
            if ($img['id'] === $id) {
                return $i;
            }
        }
        return 0;
    }

    private function description(array $data): string
    {
        $typeDescriptions = [
            'Running Shoes'  => 'engineered for performance runners who demand the best in cushioning, responsiveness, and breathability on every run',
            'Casual Shoes'   => 'a timeless silhouette that blends street-ready style with all-day comfort for any occasion',
            'Basketball'     => 'designed for explosive play on the court, offering superior ankle support, cushioning, and traction',
            'Training Shoes' => 'built for high-intensity training sessions with stable support, flexibility, and durability',
            'Skate Shoes'    => 'crafted for skaters who need board feel, durability, and style — on and off the board',
            'Trail Shoes'    => 'purpose-built for off-road adventures with aggressive grip, protective overlays, and all-terrain traction',
            'Hiking Shoes'   => 'engineered for long days on the trail with waterproof protection, ankle stability, and long-lasting comfort',
        ];

        $desc = $typeDescriptions[$data['type']] ?? 'a must-have addition to any footwear collection';

        return "<p>The <strong>{$data['title']}</strong> by <strong>{$data['vendor']}</strong> is {$desc}.</p>"
             . "<p>Crafted from premium materials and built with {$data['vendor']}'s signature technology, these shoes deliver exceptional performance and style in every step.</p>"
             . "<p><strong>Features:</strong></p>"
             . "<ul><li>" . implode('</li><li>', $data['tags']) . "</li></ul>";
    }
}
