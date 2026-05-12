<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey  = config('services.rajaongkir.api_key', '');
        $this->baseUrl = config('services.rajaongkir.base_url', 'https://api.rajaongkir.com/starter');
    }

    /**
     * Provinces are static — read from bundled JSON file, no API call needed.
     */
    public function getProvinces(): array
    {
        $path = $this->rajaongkirPath('provinces.json');

        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true) ?: [];
        }

        // Fallback: try API
        $response = $this->get('/province');
        return $response['rajaongkir']['results'] ?? [];
    }

    /**
     * Cities: read from seeded JSON file (created by rajaongkir:cache command).
     * Falls back to live API call if the file doesn't exist.
     */
    public function getCities(int $provinceId): array
    {
        $path = $this->rajaongkirPath("cities_{$provinceId}.json");

        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true) ?: [];
        }

        // Fallback: live API call
        $response = $this->get('/city', ['province' => $provinceId]);
        $cities   = $response['rajaongkir']['results'] ?? [];

        // Cache to file for next time
        if (! empty($cities)) {
            file_put_contents($path, json_encode($cities));
        }

        return $cities;
    }

    public function getCost(int $originCityId, int $destinationCityId, int $weightGrams, string $courier): array
    {
        $response = $this->post('/cost', [
            'origin'      => $originCityId,
            'destination' => $destinationCityId,
            'weight'      => $weightGrams,
            'courier'     => $courier,
        ]);

        return $response['rajaongkir']['results'][0]['costs'] ?? [];
    }

    private function rajaongkirPath(string $filename): string
    {
        // Use base_path so the path is always relative to the project root,
        // not affected by FilesystemTenancyBootstrapper changing storage_path().
        return base_path('storage/app/rajaongkir/' . $filename);
    }

    private function get(string $path, array $query = []): array
    {
        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->timeout(8)
                ->get($this->baseUrl . $path, $query);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning('RajaOngkir GET timeout/error: ' . $e->getMessage());
            return [];
        }
    }

    private function post(string $path, array $data): array
    {
        try {
            $response = Http::withHeaders(['key' => $this->apiKey])
                ->timeout(8)
                ->post($this->baseUrl . $path, $data);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning('RajaOngkir POST timeout/error: ' . $e->getMessage());
            return [];
        }
    }
}
