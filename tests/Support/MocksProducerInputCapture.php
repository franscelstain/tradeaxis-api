<?php

namespace Tests\Support;

/** Isolated service tests use this port; persistence proof uses the real repository and database. */
trait MocksProducerInputCapture
{
    private function mockProducerInputCapture()
    {
        $repository = $this->createMock(\App\Infrastructure\Persistence\MarketData\RunInputCaptureRepository::class);
        $repository->method('executeProducer')->willReturnCallback(static function ($run, $stage, $operation, $producer) {
            return $producer();
        });
        $repository->method('owningRun')->willReturnCallback(static function ($runId) {
            return new \App\Models\EodRun(['run_id' => $runId]);
        });
        return $repository;
    }
}
