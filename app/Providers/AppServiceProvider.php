<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\MonthlyBill;
use App\Models\Payment;
use App\Observers\AnnouncementObserver;
use App\Observers\BillObserver;
use App\Observers\PaymentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        MonthlyBill::observe(BillObserver::class);
        Payment::observe(PaymentObserver::class);
        Announcement::observe(AnnouncementObserver::class);
    }
}
