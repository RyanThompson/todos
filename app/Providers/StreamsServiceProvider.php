<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Streams\Api\Support\Facades\API;

class StreamsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (config('streams.api.enabled')) {
            API::routeEntries();
        }
        if (class_exists(\Streams\Ui\Support\Facades\UI::class)) {
        $pages = [];
        foreach (glob(app_path('Domains/*/Panel/Pages/*.php')) ?: [] as $file) {
            $class = 'App\\Domains\\'.basename(dirname($file, 3)).'\\Panel\\Pages\\'.basename($file, '.php');
            if (class_exists($class)) {
                $pages[] = $class;
            }
        }
            \Streams\Ui\Support\Facades\UI::panel(
                \Streams\Ui\Builders\Panels\Panel::make('admin')->default()->path('admin')->middleware(['web', \App\Http\Middleware\EnsureEnvAdmin::class])->pages($pages)
            );
        }
        // Panel login is the env-admin middleware on /admin.
    }
}
