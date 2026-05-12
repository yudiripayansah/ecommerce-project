<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\Tenant;
use Database\Seeders\BajuStoreSeeder;
use Database\Seeders\SepatuStoreSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class DemoSeed extends Command
{
    protected $signature = 'demo:seed
        {--fresh : Hapus tenant lama dan seed ulang dari awal}';

    protected $description = 'Buat 2 toko demo (toko-sepatu & toko-baju) lengkap dengan produk, koleksi, dan gambar.';

    public function handle(): int
    {
        $this->seedStore(
            id:           'toko-sepatu',
            name:         'Toko Sepatu Nusantara',
            adminEmail:   'admin@toko-sepatu.test',
            seederClass:  SepatuStoreSeeder::class,
            settings: [
                'store_name'    => 'Toko Sepatu Nusantara',
                'store_email'   => 'info@tokosepatu.test',
                'store_phone'   => '081234567890',
                'bank_accounts' => json_encode([[
                    'bank_name'      => 'BCA',
                    'account_number' => '1234567890',
                    'account_holder' => 'Toko Sepatu Nusantara',
                ]]),
            ],
        );

        $this->newLine();

        $this->seedStore(
            id:           'toko-baju',
            name:         'Butik Baju Nusantara',
            adminEmail:   'admin@toko-baju.test',
            seederClass:  BajuStoreSeeder::class,
            settings: [
                'store_name'    => 'Butik Baju Nusantara',
                'store_email'   => 'info@butikbaju.test',
                'store_phone'   => '089876543210',
                'bank_accounts' => json_encode([[
                    'bank_name'      => 'Mandiri',
                    'account_number' => '0987654321',
                    'account_holder' => 'Butik Baju Nusantara',
                ]]),
            ],
        );

        $this->newLine();
        $this->info('════════════════════════════════════════');
        $this->info(' Demo seeding selesai!');
        $this->info('════════════════════════════════════════');
        $this->newLine();

        $centralDomain = env('CENTRAL_DOMAIN', 'localhost');

        $this->table(
            ['Toko', 'URL Storefront', 'Admin Panel', 'Login'],
            [
                [
                    'Toko Sepatu Nusantara',
                    "http://toko-sepatu.{$centralDomain}:8000",
                    "http://toko-sepatu.{$centralDomain}:8000/admin",
                    'admin@toko-sepatu.test / demo123',
                ],
                [
                    'Butik Baju Nusantara',
                    "http://toko-baju.{$centralDomain}:8000",
                    "http://toko-baju.{$centralDomain}:8000/admin",
                    'admin@toko-baju.test / demo123',
                ],
            ]
        );

        $this->newLine();
        $this->line('Tambahkan ke <comment>/etc/hosts</comment> jika belum ada:');
        $this->line("  127.0.0.1  toko-sepatu.{$centralDomain}");
        $this->line("  127.0.0.1  toko-baju.{$centralDomain}");

        return self::SUCCESS;
    }

    private function seedStore(
        string $id,
        string $name,
        string $adminEmail,
        string $seederClass,
        array  $settings,
    ): void {
        $this->line('────────────────────────────────────────');
        $this->info("Toko: {$name} [{$id}]");

        // --fresh: hapus tenant lama
        if ($this->option('fresh') && ($existing = Tenant::find($id))) {
            $this->warn("  Menghapus tenant lama '{$id}'...");
            $existing->delete();
        }

        // Buat tenant jika belum ada
        $tenant = Tenant::find($id);

        if (! $tenant) {
            $tenant = Tenant::create([
                'id'        => $id,
                'name'      => $name,
                'plan'      => 'starter',
                'is_active' => true,
            ]);

            $domain = $id . '.' . env('CENTRAL_DOMAIN', 'localhost');
            $tenant->domains()->create(['domain' => $domain]);
            $this->info("  Tenant & database dibuat.");

            tenancy()->initialize($tenant);

            \App\Models\User::create([
                'name'     => 'Admin',
                'email'    => $adminEmail,
                'password' => Hash::make('demo123'),
            ]);

            tenancy()->end();
            $this->info("  Admin: {$adminEmail} / demo123");
        } else {
            $this->warn("  Tenant '{$id}' sudah ada, lanjut seed konten...");
        }

        // Jalankan seeder dalam konteks tenant
        tenancy()->initialize($tenant);

        Setting::setMany($settings);
        $this->info("  Settings disimpan.");

        /** @var \Illuminate\Database\Seeder $seeder */
        $seeder = new $seederClass();
        $seeder->setContainer(app())->setCommand($this);
        $seeder->run();

        tenancy()->end();

        $this->info("  Seeding '{$id}' selesai.");
    }
}
