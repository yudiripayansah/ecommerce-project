<?php

namespace App\Http\Controllers;

use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(private readonly RajaOngkirService $rajaOngkir) {}

    public function provinces(): JsonResponse
    {
        $provinces = $this->rajaOngkir->getProvinces();

        return response()->json($provinces);
    }

    public function cities(Request $request): JsonResponse
    {
        $request->validate(['province_id' => 'required|integer']);

        $cities = $this->rajaOngkir->getCities($request->integer('province_id'));

        return response()->json($cities);
    }

    public function cost(Request $request): JsonResponse
    {
        $request->validate([
            'destination_city_id' => 'required|integer',
            'courier'             => 'nullable|string',
        ]);

        $cart        = session('cart', []);
        $totalWeight = max(1000, collect($cart)->sum(fn ($i) => $i['quantity'] * 1000));

        // Di local dev, langsung return mock tanpa hit API (API unreachable dari luar Indonesia)
        if (app()->environment('local')) {
            return response()->json($this->mockShippingOptions($totalWeight));
        }

        set_time_limit(60);

        $originCityId      = (int) config('services.rajaongkir.origin_city');
        $destinationCityId = $request->integer('destination_city_id');
        $couriers          = explode(':', config('services.rajaongkir.couriers', 'jne'));

        if ($request->filled('courier')) {
            $couriers = [$request->input('courier')];
        }

        $results = [];

        foreach ($couriers as $courier) {
            try {
                $costs = $this->rajaOngkir->getCost(
                    $originCityId,
                    $destinationCityId,
                    $totalWeight,
                    $courier,
                );

                foreach ($costs as $cost) {
                    foreach ($cost['costs'] as $detail) {
                        $results[] = [
                            'courier'     => strtoupper($courier),
                            'service'     => $cost['service'],
                            'description' => $cost['description'],
                            'cost'        => $detail['value'],
                            'etd'         => $detail['etd'],
                        ];
                    }
                }
            } catch (\Throwable) {
                // Kurir ini gagal, lanjut ke kurir berikutnya
            }
        }

        usort($results, fn ($a, $b) => $a['cost'] <=> $b['cost']);

        return response()->json($results);
    }

    private function mockShippingOptions(int $weightGrams): array
    {
        $kg   = max(1, ceil($weightGrams / 1000));
        $base = $kg * 1000;

        return [
            ['courier' => 'JNE',  'service' => 'REG',     'description' => 'Layanan Reguler',       'cost' => 9000  + $base, 'etd' => '2-3'],
            ['courier' => 'JNE',  'service' => 'OKE',     'description' => 'Ongkos Kirim Ekonomis', 'cost' => 7000  + $base, 'etd' => '3-5'],
            ['courier' => 'JNE',  'service' => 'YES',     'description' => 'Yakin Esok Sampai',     'cost' => 19000 + $base, 'etd' => '1-1'],
            ['courier' => 'TIKI', 'service' => 'REG',     'description' => 'Regular Service',       'cost' => 8000  + $base, 'etd' => '2-3'],
            ['courier' => 'TIKI', 'service' => 'ECO',     'description' => 'Economy Service',       'cost' => 6000  + $base, 'etd' => '4-6'],
            ['courier' => 'POS',  'service' => 'Pos Reguler', 'description' => 'Pos Reguler',       'cost' => 7500  + $base, 'etd' => '3-5'],
        ];
    }
}
