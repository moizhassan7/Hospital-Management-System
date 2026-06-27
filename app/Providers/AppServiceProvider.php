<?php

namespace App\Providers;

use App\Services\HospitalBrandingService;
use Illuminate\Http\Request;
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
        $this->configureLanUrls();

        try {
            $branding = $this->app->make(HospitalBrandingService::class);
            $branding->seedDefaultsIfEmpty();
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
