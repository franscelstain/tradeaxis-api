<?php

namespace App\Infrastructure\MarketData\Source;

use App\Application\MarketData\Ports\AuthoritativeTradingStatusEvidenceVerifier;

class IdxTradingStatusEvidenceVerifier implements AuthoritativeTradingStatusEvidenceVerifier
{
    const MAX_RESPONSE_BYTES = 2097152;
    const BOUNDED_SAMPLE_BYTES = 4096;

    public function verifySnapshot(array $source, array $expectedEntries)
    {
        $response = $this->fetch($source, 'block.idx.id');
        $asOf = (string) ($source['observed_as_of'] ?? '');
        $expectedLabel = 'Information as of '.date('d F Y', strtotime($asOf));
        if ($asOf === '' || stripos($response['body'], $expectedLabel) === false) {
            throw new \RuntimeException('STAGE8_STATUS_SNAPSHOT_AS_OF_MISMATCH: IDX snapshot does not carry the declared as-of date.');
        }

        $actualEntries = $this->parseLongSuspensionTable($response['body']);
        $expected = [];
        foreach ($expectedEntries as $entry) {
            $expected[(string) $entry['ticker_code']] = (string) $entry['reported_suspension_date'];
        }
        ksort($expected, SORT_STRING);
        ksort($actualEntries, SORT_STRING);
        if ($actualEntries !== $expected) {
            throw new \RuntimeException(
                'STAGE8_STATUS_SNAPSHOT_CONTENT_MISMATCH: IDX snapshot entries differ from the frozen Stage 8 manifest.'
            );
        }

        return $this->evidenceResult($response, hash('sha256', $this->canonicalJson([
            'as_of' => $asOf,
            'entries' => $actualEntries,
        ])), [
            'observed_as_of' => $asOf,
            'entries' => $actualEntries,
        ]);
    }

    public function verifyTransitionSearch(array $source, array $tickerCodes)
    {
        $response = $this->fetch($source, 'www.idx.id');
        $decoded = json_decode($response['body'], true);
        if (! is_array($decoded) || ! isset($decoded['Results']) || ! is_array($decoded['Results'])) {
            throw new \RuntimeException('STAGE8_STATUS_TRANSITION_SCHEMA_MISMATCH: IDX transition response lacks Results.');
        }

        $tickerSet = array_fill_keys(array_values($tickerCodes), true);
        $start = (string) ($source['search_start'] ?? '');
        $end = (string) ($source['search_end'] ?? '');
        $actual = [];
        foreach ($decoded['Results'] as $row) {
            $code = strtoupper(trim((string) ($row['Kode'] ?? '')));
            $date = substr((string) ($row['Date'] ?? ''), 0, 10);
            if (! isset($tickerSet[$code]) || $date < $start || $date > $end) {
                continue;
            }
            $actual[] = [
                'ticker_code' => $code,
                'event_type' => strtoupper(trim((string) ($row['Info_Type'] ?? ''))),
                'event_at' => str_replace('T', ' ', substr((string) ($row['Date'] ?? ''), 0, 19)),
            ];
        }

        $expected = $source['expected_in_scope_events'] ?? [];
        usort($actual, [$this, 'compareEvent']);
        usort($expected, [$this, 'compareEvent']);
        if ($actual !== $expected) {
            throw new \RuntimeException(
                'STAGE8_STATUS_TRANSITION_CONTENT_MISMATCH: authoritative suspension transitions differ within the measured interval.'
            );
        }

        return $this->evidenceResult($response, hash('sha256', $this->canonicalJson([
            'search_start' => $start,
            'search_end' => $end,
            'events' => $actual,
        ])), [
            'search_start' => $start,
            'search_end' => $end,
            'events' => $actual,
        ]);
    }

    private function fetch(array $source, $allowedHost): array
    {
        if (! function_exists('curl_init')) {
            throw new \RuntimeException('STAGE8_STATUS_AUTHORITY_FETCH_UNAVAILABLE: PHP cURL is required.');
        }

        $url = (string) ($source['document_url'] ?? '');
        if (strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https'
            || strtolower((string) parse_url($url, PHP_URL_HOST)) !== $allowedHost) {
            throw new \RuntimeException('STAGE8_STATUS_AUTHORITY_URL_FORBIDDEN: status evidence must remain on its pinned official IDX host.');
        }

        $body = '';
        $handle = curl_init($url);
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPHEADER => ['Accept: '.(string) ($source['content_type'] ?? '*/*')],
            CURLOPT_USERAGENT => 'TradeAxis-MarketData-TradingStatusEvidence/1.0',
            CURLOPT_WRITEFUNCTION => function ($curl, $chunk) use (&$body) {
                if (strlen($body) + strlen($chunk) > self::MAX_RESPONSE_BYTES) {
                    return 0;
                }
                $body .= $chunk;

                return strlen($chunk);
            },
        ]);

        $executed = curl_exec($handle);
        $errorNumber = curl_errno($handle);
        $error = curl_error($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $contentType = strtolower(trim((string) curl_getinfo($handle, CURLINFO_CONTENT_TYPE)));
        $effectiveUrl = (string) curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);
        curl_close($handle);

        if ($executed === false) {
            throw new \RuntimeException('STAGE8_STATUS_AUTHORITY_FETCH_FAILED: curl='.$errorNumber.'; '.$error.'.');
        }
        if ($status !== 200 || $effectiveUrl !== $url) {
            throw new \RuntimeException('STAGE8_STATUS_AUTHORITY_FETCH_FAILED: official response was not an exact HTTP 200 request.');
        }
        $expectedType = strtolower((string) ($source['content_type'] ?? ''));
        if ($expectedType === '' || strpos($contentType, $expectedType) !== 0) {
            throw new \RuntimeException('STAGE8_STATUS_AUTHORITY_CONTENT_TYPE_MISMATCH: official response type changed.');
        }
        if ($body === '') {
            throw new \RuntimeException('STAGE8_STATUS_AUTHORITY_EMPTY_RESPONSE: official response body is empty.');
        }

        return [
            'body' => $body,
            'http_status' => $status,
            'content_type' => $contentType,
            'document_url' => $url,
        ];
    }

    private function parseLongSuspensionTable($html): array
    {
        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $loaded = $document->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (! $loaded) {
            throw new \RuntimeException('STAGE8_STATUS_SNAPSHOT_SCHEMA_MISMATCH: IDX HTML cannot be parsed.');
        }

        foreach ($document->getElementsByTagName('table') as $table) {
            $rows = $table->getElementsByTagName('tr');
            if ($rows->length < 2) {
                continue;
            }
            $header = $this->rowCells($rows->item(0));
            $codeIndex = array_search('Code', $header, true);
            $dateIndex = array_search('Suspension Date', $header, true);
            if ($codeIndex === false || $dateIndex === false) {
                continue;
            }

            $entries = [];
            for ($index = 1; $index < $rows->length; $index++) {
                $cells = $this->rowCells($rows->item($index));
                $code = strtoupper(trim((string) ($cells[$codeIndex] ?? '')));
                $date = $this->normalizeIdxDate((string) ($cells[$dateIndex] ?? ''));
                if (! preg_match('/^[A-Z0-9]{4}$/', $code) || $date === null || isset($entries[$code])) {
                    throw new \RuntimeException('STAGE8_STATUS_SNAPSHOT_SCHEMA_MISMATCH: IDX status row is invalid or duplicated.');
                }
                $entries[$code] = $date;
            }

            if ($entries !== []) {
                return $entries;
            }
        }

        throw new \RuntimeException('STAGE8_STATUS_SNAPSHOT_SCHEMA_MISMATCH: long-suspension table was not found.');
    }

    private function rowCells(\DOMElement $row): array
    {
        $cells = [];
        foreach ($row->childNodes as $node) {
            if ($node instanceof \DOMElement && in_array(strtolower($node->tagName), ['td', 'th'], true)) {
                $cells[] = trim((string) preg_replace('/\s+/u', ' ', $node->textContent));
            }
        }

        return $cells;
    }

    private function normalizeIdxDate($value): ?string
    {
        $date = \DateTimeImmutable::createFromFormat('!d-M-y', trim((string) $value));

        return $date ? $date->format('Y-m-d') : null;
    }

    private function evidenceResult(array $response, $schemaFingerprint, array $semantic): array
    {
        $body = $response['body'];
        $sample = substr($body, 0, self::BOUNDED_SAMPLE_BYTES);
        $bounded = json_encode([
            'encoding' => 'base64',
            'sample_byte_length' => strlen($sample),
            'sample_sha256' => hash('sha256', $sample),
            'sample_base64' => base64_encode($sample),
        ], JSON_UNESCAPED_SLASHES);
        if ($bounded === false) {
            throw new \RuntimeException('STAGE8_STATUS_EVIDENCE_SAMPLE_ENCODING_FAILED.');
        }

        return [
            'document_sha256' => hash('sha256', $body),
            'document_byte_length' => strlen($body),
            'content_type' => $response['content_type'],
            'http_status' => $response['http_status'],
            'schema_fingerprint' => $schemaFingerprint,
            'payload_ref' => 'sha256:'.hash('sha256', $body),
            'bounded_payload_body' => $bounded,
            'semantic' => $semantic,
        ];
    }

    private function compareEvent(array $left, array $right): int
    {
        return strcmp(
            implode('|', [$left['event_at'], $left['ticker_code'], $left['event_type']]),
            implode('|', [$right['event_at'], $right['ticker_code'], $right['event_type']])
        );
    }

    private function canonicalJson($value): string
    {
        if (is_array($value)) {
            $isList = $value === [] || array_keys($value) === range(0, count($value) - 1);
            if (! $isList) {
                ksort($value, SORT_STRING);
            }
            foreach ($value as $key => $child) {
                $value[$key] = json_decode($this->canonicalJson($child), true);
            }
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
