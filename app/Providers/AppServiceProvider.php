<?php

namespace App\Providers;

// use App\Listeners\SyncDesktopCatalogOnLogin;
use App\Services\HospitalBrandingService;
// use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Event;
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
