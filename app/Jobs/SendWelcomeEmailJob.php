<?php

namespace App\Jobs;

use App\Jobs\Concerns\RunsInTenantContext;
use App\Mail\Admin\NewCustomerMail;
use App\Mail\Customer\WelcomeMail;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RunsInTenantContext;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int    $customerId,
        public readonly string $tenantId,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $this->runForTenant(function (): void {
            $customer = Customer::find($this->customerId);

            if (! $customer) {
                return;
            }

            Mail::to($customer->email)->send(new WelcomeMail($customer));

            $adminEmail = Setting::get('contact_email', config('mail.from.address'));
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new NewCustomerMail($customer));
            }
        });
    }
}
