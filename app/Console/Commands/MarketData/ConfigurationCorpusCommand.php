<?php

namespace App\Console\Commands\MarketData;

use App\Application\MarketData\Services\MarketDataConfigurationCorpusService;

class ConfigurationCorpusCommand extends AbstractMarketDataCommand
{
    protected $signature = 'market-data:configuration:corpus';
    protected $description = 'Read-only sealed-corpus CONFIG_UNBOUND census, including zero and superseded publications.';

    public function handle()
    {
        try {
            $result = app(MarketDataConfigurationCorpusService::class)->report();
            $this->line(json_encode($result, JSON_UNESCAPED_SLASHES));
            return $result['config_unbound_publication_count'] > 0 ? 2 : 0;
        } catch (\Throwable $error) {
            $this->renderCommandBlocked('COMMAND_EXECUTION_FAILED', $error->getMessage());
            return 1;
        }
    }
}
