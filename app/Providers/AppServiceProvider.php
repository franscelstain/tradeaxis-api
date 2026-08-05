<?php

namespace App\Providers;

use App\Application\MarketData\Ports\ApiEodBarsSource;
use App\Application\MarketData\Ports\ManualEodBarsSource;
use App\Application\MarketData\Ports\SourceObservationRecorder;
use App\Infrastructure\MarketData\Source\LocalFileEodBarsAdapter;
use App\Infrastructure\MarketData\Source\PublicApiEodBarsAdapter;
use App\Infrastructure\Persistence\MarketData\SourceObservationRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(ApiEodBarsSource::class, PublicApiEodBarsAdapter::class);
        $this->app->bind(ManualEodBarsSource::class, LocalFileEodBarsAdapter::class);
        $this->app->bind(SourceObservationRecorder::class, SourceObservationRepository::class);
    }
}
