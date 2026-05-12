<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create
        {id : Subdomain/ID toko (huruf kecil, angka, strip)}
        {name : Nama toko}
        {--plan=free : Paket (free/starter/pro)}
        {--domain= : Custom domain (default: id.CENTRAL_DOMAIN)}
        {--admin-email= : Email admin toko}
        {--admin-password= : Password admin toko}';

    protected $description = 'Buat tenant baru beserta database dan migrasinya';

    public function handle(): int
    {
        $id   = $this->argument('id');
        $name = $this->argument('name');
        $plan = $this->option('plan');

        if (! preg_match('/^[a-z0-9\-]+$/', $id)) {
            $this->error('ID toko hanya boleh huruf kecil, angka, dan strip.');
            return self::FAILURE;
        }

        if (Tenant::find($id)) {
            $this->error("Tenant '{$id}' sudah ada.");
            return self::FAILURE;
        }

        $this->info("Membuat tenant '{$id}' ({$name})...");

        $tenant = Tenant::create([
            'id'        => $id,
            'name'      => $name,
            'plan'      => $plan,
            'is_active' => true,
        ]);

        $domain = $this->option('domain') ?? $id . '.' . env('CENTRAL_DOMAIN', 'localhost');
        $tenant->domains()->create(['domain' => $domain]);

        $this->info("  Database dibuat: tenant{$id}");
        $this->info("  Domain: {$domain}");

        // Optionally create admin user in tenant context
        $email    = $this->option('admin-email');
        $password = $this->option('admin-password');

        if ($email && $password) {
            tenancy()->initialize($tenant);

            \App\Models\User::create([
                'name'     => 'Admin',
                'email'    => $email,
                'password' => Hash::make($password),
            ]);

            tenancy()->end();
            $this->info("  Admin dibuat: {$email}");
        }

        $this->newLine();
        $this->info("Tenant '{$id}' berhasil dibuat!");
        $this->line("  URL: http://{$domain}");
        $this->line("  Admin panel: http://{$domain}/admin");

        return self::SUCCESS;
    }
}
