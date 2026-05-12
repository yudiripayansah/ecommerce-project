<?php

namespace App\Console\Commands;

use App\Services\RajaOngkirService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CacheRajaOngkirData extends Command
{
    protected $signature   = 'rajaongkir:cache {--province= : Cache cities for a specific province ID only}';
    protected $description = 'Fetch dan simpan data provinsi & kota dari RajaOngkir ke file lokal';

    public function handle(): int
    {
        $apiKey  = config('services.rajaongkir.api_key');
        $baseUrl = config('services.rajaongkir.base_url', 'https://api.rajaongkir.com/starter');

        if (! $apiKey) {
            $this->error('RAJAONGKIR_API_KEY belum diisi di .env');
            return self::FAILURE;
        }

        $dir = storage_path('app/rajaongkir');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // ── Provinsi ──────────────────────────────────────────────────────
        $this->info('Mengambil data provinsi...');
        $resp = Http::withHeaders(['key' => $apiKey])->timeout(30)->get($baseUrl . '/province');
        $provinces = $resp->json()['rajaongkir']['results'] ?? [];

        if (empty($provinces)) {
            $this->error('Gagal mengambil data provinsi. Cek API key dan koneksi.');
            return self::FAILURE;
        }

        file_put_contents($dir . '/provinces.json', json_encode($provinces, JSON_PRETTY_PRINT));
        $this->info('✓ ' . count($provinces) . ' provinsi disimpan.');

        // ── Kota ─────────────────────────────────────────────────────────
        $onlyProvince = $this->option('province');
        $targetProvinces = $onlyProvince
            ? array_filter($provinces, fn ($p) => $p['province_id'] == $onlyProvince)
            : $provinces;

        $this->info('Mengambil data kota (' . count($targetProvinces) . ' provinsi)...');
        $bar = $this->output->createProgressBar(count($targetProvinces));
        $bar->start();

        foreach ($targetProvinces as $province) {
            $provinceId = $province['province_id'];
            $cityResp   = Http::withHeaders(['key' => $apiKey])
                ->timeout(30)
                ->get($baseUrl . '/city', ['province' => $provinceId]);

            $cities = $cityResp->json()['rajaongkir']['results'] ?? [];
            if (! empty($cities)) {
                file_put_contents($dir . "/cities_{$provinceId}.json", json_encode($cities, JSON_PRETTY_PRINT));
            }

            $bar->advance();
            usleep(200_000); // 200ms delay agar tidak rate-limited
        }

        $bar->finish();
        $this->newLine();
        $this->info('✓ Data kota berhasil disimpan ke storage/app/rajaongkir/');
        $this->info('Jalankan ulang server untuk memuat data terbaru.');

        return self::SUCCESS;
    }
}
