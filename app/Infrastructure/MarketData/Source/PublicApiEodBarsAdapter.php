<?php

namespace App\Infrastructure\MarketData\Source;

use Carbon\Carbon;
use Illuminate\Support\Str;

class PublicApiEodBarsAdapter
{
    private $fetcher;

    public function __construct(callable $fetcher = null)
    {
        $this->fetcher = $fetcher;
    }

    public function fetchOrLoadEodBars($tradeDate, $sourceMode, array $tickerCodes = [])
    {
        if ($sourceMode !== 'api') {
            throw new \RuntimeException('Source mode '.$sourceMode.' tidak didukung oleh PublicApiEodBarsAdapter.');
        }

        $apiConfig = config('market_data.source.api');
        $urlTemplate = isset($apiConfig['endpoint_template']) ? trim((string) $apiConfig['endpoint_template']) : '';
        if ($urlTemplate === '') {
            throw new SourceAcquisitionException('Source API endpoint template belum dikonfigurasi.', 'RUN_SOURCE_AUTH_ERROR');
        }

        $url = str_replace(
            ['{date}', '{symbols}'],
            [$tradeDate, implode(',', $tickerCodes)],
            $urlTemplate
        );

        $response = $this->requestWithRetry($url);
        $rows = $this->parsePayload($response['body'], $tradeDate, $response['captured_at']);

        return array_map(function ($row, $index) use ($tradeDate, $response, $apiConfig) {
            return $this->normalizeRow($row, $tradeDate, $index + 1, $response['captured_at'], $apiConfig);
        }, $rows, array_keys($rows));
    }

    private function requestWithRetry($url)
    {
        $retryMax = max(0, (int) config('market_data.provider.api_retry_max'));
        $baseBackoffMs = max(0, (int) config('market_data.provider.api_backoff_ms'));
        $capturedAt = Carbon::now(config('market_data.platform.timezone'))->toDateTimeString();
        $lastException = null;

        for ($attempt = 0; $attempt <= $retryMax; $attempt++) {
            $this->applyThrottleAndJitter($attempt);

            try {
                $response = $this->performHttpRequest($url);
                $status = (int) $response['status'];

                if (in_array($status, [401, 403], true)) {
                    throw new SourceAcquisitionException('Source API authentication/config failed with HTTP '.$status.'.', 'RUN_SOURCE_AUTH_ERROR');
                }

                if ($status === 429) {
                    throw new SourceAcquisitionException('Source API rate limited the request.', 'RUN_SOURCE_RATE_LIMIT');
                }

                if ($status === 408 || $status >= 500 || $status === 0) {
                    throw new SourceAcquisitionException('Source API request timed out or returned transient server error.', 'RUN_SOURCE_TIMEOUT');
                }

                if ($status < 200 || $status >= 300) {
                    throw new SourceAcquisitionException('Source API returned unexpected HTTP status '.$status.'.', 'RUN_SOURCE_MALFORMED_PAYLOAD');
                }

                return [
                    'body' => $response['body'],
                    'captured_at' => $capturedAt,
                ];
            } catch (SourceAcquisitionException $e) {
                $lastException = $e;
                if (! $this->shouldRetry($e->reasonCode(), $attempt, $retryMax)) {
                    throw $e;
                }

                $this->backoff($attempt, $baseBackoffMs);
            }
        }

        throw $lastException ?: new SourceAcquisitionException('Unknown source API acquisition failure.', 'RUN_SOURCE_TIMEOUT');
    }

    private function shouldRetry($reasonCode, $attempt, $retryMax)
    {
        if ($attempt >= $retryMax) {
            return false;
        }

        return in_array($reasonCode, ['RUN_SOURCE_TIMEOUT', 'RUN_SOURCE_RATE_LIMIT'], true);
    }

    private function backoff($attempt, $baseBackoffMs)
    {
        $multiplier = (int) pow(2, $attempt);
        $jitterMs = random_int(50, 150);
        usleep(($baseBackoffMs * $multiplier + $jitterMs) * 1000);
    }

    private function applyThrottleAndJitter($attempt)
    {
        $qps = max(1, (int) config('market_data.provider.api_throttle_qps'));
        $throttleUs = (int) floor(1000000 / $qps);
        $jitterUs = random_int(25000, 125000);

        if ($attempt > 0 || $throttleUs > 0) {
            usleep($throttleUs + $jitterUs);
        }
    }

    private function performHttpRequest($url)
    {
        if ($this->fetcher) {
            return call_user_func($this->fetcher, $url, $this->buildHeaders(), $this->timeoutSeconds());
        }

        $headers = implode("\r\n", $this->buildHeaders());
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => $headers,
                'timeout' => $this->timeoutSeconds(),
                'ignore_errors' => true,
            ],
        ]);

        $warning = null;
        set_error_handler(function ($severity, $message) use (&$warning) {
            $warning = $message;
            return true;
        });

        try {
            $body = file_get_contents($url, false, $context);
        } finally {
            restore_error_handler();
        }

        if ($body === false) {
            throw new SourceAcquisitionException($warning ?: 'Source API request failed.', 'RUN_SOURCE_TIMEOUT');
        }

        return [
            'status' => $this->extractHttpStatus(isset($http_response_header) ? $http_response_header : []),
            'body' => $body,
            'headers' => isset($http_response_header) ? $http_response_header : [],
        ];
    }

    private function buildHeaders()
    {
        $headers = ['Accept: application/json'];
        $headerName = trim((string) config('market_data.source.api.auth_header_name'));
        $token = trim((string) config('market_data.source.api.auth_token'));

        if ($headerName !== '' && $token !== '') {
            $headers[] = $headerName.': '.$token;
        }

        return $headers;
    }

    private function timeoutSeconds()
    {
        return max(1, (int) config('market_data.source.api.timeout_seconds'));
    }

    private function extractHttpStatus(array $headers)
    {
        foreach ($headers as $header) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})\b/', $header, $matches)) {
                return (int) $matches[1];
            }
        }

        return 0;
    }

    private function parsePayload($body, $tradeDate, $capturedAt)
    {
        $format = strtolower((string) config('market_data.source.api.response_format', 'json'));

        if ($format === 'csv') {
            return $this->parseCsv($body);
        }

        $decoded = json_decode($body, true);
        if (! is_array($decoded)) {
            throw new SourceAcquisitionException('Source API payload is not valid JSON.', 'RUN_SOURCE_MALFORMED_PAYLOAD');
        }

        $rows = $this->extractRowsByPath($decoded, (string) config('market_data.source.api.response_rows_path', ''));
        if (! is_array($rows)) {
            throw new SourceAcquisitionException('Source API response rows path is missing or not iterable.', 'RUN_SOURCE_RESPONSE_CHANGED');
        }

        return array_values($rows);
    }

    private function parseCsv($body)
    {
        $lines = preg_split('/\r\n|\n|\r/', trim((string) $body));
        if (count($lines) < 2) {
            throw new SourceAcquisitionException('Source API CSV payload has no data rows.', 'RUN_SOURCE_RESPONSE_CHANGED');
        }

        $header = str_getcsv(array_shift($lines));
        $normalizedHeader = array_map(function ($item) {
            return Str::snake(trim($item));
        }, $header);

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $values = str_getcsv($line);
            $rows[] = array_combine($normalizedHeader, $values);
        }

        return $rows;
    }

    private function extractRowsByPath(array $decoded, $path)
    {
        if ($path === '' || $path === '.') {
            return $decoded;
        }

        $current = $decoded;
        foreach (explode('.', $path) as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        return $current;
    }

    private function normalizeRow(array $row, $tradeDate, $index, $capturedAt, array $apiConfig)
    {
        $fieldMap = isset($apiConfig['field_map']) && is_array($apiConfig['field_map']) ? $apiConfig['field_map'] : [];
        $sourceCapturedAt = $this->extractField($row, isset($fieldMap['captured_at']) ? $fieldMap['captured_at'] : null);

        return [
            'ticker_code' => $this->normalizeTickerCode($this->extractField($row, isset($fieldMap['ticker_code']) ? $fieldMap['ticker_code'] : 'ticker_code')),
            'trade_date' => $this->extractField($row, isset($fieldMap['trade_date']) ? $fieldMap['trade_date'] : 'trade_date') ?: $tradeDate,
            'open' => $this->extractField($row, isset($fieldMap['open']) ? $fieldMap['open'] : 'open'),
            'high' => $this->extractField($row, isset($fieldMap['high']) ? $fieldMap['high'] : 'high'),
            'low' => $this->extractField($row, isset($fieldMap['low']) ? $fieldMap['low'] : 'low'),
            'close' => $this->extractField($row, isset($fieldMap['close']) ? $fieldMap['close'] : 'close'),
            'volume' => $this->extractField($row, isset($fieldMap['volume']) ? $fieldMap['volume'] : 'volume'),
            'adj_close' => $this->extractField($row, isset($fieldMap['adj_close']) ? $fieldMap['adj_close'] : 'adj_close'),
            'source_name' => isset($apiConfig['source_name']) ? $apiConfig['source_name'] : 'API_FREE',
            'source_row_ref' => $this->extractField($row, isset($fieldMap['source_row_ref']) ? $fieldMap['source_row_ref'] : 'source_row_ref') ?: 'api:'.$index,
            'captured_at' => $sourceCapturedAt ? Carbon::parse($sourceCapturedAt)->setTimezone(config('market_data.platform.timezone'))->toDateTimeString() : $capturedAt,
        ];
    }

    private function extractField(array $row, $field)
    {
        if ($field === null || $field === '') {
            return null;
        }

        if (array_key_exists($field, $row)) {
            return $row[$field];
        }

        $current = $row;
        foreach (explode('.', $field) as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        return $current;
    }

    private function normalizeTickerCode($value)
    {
        if ($value === null) {
            return null;
        }

        return Str::upper(trim((string) $value));
    }
}
