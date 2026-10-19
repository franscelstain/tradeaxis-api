<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$database = $argv[1];
if (! preg_match('/^tradeaxis_testing_b19_owner_[a-f0-9]{12}$/D', $database)) {
    throw new \RuntimeException('UNSAFE_OWNER_TEST_DATABASE');
}
config()->set('database.connections.b19_owner_worker', array_merge(config('database.connections.mysql'), ['database' => $database]));
config()->set('database.default', 'b19_owner_worker');
\Illuminate\Support\Facades\DB::purge('b19_owner_worker');
\Illuminate\Database\Eloquent\Model::setConnectionResolver(\Illuminate\Support\Facades\DB::getFacadeRoot());
echo "READY\n";
flush();
try {
    $run = (new \App\Infrastructure\Persistence\MarketData\EodRunRepository())
        ->getOrCreateOwningRun('2026-03-21', $argv[2], 'INGEST_BARS', null, $argv[3]);
    echo json_encode(['outcome' => 'OWNER', 'run_id' => (int) $run->run_id])."\n";
} catch (\Throwable $e) {
    echo json_encode(['outcome' => 'BLOCKED', 'reason' => $e->getMessage()])."\n";
}
