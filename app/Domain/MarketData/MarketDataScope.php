<?php

namespace App\Domain\MarketData;

use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

final class MarketDataScope
{
    public const MARKET_CODE = 'IDX';
    public const MARKET_SEGMENT = 'REGULAR';
    public const FREQUENCY = 'EOD';
    public const DATASET_START = '2023-01-02';
    public const RAW_PRODUCT = 'RAW';
    public const STRUCTURAL_ADJUSTED_PRODUCT = 'STRUCTURAL_ADJUSTED';
    public const TOTAL_RETURN_PRODUCT = 'TOTAL_RETURN';

    private $timezone;
    private $datasetStart;
    private $operationalStartDate;

    public function __construct($timezone, $datasetStart, $operationalStartDate = null)
    {
        $this->timezone = $this->validTimezone($timezone);
        $this->datasetStart = $this->validDate($datasetStart, 'MARKET_DATA_DATASET_START_INVALID');
        $this->operationalStartDate = $operationalStartDate === null || trim((string) $operationalStartDate) === ''
            ? null
            : $this->validDate($operationalStartDate, 'MARKET_DATA_OPERATIONAL_START_DATE_INVALID');

        if ($this->operationalStartDate !== null && $this->operationalStartDate < $this->datasetStart) {
            throw new RuntimeException('MARKET_DATA_OPERATIONAL_START_DATE_INVALID: operational start cannot precede intentional dataset start.');
        }
    }

    public static function fromConfig()
    {
        $marketCode = strtoupper(trim((string) config('market_data.scope.market_code', self::MARKET_CODE)));
        $marketSegment = strtoupper(trim((string) config('market_data.scope.market_segment', self::MARKET_SEGMENT)));
        $frequency = strtoupper(trim((string) config('market_data.scope.frequency', self::FREQUENCY)));
        $scopeTimezone = trim((string) config('market_data.scope.timezone', 'Asia/Jakarta'));
        $platformTimezone = trim((string) config('market_data.platform.timezone', 'Asia/Jakarta'));

        if ($marketCode !== self::MARKET_CODE || $marketSegment !== self::MARKET_SEGMENT || $frequency !== self::FREQUENCY) {
            throw new RuntimeException(
                'MARKET_DATA_SCOPE_INVALID: canonical scope must remain IDX Regular-Market EOD.'
            );
        }

        if ($scopeTimezone !== 'Asia/Jakarta' || $platformTimezone !== $scopeTimezone) {
            throw new RuntimeException(
                'MARKET_DATA_TIMEZONE_INVALID: IDX EOD scope and platform timezone must both be Asia/Jakarta.'
            );
        }

        return new self(
            $platformTimezone,
            config('market_data.scope.dataset_start', self::DATASET_START),
            config('market_data.scope.operational_start_date')
        );
    }

    public function assertRequestedDate($date)
    {
        $date = $this->validDate($date, 'MARKET_DATA_REQUESTED_DATE_INVALID');

        if ($date < $this->datasetStart) {
            throw new RuntimeException(
                'MARKET_DATA_REQUEST_BEFORE_DATASET_START: requested date '.$date.' precedes intentional dataset start '.$this->datasetStart.'.'
            );
        }

        return $date;
    }

    public function assertRequestedRange($startDate, $endDate)
    {
        $startDate = $this->assertRequestedDate($startDate);
        $endDate = $this->assertRequestedDate($endDate);

        if ($startDate > $endDate) {
            throw new RuntimeException('MARKET_DATA_REQUESTED_RANGE_INVALID: start date is after end date.');
        }

        return [$startDate, $endDate];
    }

    public function datasetStart()
    {
        return $this->datasetStart;
    }

    public function operationalStartDate()
    {
        return $this->operationalStartDate;
    }

    public function isOperationallyActivatedFor($date)
    {
        return $this->operationalStartDate !== null
            && $this->assertRequestedDate($date) >= $this->operationalStartDate;
    }

    public function stateFor($date)
    {
        return $this->isOperationallyActivatedFor($date) ? 'OPERATIONAL' : 'DEVELOPMENT';
    }

    public function timezone()
    {
        return $this->timezone;
    }

    private function validTimezone($timezone)
    {
        $timezone = trim((string) $timezone);

        try {
            new DateTimeZone($timezone);
        } catch (\Throwable $e) {
            throw new RuntimeException('MARKET_DATA_TIMEZONE_INVALID: '.$timezone, 0, $e);
        }

        return $timezone;
    }

    private function validDate($date, $reasonCode)
    {
        $date = trim((string) $date);
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date, new DateTimeZone($this->timezone));
        $errors = DateTimeImmutable::getLastErrors();

        if (! $parsed || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $parsed->format('Y-m-d') !== $date) {
            throw new RuntimeException($reasonCode.': expected YYYY-MM-DD, got '.$date.'.');
        }

        return $date;
    }
}
