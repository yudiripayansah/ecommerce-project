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

class BajuStoreSeeder extends Seeder
{
    private array $imagePool = [];

    private array $unsplashImages = [
        ['id' => '1490481860517-d9d713b6fb6d', 'alt' => 'Fashion clothing collection'],
        ['id' => '1441986300917-64674bd600d8', 'alt' => 'Clothes hanging on rack'],
        ['id' => '1558618666-fcd25c85cd64', 'alt' => 'Fashion woman outfit'],
        ['id' => '1516762689617-e1cfffcbe80e', 'alt' => 'Clothing folded on shelf'],
        ['id' => '1555529669-e69e7aa0ba9a', 'alt' => 'Casual t-shirt on hanger'],
        ['id' => '1562572159-4efc207f5aff', 'alt' => 'Plain t-shirt flatlay'],
        ['id' => '1583744946564-b52ac1c389c8', 'alt' => 'Hoodie sweatshirt detail'],
        ['id' => '1594938298603-c8148c4b4819', 'alt' => 'Trousers pants flat lay'],
        ['id' => '1507679799987-c73779587ccf', 'alt' => 'Dress shirt formal'],
        ['id' => '1525507788-1e5ab05b03b9', 'alt' => 'Denim jacket blue'],
        ['id' => '1523381210434-271e8329d0ff', 'alt' => 'Fashion model casual wear'],
        ['id' => '1434389677669-e08b4cac3105', 'alt' => 'Fashion store clothing'],
        ['id' => '1548123378-d24a65a0c6ce', 'alt' => 'Casual outfit flatlay'],
        ['id' => '1469334031814-8cbb895dde90', 'alt' => 'Jacket outerwear lifestyle'],
        ['id' => '1535530992830-a2a99f2c6a50', 'alt' => 'Smart casual blazer'],
        ['id' => '1581044777550-4cfa2b3d4b84', 'alt' => 'Summer dress floral'],
        ['id' => '1552673597-d2c19ded9ae0', 'alt' => 'Formal suit professional'],
        ['id' => '1613480076573-2d280e07e5a8', 'alt' => 'Crewneck sweatshirt minimal'],
        ['id' => '1574180045827-681f239a0965', 'alt' => 'Jeans denim close up'],
        ['id' => '1620012253295-2e52b8c30d67', 'alt' => 'Polo shirt pastel color'],
    ];

    private array $collectionData = [
        [
            'title'       => 'New Arrivals',
            'handle'      => 'new-arrivals',
            'description' => '<p>Koleksi terbaru yang baru tiba! Tampil trendi dengan pilihan pakaian paling fresh dari brand-brand favorit.</p>',
            'sort_order'  => 'created-descending',
            'img'         => 0,
        ],
        [
            'title'       => 'Best Sellers',
            'handle'      => 'best-sellers',
            'description' => '<p>Pakaian terlaris pilihan pelanggan setia kami. Kenyamanan, kualitas, dan gaya dalam satu koleksi.</p>',
            'sort_order'  => 'best-selling',
            'img'         => 1,
        ],
        [
            'title'       => 'Kaos & T-Shirt',
            'handle'      => 'kaos-t-shirt',
            'description' => '<p>Koleksi kaos kasual untuk keseharian. Dari basic polos hingga graphic tee, semua ada di sini.</p>',
            'sort_order'  => 'manual',
            'img'         => 5,
        ],
        [
            'title'       => 'Kemeja & Polo',
            'handle'      => 'kemeja-polo',
            'description' => '<p>Tampil smart casual dengan koleksi kemeja dan polo pilihan. Sempurna untuk kantor maupun acara santai.</p>',
            'sort_order'  => 'manual',
            'img'         => 8,
        ],
        [
            'title'       => 'Celana & Jeans',
            'handle'      => 'celana-jeans',
            'description' => '<p>Dari celana chino elegan hingga jeans kasual. Temukan bawahan sempurna untuk melengkapi outfit Anda.</p>',
            'sort_order'  => 'manual',
            'img'         => 7,
        ],
        [
            'title'       => 'Jaket & Outer',
            'handle'      => 'jaket-outer',
            'description' => '<p>Koleksi jaket dan outwear untuk berbagai cuaca dan kesempatan. Stylish sekaligus fungsional.</p>',
            'sort_order'  => 'manual',
            'img'         => 9,
        ],
        [
            'title'       => 'Dress & Rok',
            'handle'      => 'dress-rok',
            'description' => '<p>Koleksi dress dan rok feminin yang elegan. Dari casual hingga formal, pilih yang sesuai suasana hati Anda.</p>',
            'sort_order'  => 'manual',
            'img'         => 15,
        ],
        [
            'title'       => 'Batik & Motif',
            'handle'      => 'batik-motif',
            'description' => '<p>Bangga memakai karya anak bangsa. Koleksi batik dan pakaian bermotif untuk tampilan berkarakter khas Indonesia.</p>',
            'sort_order'  => 'manual',
            'img'         => 11,
        ],
        [
            'title'       => 'Pakaian Formal',
            'handle'      => 'pakaian-formal',
            'description' => '<p>Tampil profesional dan berwibawa. Koleksi pakaian formal untuk rapat, presentasi, dan acara resmi.</p>',
            'sort_order'  => 'manual',
            'img'         => 16,
        ],
        [
            'title'       => 'Koleksi Premium',
            'handle'      => 'koleksi-premium',
            'description' => '<p>Material terbaik, potongan sempurna, detail halus. Koleksi premium untuk Anda yang tidak berkompromi dengan kualitas.</p>',
            'sort_order'  => 'price-descending',
            'img'         => 14,
        ],
    ];

    private function catalogue(): array
    {
        return [
            // ── Uniqlo ────────────────────────────────────────────────────────
            ['title' => 'Uniqlo AIRism Cotton Crew Neck T-Shirt',  'vendor' => 'Uniqlo', 'price' => 299_000, 'compare' => null,     'type' => 'Kaos',    'cat' => 'Atasan',  'tags' => ['Cotton','Breathable','Best Seller'],              'cols' => ['Kaos & T-Shirt','Best Sellers'],          'sizes' => 'apparel', 'img' => 5],
            ['title' => 'Uniqlo HEATTECH Cotton T-Shirt',          'vendor' => 'Uniqlo', 'price' => 149_000, 'compare' => null,     'type' => 'Kaos',    'cat' => 'Atasan',  'tags' => ['Cotton','Breathable'],                           'cols' => ['Kaos & T-Shirt'],                         'sizes' => 'apparel', 'img' => 4],
            ['title' => 'Uniqlo Oxford Slim-Fit Shirt',            'vendor' => 'Uniqlo', 'price' => 599_000, 'compare' => null,     'type' => 'Kemeja',  'cat' => 'Atasan',  'tags' => ['Cotton','Slim Fit','Smart Casual'],               'cols' => ['Kemeja & Polo','Best Sellers'],           'sizes' => 'apparel', 'img' => 8],
            ['title' => 'Uniqlo Stretch Slim-Fit Chinos',          'vendor' => 'Uniqlo', 'price' => 499_000, 'compare' => null,     'type' => 'Celana',  'cat' => 'Bawahan', 'tags' => ['Slim Fit','Stretch','Smart Casual'],              'cols' => ['Celana & Jeans','Best Sellers'],          'sizes' => 'waist',   'img' => 7],
            ['title' => 'Uniqlo Ultra Light Down Jacket',          'vendor' => 'Uniqlo', 'price' => 999_000, 'compare' => null,     'type' => 'Jaket',   'cat' => 'Outer',   'tags' => ['Lightweight','New Arrival'],                      'cols' => ['Jaket & Outer','New Arrivals'],           'sizes' => 'apparel', 'img' => 13],
            ['title' => 'Uniqlo Soft Fleece Full-Zip Hoodie',      'vendor' => 'Uniqlo', 'price' => 599_000, 'compare' => null,     'type' => 'Hoodie',  'cat' => 'Outer',   'tags' => ['Fleece','Breathable','Casual'],                   'cols' => ['Jaket & Outer'],                         'sizes' => 'apparel', 'img' => 6],
            ['title' => 'Uniqlo Smart Ankle Pants',                'vendor' => 'Uniqlo', 'price' => 549_000, 'compare' => null,     'type' => 'Celana',  'cat' => 'Bawahan', 'tags' => ['Slim Fit','Formal','Smart Casual'],               'cols' => ['Celana & Jeans','Pakaian Formal'],        'sizes' => 'waist',   'img' => 7],
            ['title' => 'Uniqlo Linen Cotton Blend Shirt',         'vendor' => 'Uniqlo', 'price' => 499_000, 'compare' => null,     'type' => 'Kemeja',  'cat' => 'Atasan',  'tags' => ['Linen','Breathable','Casual'],                    'cols' => ['Kemeja & Polo'],                         'sizes' => 'apparel', 'img' => 8],
            ['title' => 'Uniqlo Denim Straight Leg Jeans',         'vendor' => 'Uniqlo', 'price' => 599_000, 'compare' => null,     'type' => 'Jeans',   'cat' => 'Bawahan', 'tags' => ['Denim','Regular Fit','Casual'],                   'cols' => ['Celana & Jeans'],                        'sizes' => 'waist',   'img' => 18],
            ['title' => 'Uniqlo Supima Cotton Crewneck Sweater',   'vendor' => 'Uniqlo', 'price' => 699_000, 'compare' => null,     'type' => 'Sweater', 'cat' => 'Outer',   'tags' => ['Cotton','Slim Fit','Smart Casual'],               'cols' => ['Jaket & Outer','Koleksi Premium'],        'sizes' => 'apparel', 'img' => 17],

            // ── H&M ──────────────────────────────────────────────────────────
            ['title' => 'H&M Basic Cotton T-Shirt',                'vendor' => 'H&M', 'price' => 149_000, 'compare' => null,     'type' => 'Kaos',    'cat' => 'Atasan',  'tags' => ['Cotton','Regular Fit','Casual'],                  'cols' => ['Kaos & T-Shirt'],                        'sizes' => 'apparel', 'img' => 4],
            ['title' => 'H&M Regular Fit Oxford Shirt',            'vendor' => 'H&M', 'price' => 349_000, 'compare' => null,     'type' => 'Kemeja',  'cat' => 'Atasan',  'tags' => ['Cotton','Regular Fit','Smart Casual'],            'cols' => ['Kemeja & Polo'],                         'sizes' => 'apparel', 'img' => 8],
            ['title' => 'H&M Slim Fit Chino Trousers',             'vendor' => 'H&M', 'price' => 399_000, 'compare' => null,     'type' => 'Celana',  'cat' => 'Bawahan', 'tags' => ['Slim Fit','Stretch','Smart Casual'],              'cols' => ['Celana & Jeans'],                        'sizes' => 'waist',   'img' => 7],
            ['title' => 'H&M Oversized Graphic Hoodie',            'vendor' => 'H&M', 'price' => 449_000, 'compare' => null,     'type' => 'Hoodie',  'cat' => 'Outer',   'tags' => ['Cotton','Oversize','Street','New Arrival'],       'cols' => ['Jaket & Outer','New Arrivals'],           'sizes' => 'apparel', 'img' => 6],
            ['title' => 'H&M Regular Fit Linen Blazer',            'vendor' => 'H&M', 'price' => 799_000, 'compare' => null,     'type' => 'Blazer',  'cat' => 'Outer',   'tags' => ['Linen','Regular Fit','Formal'],                   'cols' => ['Jaket & Outer','Pakaian Formal'],         'sizes' => 'apparel', 'img' => 14],
            ['title' => 'H&M Straight Leg Jeans',                  'vendor' => 'H&M', 'price' => 449_000, 'compare' => 549_000,  'type' => 'Jeans',   'cat' => 'Bawahan', 'tags' => ['Denim','Regular Fit','Casual'],                   'cols' => ['Celana & Jeans'],                        'sizes' => 'waist',   'img' => 18],
            ['title' => 'H&M Floral Wrap Dress',                   'vendor' => 'H&M', 'price' => 399_000, 'compare' => null,     'type' => 'Dress',   'cat' => 'Dress',   'tags' => ['Cotton','Regular Fit','Casual','New Arrival'],    'cols' => ['Dress & Rok','New Arrivals'],             'sizes' => 'apparel', 'img' => 15],
            ['title' => 'H&M Cotton Jersey Joggers',               'vendor' => 'H&M', 'price' => 299_000, 'compare' => null,     'type' => 'Celana',  'cat' => 'Bawahan', 'tags' => ['Cotton','Loose Fit','Casual'],                    'cols' => ['Celana & Jeans'],                        'sizes' => 'apparel', 'img' => 7],
            ['title' => 'H&M Oversized Denim Jacket',              'vendor' => 'H&M', 'price' => 549_000, 'compare' => null,     'type' => 'Jaket',   'cat' => 'Outer',   'tags' => ['Denim','Oversize','Street'],                      'cols' => ['Jaket & Outer'],                         'sizes' => 'apparel', 'img' => 9],
            ['title' => 'H&M Slim Fit Formal Trousers',            'vendor' => 'H&M', 'price' => 499_000, 'compare' => null,     'type' => 'Celana',  'cat' => 'Bawahan', 'tags' => ['Polyester','Slim Fit','Formal'],                  'cols' => ['Celana & Jeans','Pakaian Formal'],        'sizes' => 'waist',   'img' => 16],

            // ── Erigo ─────────────────────────────────────────────────────────
            ['title' => 'Erigo T-Shirt Basic Polos Oversize',      'vendor' => 'Erigo', 'price' =>  99_000, 'compare' => null,     'type' => 'Kaos',    'cat' => 'Atasan',  'tags' => ['Cotton','Oversize','Street','Best Seller'],       'cols' => ['Kaos & T-Shirt','Best Sellers'],          'sizes' => 'apparel', 'img' => 5],
            ['title' => 'Erigo Kemeja Flanel Pria',                'vendor' => 'Erigo', 'price' => 299_000, 'compare' => null,     'type' => 'Kemeja',  'cat' => 'Atasan',  'tags' => ['Regular Fit','Casual'],                          'cols' => ['Kemeja & Polo'],                         'sizes' => 'apparel', 'img' => 8],
            ['title' => 'Erigo Celana Jogger Pria',                'vendor' => 'Erigo', 'price' => 249_000, 'compare' => null,     'type' => 'Celana',  'cat' => 'Bawahan', 'tags' => ['Cotton','Regular Fit','Casual','Best Seller'],    'cols' => ['Celana & Jeans','Best Sellers'],          'sizes' => 'apparel', 'img' => 7],
            ['title' => 'Erigo Bomber Jacket',                     'vendor' => 'Erigo', 'price' => 449_000, 'compare' => null,     'type' => 'Jaket',   'cat' => 'Outer',   'tags' => ['Polyester','Regular Fit','Street','New Arrival'], 'cols' => ['Jaket & Outer','New Arrivals'],           'sizes' => 'apparel', 'img' => 13],
            ['title' => 'Erigo Polo Shirt Pique Basic',            'vendor' => 'Erigo', 'price' => 199_000, 'compare' => null,     'type' => 'Polo',    'cat' => 'Atasan',  'tags' => ['Cotton','Regular Fit','Smart Casual'],            'cols' => ['Kemeja & Polo'],                         'sizes' => 'apparel', 'img' => 19],
            ['title' => 'Erigo Hoodie Fleece Pullover',            'vendor' => 'Erigo', 'price' => 349_000, 'compare' => null,     'type' => 'Hoodie',  'cat' => 'Outer',   'tags' => ['Fleece','Regular Fit','Street'],                  'cols' => ['Jaket & Outer'],                         'sizes' => 'apparel', 'img' => 6],
            ['title' => 'Erigo Kemeja Batik Casual Pria',          'vendor' => 'Erigo', 'price' => 299_000, 'compare' => null,     'type' => 'Batik',   'cat' => 'Atasan',  'tags' => ['Batik','Regular Fit','Casual'],           'cols' => ['Batik & Motif'],                         'sizes' => 'apparel', 'img' => 11],
            ['title' => 'Erigo Cargo Pants Pria',                  'vendor' => 'Erigo', 'price' => 349_000, 'compare' => null,     'type' => 'Celana',  'cat' => 'Bawahan', 'tags' => ['Regular Fit','Street'],                          'cols' => ['Celana & Jeans'],                        'sizes' => 'apparel', 'img' => 7],
            ['title' => 'Erigo Sweater Rajut Pria',                'vendor' => 'Erigo', 'price' => 299_000, 'compare' => null,     'type' => 'Sweater', 'cat' => 'Outer',   'tags' => ['Casual','New Arrival'],                          'cols' => ['Jaket & Outer','New Arrivals'],           'sizes' => 'apparel', 'img' => 17],
            ['title' => 'Erigo Celana Pendek Sporty',              'vendor' => 'Erigo', 'price' => 149_000, 'compare' => null,     'type' => 'Celana',  'cat' => 'Bawahan', 'tags' => ['Polyester','Regular Fit','Casual'],               'cols' => ['Celana & Jeans'],                        'sizes' => 'apparel', 'img' => 7],
            ['title' => 'Erigo Kemeja Batik Motif Mega Mendung',   'vendor' => 'Erigo', 'price' => 329_000, 'compare' => null,     'type' => 'Batik',   'cat' => 'Atasan',  'tags' => ['Batik','Regular Fit','Smart Casual'],             'cols' => ['Batik & Motif','New Arrivals'],           'sizes' => 'apparel', 'img' => 11],

            // ── Zara ──────────────────────────────────────────────────────────
            ['title' => 'Zara Slim Fit Oxford Shirt',              'vendor' => 'Zara', 'price' =>  699_000, 'compare' => null,     'type' => 'Kemeja',  'cat' => 'Atasan',  'tags' => ['Cotton','Slim Fit','Smart Casual','New Arrival'], 'cols' => ['Kemeja & Polo','New Arrivals'],           'sizes' => 'apparel', 'img' => 8],
            ['title' => 'Zara Printed Graphic T-Shirt',            'vendor' => 'Zara', 'price' =>  349_000, 'compare' => null,     'type' => 'Kaos',    'cat' => 'Atasan',  'tags' => ['Cotton','Regular Fit','Street'],                  'cols' => ['Kaos & T-Shirt'],                        'sizes' => 'apparel', 'img' => 5],
            ['title' => 'Zara Smart Formal Trousers',              'vendor' => 'Zara', 'price' =>  899_000, 'compare' => null,     'type' => 'Celana',  'cat' => 'Bawahan', 'tags' => ['Slim Fit','Formal'],                              'cols' => ['Celana & Jeans','Pakaian Formal','Koleksi Premium'], 'sizes' => 'waist', 'img' => 16],
            ['title' => 'Zara Oversized Textured Blazer',          'vendor' => 'Zara', 'price' => 1_499_000,'compare' => null,     'type' => 'Blazer',  'cat' => 'Outer',   'tags' => ['Oversize','Formal','New Arrival'],                'cols' => ['Jaket & Outer','Pakaian Formal','Koleksi Premium'], 'sizes' => 'apparel', 'img' => 14],
            ['title' => 'Zara Denim Straight Jeans',               'vendor' => 'Zara', 'price' =>  799_000, 'compare' => null,     'type' => 'Jeans',   'cat' => 'Bawahan', 'tags' => ['Denim','Regular Fit','Casual'],                   'cols' => ['Celana & Jeans'],                        'sizes' => 'waist',   'img' => 18],
            ['title' => 'Zara Floral Mini Dress',                  'vendor' => 'Zara', 'price' =>  799_000, 'compare' => 999_000,  'type' => 'Dress',   'cat' => 'Dress',   'tags' => ['Regular Fit','Casual','Best Seller'],             'cols' => ['Dress & Rok','Best Sellers'],             'sizes' => 'apparel', 'img' => 15],
            ['title' => 'Zara Technical Bomber Jacket',            'vendor' => 'Zara', 'price' => 1_299_000,'compare' => null,     'type' => 'Jaket',   'cat' => 'Outer',   'tags' => ['Polyester','Regular Fit','Street'],               'cols' => ['Jaket & Outer','Koleksi Premium'],        'sizes' => 'apparel', 'img' => 9],
            ['title' => 'Zara Textured Knit Polo',                 'vendor' => 'Zara', 'price' =>  599_000, 'compare' => null,     'type' => 'Polo',    'cat' => 'Atasan',  'tags' => ['Regular Fit','Smart Casual','New Arrival'],       'cols' => ['Kemeja & Polo','New Arrivals'],           'sizes' => 'apparel', 'img' => 19],
            ['title' => 'Zara Midi Pleated Skirt',                 'vendor' => 'Zara', 'price' =>  699_000, 'compare' => null,     'type' => 'Rok',     'cat' => 'Dress',   'tags' => ['Regular Fit','Casual'],                          'cols' => ['Dress & Rok'],                           'sizes' => 'apparel', 'img' => 15],
            ['title' => 'Zara Linen Blend Wide Trousers',          'vendor' => 'Zara', 'price' =>  799_000, 'compare' => null,     'type' => 'Celana',  'cat' => 'Bawahan', 'tags' => ['Linen','Loose Fit','Smart Casual'],               'cols' => ['Celana & Jeans','Koleksi Premium'],       'sizes' => 'waist',   'img' => 7],

            // ── Levi's ────────────────────────────────────────────────────────
            ['title' => 'Levi\'s 501 Original Fit Jeans',         'vendor' => 'Levi\'s', 'price' => 1_099_000, 'compare' => null,     'type' => 'Jeans',   'cat' => 'Bawahan', 'tags' => ['Denim','Regular Fit','Street','Best Seller'],     'cols' => ['Celana & Jeans','Best Sellers'],          'sizes' => 'waist', 'img' => 18],
            ['title' => 'Levi\'s 511 Slim Fit Jeans',             'vendor' => 'Levi\'s', 'price' => 1_099_000, 'compare' => null,     'type' => 'Jeans',   'cat' => 'Bawahan', 'tags' => ['Denim','Slim Fit','Casual'],                      'cols' => ['Celana & Jeans'],                        'sizes' => 'waist', 'img' => 18],
            ['title' => 'Levi\'s Graphic Crewneck T-Shirt',       'vendor' => 'Levi\'s', 'price' =>  349_000, 'compare' => null,     'type' => 'Kaos',    'cat' => 'Atasan',  'tags' => ['Cotton','Regular Fit','Street'],                  'cols' => ['Kaos & T-Shirt'],                        'sizes' => 'apparel', 'img' => 5],
            ['title' => 'Levi\'s Trucker Denim Jacket',           'vendor' => 'Levi\'s', 'price' => 1_299_000, 'compare' => null,     'type' => 'Jaket',   'cat' => 'Outer',   'tags' => ['Denim','Regular Fit','Street','Best Seller'],     'cols' => ['Jaket & Outer','Best Sellers'],           'sizes' => 'apparel', 'img' => 9],
            ['title' => 'Levi\'s Barstow Western Shirt',          'vendor' => 'Levi\'s', 'price' =>  799_000, 'compare' => null,     'type' => 'Kemeja',  'cat' => 'Atasan',  'tags' => ['Cotton','Regular Fit','Casual'],                  'cols' => ['Kemeja & Polo'],                         'sizes' => 'apparel', 'img' => 8],
            ['title' => 'Levi\'s 505 Regular Fit Jeans',          'vendor' => 'Levi\'s', 'price' =>  999_000, 'compare' => null,     'type' => 'Jeans',   'cat' => 'Bawahan', 'tags' => ['Denim','Regular Fit','Casual'],                   'cols' => ['Celana & Jeans'],                        'sizes' => 'waist', 'img' => 18],
            ['title' => 'Levi\'s High Rise Mom Jeans',            'vendor' => 'Levi\'s', 'price' => 1_099_000, 'compare' => null,     'type' => 'Jeans',   'cat' => 'Bawahan', 'tags' => ['Denim','Regular Fit','Casual','New Arrival'],     'cols' => ['Celana & Jeans','New Arrivals'],          'sizes' => 'waist', 'img' => 18],
            ['title' => 'Levi\'s Housemark Polo Shirt',           'vendor' => 'Levi\'s', 'price' =>  549_000, 'compare' => null,     'type' => 'Polo',    'cat' => 'Atasan',  'tags' => ['Cotton','Regular Fit','Casual'],                  'cols' => ['Kemeja & Polo'],                         'sizes' => 'apparel', 'img' => 19],
            ['title' => 'Levi\'s Sherpa Trucker Jacket',          'vendor' => 'Levi\'s', 'price' => 1_599_000, 'compare' => null,     'type' => 'Jaket',   'cat' => 'Outer',   'tags' => ['Regular Fit','Casual','New Arrival'],             'cols' => ['Jaket & Outer','New Arrivals','Koleksi Premium'], 'sizes' => 'apparel', 'img' => 13],
            ['title' => 'Levi\'s Logo Crewneck Sweatshirt',       'vendor' => 'Levi\'s', 'price' =>  699_000, 'compare' => null,     'type' => 'Hoodie',  'cat' => 'Outer',   'tags' => ['Cotton','Regular Fit','Casual'],                  'cols' => ['Jaket & Outer'],                         'sizes' => 'apparel', 'img' => 17],
        ];
    }

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('  [Baju] Membangun image pool...');
        $this->buildImagePool();
        $this->command->info('  [Baju] ' . count($this->imagePool) . ' gambar siap.');

        $this->seedCategories();
        $this->seedTags();
        $this->seedCollections();
        $this->seedProducts();
    }

    private function seedCategories(): void
    {
        $names = ['Atasan', 'Bawahan', 'Outer', 'Dress', 'Batik', 'Aksesori'];
        foreach ($names as $name) {
            Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
    }

    private function seedTags(): void
    {
        $names = [
            'Cotton', 'Linen', 'Denim', 'Fleece', 'Polyester',
            'Oversize', 'Slim Fit', 'Regular Fit', 'Loose Fit',
            'Casual', 'Formal', 'Street', 'Smart Casual',
            'New Arrival', 'Best Seller',
            'Breathable', 'Waterproof', 'Stretch', 'Batik',
        ];
        foreach ($names as $name) {
            Tag::firstOrCreate(['name' => $name]);
        }
    }

    private function seedCollections(): void
    {
        foreach ($this->collectionData as $i => $data) {
            $img = $data['img'];
            unset($data['img']);
            $data['published_at'] = now()->subDays($i + 1);

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

        $this->command->info('  [Baju] Seeding ' . count($products) . ' produk pakaian...');
        $bar      = $this->command->getOutput()->createProgressBar(count($products));
        $bar->start();

        $apparelSizes = ['S', 'M', 'L', 'XL', 'XXL'];
        $waistSizes   = ['28', '30', '32', '34', '36', '38'];
        $position     = 1;

        foreach ($products as $idx => $data) {
            $handle = Str::slug($data['vendor'] . ' ' . $data['title']);

            if (Product::where('handle', $handle)->exists()) {
                $bar->advance();
                continue;
            }

            $featuredFile = $this->imagePool[$data['img']] ?? ($this->imagePool[array_key_first($this->imagePool)] ?? null);
            $sizes        = $data['sizes'] === 'waist' ? $waistSizes : $apparelSizes;
            $option1Name  = $data['sizes'] === 'waist' ? 'Lingkar Pinggang' : 'Ukuran';

            $product = Product::create([
                'title'                  => $data['title'],
                'handle'                 => $handle,
                'vendor'                 => $data['vendor'],
                'product_type'           => $data['type'],
                'price'                  => $data['price'],
                'compare_at_price'       => $data['compare'],
                'description'            => $this->description($data),
                'status'                 => 'active',
                'option1_name'           => $option1Name,
                'published_at'           => now()->subDays($idx),
                'featured_store_file_id' => $featuredFile?->id,
            ]);

            foreach ($sizes as $i => $size) {
                ProductVariant::create([
                    'product_id'         => $product->id,
                    'title'              => $size,
                    'option1'            => $size,
                    'price'              => $data['price'],
                    'compare_at_price'   => $data['compare'],
                    'sku'                => strtoupper(Str::limit(str_replace("'", '', $data['vendor']), 3, '')) . '-' . ($idx + 1) . '-' . $size,
                    'inventory_quantity' => rand(0, 50),
                    'position'           => $i + 1,
                    'requires_shipping'  => true,
                    'taxable'            => true,
                    'weight'             => round(rand(30, 80) / 100, 2),
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
        $this->command->info('  [Baju] 50 produk selesai di-seed.');
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
            'Kaos'    => 'kaos kasual yang nyaman dipakai sepanjang hari, terbuat dari material berkualitas dengan potongan yang pas di badan',
            'Kemeja'  => 'kemeja dengan potongan yang rapi dan material berkualitas, cocok untuk tampilan smart casual maupun formal',
            'Polo'    => 'polo shirt klasik dengan material pique premium yang memberikan tampilan rapi namun tetap santai',
            'Hoodie'  => 'hoodie dengan material lembut dan hangat, sempurna untuk berbagai kesempatan kasual',
            'Sweater' => 'sweater dengan bahan rajut premium yang memberikan kehangatan dan tampilan stylish di segala musim',
            'Jaket'   => 'jaket serbaguna yang memadukan fungsi dan gaya, cocok untuk berbagai kondisi cuaca',
            'Blazer'  => 'blazer elegan dengan potongan modern yang mengangkat penampilan Anda ke level berikutnya',
            'Celana'  => 'celana dengan potongan yang nyaman dan material berkualitas untuk aktivitas sehari-hari',
            'Jeans'   => 'jeans denim ikonik yang tak lekang oleh waktu, pas di badan dan tahan lama untuk daily wear',
            'Dress'   => 'dress feminin yang mengalir indah, sempurna untuk berbagai kesempatan dari kasual hingga semi-formal',
            'Rok'     => 'rok dengan potongan yang flattering dan material berkualitas untuk tampilan yang effortlessly chic',
            'Batik'   => 'pakaian batik dengan motif khas Indonesia yang memadukan keindahan budaya lokal dengan sentuhan modern',
        ];

        $desc = $typeDescs[$data['type']] ?? 'pakaian berkualitas dengan desain modern yang nyaman dipakai sepanjang hari';

        return "<p><strong>{$data['title']}</strong> dari <strong>{$data['vendor']}</strong> adalah {$desc}.</p>"
             . "<p>Dibuat dengan standar kualitas tinggi khas {$data['vendor']}, pakaian ini hadir dengan detail yang teliti untuk memastikan kenyamanan dan gaya terbaik.</p>"
             . "<p><strong>Detail Produk:</strong></p>"
             . '<ul><li>' . implode('</li><li>', $data['tags']) . '</li></ul>';
    }
}
