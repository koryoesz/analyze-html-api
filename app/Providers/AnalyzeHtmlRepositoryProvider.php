<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\IAnalyzeHtmlRepository;
use App\Services\AnalyzeHtmlService;

class AnalyzeHtmlRepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(IAnalyzeHtmlRepository::class, AnalyzeHtmlService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
