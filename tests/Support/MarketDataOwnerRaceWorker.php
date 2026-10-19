<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

config()->set('database.default', 'sqlite');
config()->set('database.connections.sqlite', [
    'driver' => 'sqlite', 'database' => $argv[1], 'prefix' => '', 'foreign_key_constraints' => false,
]);
\Illuminate\Support\Facades\DB::purge('sqlite');
\Illuminate\Support\Facades\DB::reconnect('sqlite');
\Illuminate\Database\Eloquent\Model::setConnectionResolver(\Illuminate\Support\Facades\DB::getFacadeRoot());
\Illuminate\Support\Facades\DB::statement('PRAGMA busy_timeout = 10000');
\Carbon\Carbon::setTestNow('2026-03-25 10:30:00');

echo "READY\n";
flush();
try {
    $run = (new \App\Infrastructure\Persistence\MarketData\EodRunRepository())
        ->getOrCreateOwningRun('2026-03-21', $argv[2], 'INGEST_BARS', null, 'full_publish');
    echo json_encode(['outcome' => 'OWNER', 'run_id' => (int) $run->run_id])."\n";
} catch (\Throwable $e) {
    echo json_encode(['outcome' => 'BLOCKED', 'reason' => $e->getMessage()])."\n";
}
