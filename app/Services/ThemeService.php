<?php

namespace App\Services;

class ThemeService
{
    public static function all(): array
    {
        return [
            'minimal' => [
                'label'    => 'Minimal',
                'desc'     => 'Desain bersih dan ringan. Cocok untuk semua jenis toko.',
                'features' => ['Header putih standar', 'Hero gelap elegan', 'Produk kartu persegi'],
                'preview'  => [
                    'header' => '#ffffff',
                    'hero'   => '#111827',
                    'card'   => '#f4f4f5',
                    'cta'    => '#ffffff',
                ],
            ],
            'dark' => [
                'label'    => 'Dark',
                'desc'     => 'Mode gelap penuh dengan aksen indigo. Terkesan premium.',
                'features' => ['Latar gelap di seluruh halaman', 'Aksen warna indigo', 'Hero dengan gradient'],
                'preview'  => [
                    'header' => '#111827',
                    'hero'   => '#030712',
                    'card'   => '#1f2937',
                    'cta'    => '#6366f1',
                ],
            ],
            'boutique' => [
                'label'    => 'Boutique',
                'desc'     => 'Logo terpusat, nuansa hangat. Ideal untuk fashion & lifestyle.',
                'features' => ['Logo di tengah header', 'Produk kartu portrait 3:4', 'Hero split dua kolom'],
                'preview'  => [
                    'header' => '#fafaf9',
                    'hero'   => '#292524',
                    'card'   => '#f5f5f4',
                    'cta'    => '#ffffff',
                ],
            ],
        ];
    }

    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    public static function exists(string $key): bool
    {
        return array_key_exists($key, self::all());
    }
}
