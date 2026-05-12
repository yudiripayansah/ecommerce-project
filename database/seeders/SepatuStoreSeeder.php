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

class SepatuStoreSeeder extends Seeder
{
    private array $imagePool = [];

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
    ];

    private array $collectionData = [
        [
            'title'       => 'New Arrivals',
            'handle'      => 'new-arrivals',
            'description' => '<p>Koleksi terbaru! Jadilah yang pertama melangkah dengan model sepatu paling fresh pilihan kami.</p>',
            'sort_order'  => 'created-descending',
            'img'         => 0,
        ],
        [
            'title'       => 'Best Sellers',
            'handle'      => 'best-sellers',
            'description' => '<p>Pilihan terlaris yang dicintai ribuan pelanggan. Sepatu-sepatu yang tidak pernah ketinggalan zaman.</p>',
            'sort_order'  => 'best-selling',
            'img'         => 8,
        ],
        [
            'title'       => 'Running Performance',
            'handle'      => 'running-performance',
            'description' => '<p>Direkayasa untuk setiap langkah. Sepatu lari dari brand terkemuka untuk performa terbaik di setiap jarak.</p>',
            'sort_order'  => 'manual',
            'img'         => 1,
        ],
        [
            'title'       => 'Street Style',
            'handle'      => 'street-style',
            'description' => '<p>Ikon kasual untuk kehidupan sehari-hari. Sneaker lifestyle klasik yang cocok dipadukan dengan outfit apapun.</p>',
            'sort_order'  => 'manual',
            'img'         => 2,
        ],
        [
            'title'       => 'Premium Collection',
            'handle'      => 'premium-collection',
            'description' => '<p>Tanpa kompromi. Koleksi premium dengan material terbaik, teknologi terdepan, dan keahlian pengerjaan tertinggi.</p>',
            'sort_order'  => 'price-descending',
            'img'         => 11,
        ],
        [
            'title'       => 'Trail & Adventure',
            'handle'      => 'trail-adventure',
            'description' => '<p>Taklukkan segala medan. Sepatu trail dan hiking yang dirancang untuk tetap melaju saat jalur semakin menantang.</p>',
            'sort_order'  => 'manual',
            'img'         => 13,
        ],
        [
            'title'       => 'Basketball',
            'handle'      => 'basketball',
            'description' => '<p>Dominasi lapangan. Sepatu basket dengan dukungan pergelangan kaki, cushioning, dan traksi untuk permainan eksplosif.</p>',
            'sort_order'  => 'manual',
            'img'         => 5,
        ],
        [
            'title'       => 'Training & Gym',
            'handle'      => 'training-gym',
            'description' => '<p>Berlatih lebih keras. Sepatu training yang stabil, fleksibel, dan tahan lama untuk sesi gym intensitas tinggi.</p>',
            'sort_order'  => 'manual',
            'img'         => 7,
        ],
        [
            'title'       => 'Skate',
            'handle'      => 'skate',
            'description' => '<p>Untuk skater sejati. Sepatu skate dengan board feel, daya tahan ekstra, dan gaya yang tidak pernah pudar.</p>',
            'sort_order'  => 'manual',
            'img'         => 10,
        ],
        [
            'title'       => 'Koleksi Terjangkau',
            'handle'      => 'koleksi-terjangkau',
            'description' => '<p>Kualitas tidak harus mahal. Temukan sepatu pilihan berkualitas dengan harga di bawah 1 juta rupiah.</p>',
            'sort_order'  => 'price-ascending',
            'img'         => 4,
        ],
    ];

    private function catalogue(): array
    {
        return [
            // ── Nike ─────────────────────────────────────────────────────────
            ['title' => 'Nike Air Max 270 React',          'vendor' => 'Nike', 'price' => 1_650_000, 'compare' => null,      'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Breathable','New Arrival'],          'cols' => ['New Arrivals','Running Performance'], 'img' => 0],
            ['title' => 'Nike React Infinity Run Flyknit 3','vendor'=> 'Nike', 'price' => 2_099_000, 'compare' => 2_499_000, 'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Knit Upper','Cushioned','Lightweight'],          'cols' => ['Running Performance','Premium Collection'], 'img' => 12],
            ['title' => 'Nike Air Pegasus 40',             'vendor' => 'Nike', 'price' => 1_549_000, 'compare' => null,      'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Breathable','Cushioned'],                        'cols' => ['Running Performance','Best Sellers'], 'img' => 8],
            ['title' => 'Nike Air Force 1 \'07',           'vendor' => 'Nike', 'price' => 1_349_000, 'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street','Best Seller'],                'cols' => ['Street Style','Best Sellers'], 'img' => 2],
            ['title' => 'Nike Dunk Low Retro',             'vendor' => 'Nike', 'price' => 1_349_000, 'compare' => 1_599_000, 'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Leather','Street'],                      'cols' => ['Street Style','New Arrivals'], 'img' => 11],
            ['title' => 'Nike Blazer Mid \'77 Vintage',   'vendor' => 'Nike', 'price' => 1_049_000, 'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Street','High Top'],                     'cols' => ['Street Style','Koleksi Terjangkau'], 'img' => 19],
            ['title' => 'Nike Zoom Fly 5',                 'vendor' => 'Nike', 'price' => 1_999_000, 'compare' => null,      'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Lightweight','Cushioned'],        'cols' => ['Running Performance','New Arrivals'], 'img' => 7],
            ['title' => 'Nike Metcon 8',                   'vendor' => 'Nike', 'price' => 1_799_000, 'compare' => null,      'type' => 'Training Shoes', 'cat' => 'Training & Gym',     'tags' => ['Cushioned','Breathable'],                        'cols' => ['Training & Gym'], 'img' => 7],
            ['title' => 'Nike Air Jordan 1 Retro High OG', 'vendor' => 'Nike', 'price' => 3_499_000, 'compare' => null,      'type' => 'Basketball',     'cat' => 'Basketball',         'tags' => ['Leather','High Top','Best Seller'],              'cols' => ['Basketball','Best Sellers','Premium Collection'], 'img' => 5],
            ['title' => 'Nike Air Zoom Alphafly Next% 2',  'vendor' => 'Nike', 'price' => 3_999_000, 'compare' => null,      'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Lightweight','Cushioned'],        'cols' => ['Premium Collection','Running Performance'], 'img' => 0],

            // ── Adidas ────────────────────────────────────────────────────────
            ['title' => 'Adidas Ultraboost 23',            'vendor' => 'Adidas', 'price' => 2_799_000, 'compare' => null,      'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Knit Upper','Breathable'],           'cols' => ['Running Performance','Premium Collection','Best Sellers'], 'img' => 1],
            ['title' => 'Adidas NMD R1',                   'vendor' => 'Adidas', 'price' => 1_699_000, 'compare' => 1_999_000, 'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Street','Knit Upper'],               'cols' => ['Street Style','New Arrivals'], 'img' => 3],
            ['title' => 'Adidas Stan Smith',               'vendor' => 'Adidas', 'price' => 999_000,   'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street','Best Seller'],                'cols' => ['Street Style','Best Sellers','Koleksi Terjangkau'], 'img' => 4],
            ['title' => 'Adidas Samba OG',                 'vendor' => 'Adidas', 'price' => 1_299_000, 'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Suede','Street'],                      'cols' => ['Street Style','New Arrivals'], 'img' => 11],
            ['title' => 'Adidas Gazelle Indoor',           'vendor' => 'Adidas', 'price' => 1_099_000, 'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Street'],                                'cols' => ['Street Style','Koleksi Terjangkau'], 'img' => 3],
            ['title' => 'Adidas Forum Low',                'vendor' => 'Adidas', 'price' => 1_099_000, 'compare' => 1_299_000, 'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street'],                              'cols' => ['Street Style','Koleksi Terjangkau'], 'img' => 2],
            ['title' => 'Adidas Superstar',                'vendor' => 'Adidas', 'price' => 1_049_000, 'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street','Best Seller'],                'cols' => ['Street Style','Best Sellers','Koleksi Terjangkau'], 'img' => 4],
            ['title' => 'Adidas ZX 22 Boost',             'vendor' => 'Adidas', 'price' => 1_549_000, 'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Street','Breathable'],               'cols' => ['Street Style','New Arrivals'], 'img' => 15],
            ['title' => 'Adidas Terrex Swift R3 GTX',     'vendor' => 'Adidas', 'price' => 2_199_000, 'compare' => null,      'type' => 'Hiking Shoes',   'cat' => 'Trail & Hiking',     'tags' => ['Waterproof','Gore-Tex','Trail'],                 'cols' => ['Trail & Adventure'], 'img' => 13],
            ['title' => 'Adidas Adizero Adios Pro 3',     'vendor' => 'Adidas', 'price' => 3_499_000, 'compare' => null,      'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Lightweight'],                    'cols' => ['Premium Collection','Running Performance'], 'img' => 7],

            // ── New Balance ───────────────────────────────────────────────────
            ['title' => 'New Balance 990v5 Made in USA',  'vendor' => 'New Balance', 'price' => 3_299_000, 'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Leather','Cushioned'],                   'cols' => ['Premium Collection','Best Sellers'], 'img' => 8],
            ['title' => 'New Balance 574 Core',           'vendor' => 'New Balance', 'price' => 1_199_000, 'compare' => 1_399_000, 'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Street'],                                'cols' => ['Street Style'], 'img' => 3],
            ['title' => 'New Balance Fresh Foam X 1080v13','vendor'=> 'New Balance', 'price' => 2_599_000, 'compare' => null,      'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Breathable','Knit Upper'],           'cols' => ['Running Performance','Premium Collection'], 'img' => 1],
            ['title' => 'New Balance 327',                'vendor' => 'New Balance', 'price' => 1_099_000, 'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Street','New Arrival'],                  'cols' => ['Street Style','New Arrivals','Koleksi Terjangkau'], 'img' => 14],
            ['title' => 'New Balance 9060',               'vendor' => 'New Balance', 'price' => 1_899_000, 'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Street','New Arrival'],              'cols' => ['Street Style','New Arrivals'], 'img' => 8],
            ['title' => 'New Balance Fuel Cell Rebel v3', 'vendor' => 'New Balance', 'price' => 1_999_000, 'compare' => 2_299_000, 'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Lightweight','Carbon Plate','Breathable'],       'cols' => ['Running Performance'], 'img' => 15],
            ['title' => 'New Balance 2002R',              'vendor' => 'New Balance', 'price' => 1_699_000, 'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Street','New Arrival'],                  'cols' => ['Street Style','New Arrivals'], 'img' => 8],
            ['title' => 'New Balance 997H',               'vendor' => 'New Balance', 'price' => 1_299_000, 'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Street'],                                'cols' => ['Street Style'], 'img' => 3],
            ['title' => 'New Balance 1906D Protection Pack','vendor'=> 'New Balance', 'price' => 1_899_000, 'compare' => null,     'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Leather','New Arrival'],                 'cols' => ['New Arrivals','Street Style'], 'img' => 14],
            ['title' => 'New Balance FuelCell SuperComp Elite v3','vendor'=>'New Balance','price'=>3_999_000,'compare'=>null,       'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Lightweight','Cushioned'],        'cols' => ['Running Performance','Premium Collection'], 'img' => 7],

            // ── Puma ──────────────────────────────────────────────────────────
            ['title' => 'Puma Suede Classic XXI',         'vendor' => 'Puma', 'price' => 799_000,   'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Suede','Street','Best Seller'],                  'cols' => ['Street Style','Best Sellers','Koleksi Terjangkau'], 'img' => 6],
            ['title' => 'Puma RS-X',                      'vendor' => 'Puma', 'price' => 1_299_000, 'compare' => 1_499_000, 'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Street'],                            'cols' => ['Street Style'], 'img' => 15],
            ['title' => 'Puma Cali Sport Mix',            'vendor' => 'Puma', 'price' => 1_099_000, 'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street'],                              'cols' => ['Street Style','Koleksi Terjangkau'], 'img' => 4],
            ['title' => 'Puma Velocity Nitro 2',          'vendor' => 'Puma', 'price' => 1_499_000, 'compare' => null,      'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Breathable','Lightweight'],          'cols' => ['Running Performance'], 'img' => 15],
            ['title' => 'Puma Deviate Nitro Elite 2',     'vendor' => 'Puma', 'price' => 2_799_000, 'compare' => null,      'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Lightweight','Cushioned'],        'cols' => ['Running Performance','Premium Collection'], 'img' => 0],
            ['title' => 'Puma Cell Venom',                'vendor' => 'Puma', 'price' => 1_199_000, 'compare' => 1_399_000, 'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Street'],                            'cols' => ['Street Style'], 'img' => 15],
            ['title' => 'Puma Smash v2 Leather',         'vendor' => 'Puma', 'price' => 549_000,   'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street'],                              'cols' => ['Street Style','Koleksi Terjangkau'], 'img' => 11],
            ['title' => 'Puma Fast-R Nitro Elite',        'vendor' => 'Puma', 'price' => 3_299_000, 'compare' => null,      'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Lightweight'],                    'cols' => ['Running Performance','Premium Collection'], 'img' => 7],
            ['title' => 'Puma Faster-R Nitro Elite 2',   'vendor' => 'Puma', 'price' => 3_699_000, 'compare' => null,      'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Carbon Plate','Cushioned','New Arrival'],        'cols' => ['Running Performance','Premium Collection','New Arrivals'], 'img' => 15],
            ['title' => 'Puma Anzarun 2.0',              'vendor' => 'Puma', 'price' => 699_000,   'compare' => 899_000,   'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Lightweight','Breathable'],                      'cols' => ['Running Performance','Koleksi Terjangkau'], 'img' => 1],

            // ── Reebok ────────────────────────────────────────────────────────
            ['title' => 'Reebok Classic Leather',         'vendor' => 'Reebok', 'price' => 899_000,   'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street','Best Seller'],                'cols' => ['Street Style','Best Sellers','Koleksi Terjangkau'], 'img' => 11],
            ['title' => 'Reebok Club C 85',               'vendor' => 'Reebok', 'price' => 849_000,   'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Leather','Street'],                              'cols' => ['Street Style','Koleksi Terjangkau'], 'img' => 4],
            ['title' => 'Reebok Nano X3',                 'vendor' => 'Reebok', 'price' => 1_499_000, 'compare' => null,      'type' => 'Training Shoes', 'cat' => 'Training & Gym',     'tags' => ['Cushioned','Breathable','Arch Support'],         'cols' => ['Training & Gym','New Arrivals'], 'img' => 7],
            ['title' => 'Reebok Floatride Energy 5',      'vendor' => 'Reebok', 'price' => 1_299_000, 'compare' => 1_499_000, 'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Cushioned','Lightweight'],                       'cols' => ['Running Performance'], 'img' => 8],
            ['title' => 'Reebok BB4500 Hi2',              'vendor' => 'Reebok', 'price' => 1_299_000, 'compare' => null,      'type' => 'Basketball',     'cat' => 'Basketball',         'tags' => ['High Top','Leather'],                            'cols' => ['Basketball','Best Sellers'], 'img' => 5],
            ['title' => 'Reebok Question Low',            'vendor' => 'Reebok', 'price' => 1_899_000, 'compare' => null,      'type' => 'Basketball',     'cat' => 'Basketball',         'tags' => ['Leather','Cushioned'],                           'cols' => ['Basketball'], 'img' => 5],
            ['title' => 'Reebok Instapump Fury 95',       'vendor' => 'Reebok', 'price' => 1_999_000, 'compare' => 2_399_000, 'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['Cushioned','Street'],                            'cols' => ['Street Style'], 'img' => 16],
            ['title' => 'Reebok Freestyle Hi',            'vendor' => 'Reebok', 'price' => 849_000,   'compare' => null,      'type' => 'Casual Shoes',   'cat' => 'Casual / Lifestyle', 'tags' => ['High Top','Leather'],                            'cols' => ['Street Style','Koleksi Terjangkau'], 'img' => 17],
            ['title' => 'Reebok Forever Floatride Grow',  'vendor' => 'Reebok', 'price' => 1_599_000, 'compare' => null,      'type' => 'Running Shoes',  'cat' => 'Running',            'tags' => ['Sustainable','Cushioned','Lightweight'],         'cols' => ['Running Performance'], 'img' => 9],
            ['title' => 'Reebok Lifter PR II',            'vendor' => 'Reebok', 'price' => 1_199_000, 'compare' => null,      'type' => 'Training Shoes', 'cat' => 'Training & Gym',     'tags' => ['Arch Support'],                                  'cols' => ['Training & Gym'], 'img' => 7],
        ];
    }

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('  [Sepatu] Membangun image pool...');
        $this->buildImagePool();
        $this->command->info('  [Sepatu] ' . count($this->imagePool) . ' gambar siap.');

        $this->seedCategories();
        $this->seedTags();
        $this->seedCollections();
        $this->seedProducts();
    }

    private function seedCategories(): void
    {
        $names = ['Running', 'Casual / Lifestyle', 'Basketball', 'Training & Gym', 'Skate', 'Trail & Hiking', 'Formal'];
        foreach ($names as $name) {
            Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
    }

    private function seedTags(): void
    {
        $names = [
            'Cushioned', 'Lightweight', 'Breathable', 'Waterproof', 'Gore-Tex',
            'Arch Support', 'Slip-On', 'High Top', 'Wide Fit', 'Sustainable',
            'Trail', 'Street', 'Carbon Plate', 'Knit Upper', 'Leather',
            'Suede', 'Canvas', 'Vegan', 'New Arrival', 'Best Seller',
        ];
        foreach ($names as $name) {
            Tag::firstOrCreate(['name' => $name]);
        }
    }

    private function seedCollections(): void
    {
        foreach ($this->collectionData as $data) {
            $img = $data['img'];
            unset($data['img']);
            $data['published_at'] = now()->subDays(array_search($data, $this->collectionData) + 1);

            $collection = Collection::firstOrCreate(['handle' => $data['handle']], $data);

            if ($collection->store_file_id) {
                continue;
            }

            $storeFile = $this->imagePool[$img] ?? ($this->imagePool[array_key_first($this->imagePool)] ?? null);
            if ($storeFile) {
                $collection->update(['store_file_id' => $storeFile->id]);
            }
        }
    }

    private function seedProducts(): void
    {
        $categories  = Category::pluck('id', 'name');
        $tags        = Tag::pluck('id', 'name');
        $collections = Collection::pluck('id', 'title');
        $products    = $this->catalogue();

        $this->command->info('  [Sepatu] Seeding ' . count($products) . ' produk sepatu...');
        $bar      = $this->command->getOutput()->createProgressBar(count($products));
        $bar->start();

        $euSizes  = ['36', '37', '38', '39', '40', '41', '42', '43', '44', '45'];
        $position = 1;

        foreach ($products as $idx => $data) {
            $handle = Str::slug($data['vendor'] . ' ' . $data['title']);

            if (Product::where('handle', $handle)->exists()) {
                $bar->advance();
                continue;
            }

            $featuredFile = $this->imagePool[$data['img']] ?? ($this->imagePool[array_key_first($this->imagePool)] ?? null);

            $product = Product::create([
                'title'                  => $data['title'],
                'handle'                 => $handle,
                'vendor'                 => $data['vendor'],
                'product_type'           => $data['type'],
                'price'                  => $data['price'],
                'compare_at_price'       => $data['compare'],
                'description'            => $this->description($data),
                'status'                 => 'active',
                'option1_name'           => 'Ukuran (EU)',
                'published_at'           => now()->subDays($idx),
                'featured_store_file_id' => $featuredFile?->id,
            ]);

            foreach ($euSizes as $i => $size) {
                ProductVariant::create([
                    'product_id'         => $product->id,
                    'title'              => 'EU ' . $size,
                    'option1'            => 'EU ' . $size,
                    'price'              => $data['price'],
                    'compare_at_price'   => $data['compare'],
                    'sku'                => strtoupper(Str::limit($data['vendor'], 3, '')) . '-' . ($idx + 1) . '-EU' . $size,
                    'inventory_quantity' => rand(0, 30),
                    'position'           => $i + 1,
                    'requires_shipping'  => true,
                    'taxable'            => true,
                    'weight'             => round(rand(280, 450) / 100, 2),
                    'weight_unit'        => 'kg',
                ]);
            }

            if ($featuredFile) {
                $product->media()->syncWithoutDetaching([$featuredFile->id => ['position' => 1]]);
            }

            if (isset($categories[$data['cat']])) {
                $product->categories()->syncWithoutDetaching([$categories[$data['cat']]]);
            }

            $tagIds = array_values(array_filter(array_map(fn ($t) => $tags[$t] ?? null, $data['tags'])));
            if ($tagIds) {
                $product->tags()->syncWithoutDetaching($tagIds);
            }

            foreach ($data['cols'] as $colTitle) {
                if (isset($collections[$colTitle])) {
                    $col = Collection::find($collections[$colTitle]);
                    if ($col && ! $col->products()->where('product_id', $product->id)->exists()) {
                        $col->products()->attach($product->id, ['position' => $position++]);
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->info('');
        $this->command->info('  [Sepatu] 50 produk selesai di-seed.');
    }

    private function buildImagePool(): void
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $dir  = 'store-files/seeds';
        $disk->makeDirectory($dir);

        foreach ($this->unsplashImages as $i => $img) {
            $path = "{$dir}/{$img['id']}.jpg";

            if (! $disk->exists($path)) {
                try {
                    $response = Http::timeout(20)
                        ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                        ->get("https://images.unsplash.com/photo-{$img['id']}?w=800&h=800&fit=crop&q=80&auto=format");

                    if ($response->successful() && strlen($response->body()) > 5_000) {
                        $disk->put($path, $response->body());
                    } else {
                        $this->command->warn("  ✗ Skip: {$img['id']}");
                        continue;
                    }
                } catch (\Throwable $e) {
                    $this->command->warn("  ✗ Gagal: {$img['id']} — {$e->getMessage()}");
                    continue;
                }
            }

            $fullPath  = $disk->path($path);
            $storeFile = StoreFile::firstOrCreate(
                ['path' => $path, 'disk' => 'public'],
                [
                    'filename'  => basename($path),
                    'url'       => $disk->url($path),
                    'mime_type' => file_exists($fullPath) ? (mime_content_type($fullPath) ?: 'image/jpeg') : 'image/jpeg',
                    'size'      => $disk->exists($path) ? $disk->size($path) : 0,
                    'alt'       => $img['alt'],
                ]
            );

            $this->imagePool[$i] = $storeFile;
        }
    }

    private function description(array $data): string
    {
        $typeDescs = [
            'Running Shoes'  => 'dirancang untuk pelari yang menuntut cushioning, responsivitas, dan breathability terbaik di setiap langkah',
            'Casual Shoes'   => 'silhouette timeless yang memadukan gaya street-ready dengan kenyamanan sepanjang hari untuk berbagai kesempatan',
            'Basketball'     => 'didesain untuk permainan eksplosif di lapangan, menawarkan ankle support, cushioning, dan traksi superior',
            'Training Shoes' => 'dibangun untuk sesi latihan intensitas tinggi dengan stabilitas, fleksibilitas, dan daya tahan yang handal',
            'Skate Shoes'    => 'untuk skater sejati yang butuh board feel, ketahanan ekstra, dan gaya — di atas maupun di luar papan',
            'Trail Shoes'    => 'dirancang khusus untuk petualangan off-road dengan grip agresif dan traksi segala medan',
            'Hiking Shoes'   => 'direkayasa untuk seharian di jalur dengan perlindungan waterproof, stabilitas pergelangan kaki, dan kenyamanan tahan lama',
        ];

        $desc = $typeDescs[$data['type']] ?? 'tambahan wajib untuk koleksi alas kaki Anda';

        return "<p><strong>{$data['title']}</strong> dari <strong>{$data['vendor']}</strong> adalah sepatu {$desc}.</p>"
             . "<p>Dibuat dari material premium dengan teknologi khas {$data['vendor']}, sepatu ini menghadirkan performa dan gaya luar biasa di setiap langkah.</p>"
             . "<p><strong>Fitur Unggulan:</strong></p>"
             . '<ul><li>' . implode('</li><li>', $data['tags']) . '</li></ul>';
    }
}
