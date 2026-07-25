<?php

namespace App\Application\Watchlist\Services;

use App\Infrastructure\Persistence\Watchlist\WatchlistParamsetRepository;

class WeeklySwingParamsetDraftImportService
{
    private WeeklySwingParamsetValidator $validator;
    private WeeklySwingParamsetBacktestBindingVerifier $bindingVerifier;
    private WatchlistParamsetRepository $repository;

    public function __construct(
        WeeklySwingParamsetValidator $validator = null,
        WeeklySwingParamsetBacktestBindingVerifier $bindingVerifier = null,
        WatchlistParamsetRepository $repository = null
    ) {
        $this->validator = $validator ?: new WeeklySwingParamsetValidator();
        $this->bindingVerifier = $bindingVerifier ?: new WeeklySwingParamsetBacktestBindingVerifier();
        $this->repository = $repository ?: new WatchlistParamsetRepository();
    }

    public function execute(array $payload, int $btParamId, string $catalogCode, array $source = []): array
    {
        $validation = $this->validator->validate($payload);
        if (! $validation['valid']) {
            return [
                'status' => 'BLOCKED',
                'reason_code' => 'WS_PARAMSET_VALIDATION_FAILED',
                'validation' => $validation,
                'production_ready' => false,
            ];
        }

        $binding = $this->bindingVerifier->verify($validation['canonical_payload'], $btParamId, $catalogCode);
        if (! $binding['valid']) {
            return [
                'status' => 'BLOCKED',
                'reason_code' => $binding['reason_code'],
                'validation' => $validation,
                'binding' => $binding,
                'production_ready' => false,
            ];
        }

        $provenance = [
            'import_mode' => 'DRAFT_ONLY',
            'paramset_code' => (string) $payload['paramset_code'],
            'params_hash' => (string) $validation['canonical_hash'],
            'bt_binding' => $binding,
            'source' => $source,
            'promotion_performed' => false,
        ];
        $persistence = $this->repository->persistDraft($validation['canonical_payload'], $provenance);
        if (($persistence['paramset_status'] ?? null) !== 'DRAFT') {
            return [
                'status' => 'BLOCKED',
                'reason_code' => 'WS_PARAMSET_DRAFT_IMPORT_STATUS_CONFLICT',
                'validation' => $validation,
                'binding' => $binding,
                'persistence' => $persistence,
                'param_set_id' => $persistence['param_set_id'] ?? null,
                'paramset_status' => $persistence['paramset_status'] ?? null,
                'production_ready' => false,
            ];
        }

        return [
            'status' => 'DRAFT_PERSISTED',
            'reason_code' => 'WS_PARAMSET_DRAFT_PERSISTED',
            'validation' => $validation,
            'binding' => $binding,
            'persistence' => $persistence,
            'param_set_id' => $persistence['param_set_id'],
            'paramset_status' => $persistence['paramset_status'],
            'production_ready' => false,
        ];
    }
}
