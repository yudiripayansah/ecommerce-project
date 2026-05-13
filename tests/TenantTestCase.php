<?php

namespace Tests;

use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Testing\TestResponse as TestingTestResponse;
use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;

abstract class TenantTestCase extends TestCase
{
    protected Tenant $tenant;

    protected string $tenantDomain = 'teststore.localhost';

    protected function setUp(): void
    {
        parent::setUp();

        // Disable DB switching: all tables live on the same SQLite connection.
        // This lets us use :memory: without per-tenant database files while still
        // exercising full tenant context (tenant() helper, bootstrappers, etc).
        config(['tenancy.bootstrappers' => array_values(array_filter(
            config('tenancy.bootstrappers'),
            fn (string $b) => $b !== DatabaseTenancyBootstrapper::class,
        ))]);

        // Tenant migrations run on the same connection after RefreshDatabase
        // has already run the central migrations.
        $this->artisan('migrate', [
            '--path'     => 'database/migrations/tenant',
            '--realpath' => false,
        ]);

        $this->tenant = Tenant::create(['name' => 'Test Store', 'is_active' => true]);
        $this->tenant->domains()->create(['domain' => $this->tenantDomain]);

        tenancy()->initialize($this->tenant);

        // Symfony's Request::create() extracts HTTP_HOST from the URI and ignores
        // withServerVariables(). We wire APP_URL to the tenant domain so url('/path')
        // returns a fully-qualified URL with the right host, which the tenancy
        // middleware then correctly resolves to our test tenant.
        config(['app.url' => 'http://' . $this->tenantDomain]);
        $this->app->make(\Illuminate\Routing\UrlGenerator::class)
            ->forceRootUrl('http://' . $this->tenantDomain);

        // Disable rate limiting so tests don't interfere with each other.
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    // ── HTTP helpers ──────────────────────────────────────────────────────────

    protected function tenantGet(string $uri, array $headers = []): TestingTestResponse
    {
        return $this->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->get($uri, $headers);
    }

    protected function tenantPost(string $uri, array $data = [], array $headers = []): TestingTestResponse
    {
        return $this->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->post($uri, $data, $headers);
    }

    protected function tenantPut(string $uri, array $data = [], array $headers = []): TestingTestResponse
    {
        return $this->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->put($uri, $data, $headers);
    }

    protected function tenantDelete(string $uri, array $headers = []): TestingTestResponse
    {
        return $this->withServerVariables(['HTTP_HOST' => $this->tenantDomain])
            ->delete($uri, $headers);
    }

    protected function actingAsCustomer(Customer $customer): static
    {
        return $this->actingAs($customer, 'customer');
    }

    // ── Cart helpers ──────────────────────────────────────────────────────────

    protected function makeCartItem(
        int $productId,
        int $variantId = 0,
        float $price = 50000,
        int $quantity = 1,
        string $title = 'Test Product',
    ): array {
        return [
            'product_id'    => $productId,
            'variant_id'    => $variantId ?: null,
            'handle'        => 'test-product',
            'title'         => $title,
            'variant_title' => null,
            'price'         => $price,
            'quantity'      => $quantity,
            'image'         => null,
        ];
    }

    protected function makeCart(array $items): array
    {
        $cart = [];
        foreach ($items as $item) {
            $key        = $item['product_id'] . '-' . ($item['variant_id'] ?? 0);
            $cart[$key] = $item;
        }
        return $cart;
    }

    // ── Checkout POST helpers ─────────────────────────────────────────────────

    protected function checkoutPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name'        => 'John Doe',
            'customer_email'       => 'john@example.com',
            'customer_phone'       => '081234567890',
            'shipping_address'     => 'Jl. Contoh No. 1',
            'shipping_city'        => 'Jakarta',
            'shipping_province'    => 'DKI Jakarta',
            'shipping_postal_code' => '10110',
            'payment_method'       => 'cod',
            'shipping_cost'        => 0,
        ], $overrides);
    }
}
