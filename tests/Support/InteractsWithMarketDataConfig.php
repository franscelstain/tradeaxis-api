<?php

use Illuminate\Config\Repository;
use Illuminate\Container\Container;

trait InteractsWithMarketDataConfig
{
    protected function bindMarketDataConfig(array $overrides = []): void
    {
        $baseConfig = [
            'market_data' => require dirname(__DIR__, 2).'/config/market_data.php',
        ];

        $config = array_replace_recursive($baseConfig, $overrides);

        $container = new Container();
        $container->instance('config', new Repository($config));

        Container::setInstance($container);
    }

    protected function clearMarketDataConfig(): void
    {
        Container::setInstance(null);
    }
}
