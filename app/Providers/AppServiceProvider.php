<?php

namespace App\Providers;

// use App\Listeners\SyncDesktopCatalogOnLogin;
use App\Models\CollectionCenter;
use App\Models\LimsBooking;
use App\Models\LimsCommissionRule;
use App\Models\LimsCommissionSnapshot;
use App\Models\LimsDoctor;
use App\Models\LimsPatient;
use App\Models\LimsSampleBatch;
use App\Policies\CollectionCenterPolicy;
use App\Policies\LimsBookingPolicy;
use App\Policies\LimsCommissionRulePolicy;
use App\Policies\LimsCommissionSnapshotPolicy;
use App\Policies\LimsDoctorPolicy;
use App\Policies\LimsPatientPolicy;
use App\Policies\LimsSampleBatchPolicy;
use App\Services\HospitalBrandingService;
// use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Catalog sync on login disabled — tests/particulars are in web DB; run artisan manually if needed.
        // Event::listen(Login::class, SyncDesktopCatalogOnLogin::class);

        Gate::policy(CollectionCenter::class, CollectionCenterPolicy::class);
        Gate::policy(LimsPatient::class, LimsPatientPolicy::class);
        Gate::policy(LimsSampleBatch::class, LimsSampleBatchPolicy::class);
        Gate::policy(LimsCommissionRule::class, LimsCommissionRulePolicy::class);
        Gate::policy(LimsCommissionSnapshot::class, LimsCommissionSnapshotPolicy::class);
        Gate::policy(LimsDoctor::class, LimsDoctorPolicy::class);
        Gate::policy(LimsBooking::class, LimsBookingPolicy::class);

        $this->configureLanUrls();

        try {
            $branding = $this->app->make(HospitalBrandingService::class);

            // Only touch the DB for seeding when the branding cache is cold
            // (fresh DB / after cache clear) or when running console commands
            // such as migrations. On normal warm web requests we rely purely on
            // the cached settings and skip the existence check + possible inserts.
            if ($this->app->runningInConsole() || ! $branding->isCacheWarm()) {
                $branding->seedDefaultsIfEmpty();
            }

            $branding->applyToConfig();
        } catch (\Throwable) {
            // Migrations may not have run yet.
        }
    }

    private function configureLanUrls(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $lanHost = config('hospital.lan_host');
        $lanPort = config('hospital.lan_port', 8000);

        if (! $lanHost) {
            return;
        }

        $request = $this->app->make(Request::class);

        if ($request->getHost() === $lanHost) {
            URL::forceRootUrl("http://{$lanHost}:{$lanPort}");
        }
    }
}
