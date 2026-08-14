<?php

namespace App\Infrastructure\MarketData\Source;

use App\Application\MarketData\Ports\ExchangeMarketStructureEvidenceVerifier;

class IdxExchangeMarketStructureEvidenceVerifier implements ExchangeMarketStructureEvidenceVerifier
{
    const MAX_DOCUMENT_BYTES = 2097152;
    const BOUNDED_SAMPLE_BYTES = 4096;

    public function verify(array $sourceDocument)
    {
        if (! function_exists('curl_init')) {
            throw new \RuntimeException('STAGE_7_AUTHORITY_FETCH_UNAVAILABLE: PHP cURL is required to verify market-structure evidence.');
        }

        $url = (string) ($sourceDocument['document_url'] ?? '');
        $expectedHash = (string) ($sourceDocument['document_sha256'] ?? '');
        $expectedLength = (int) ($sourceDocument['document_byte_length'] ?? 0);
        $expectedType = strtolower((string) ($sourceDocument['content_type'] ?? ''));
        $transportRole = (string) ($sourceDocument['transport_role'] ?? '');
        if ($expectedLength <= 0 || $expectedLength > self::MAX_DOCUMENT_BYTES) {
            throw new \RuntimeException('STAGE_7_DOCUMENT_LENGTH_INVALID: evidence exceeds the bounded verification limit.');
        }

        $body = '';
        $handle = curl_init($url);
        $followMirrorRedirect = $transportRole === 'AUTHORITY_DOCUMENT_MIRROR_REDIRECT';
        $options = [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => $followMirrorRedirect,
            CURLOPT_MAXREDIRS => $followMirrorRedirect ? 3 : 0,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPHEADER => ['Accept: '.$expectedType],
            CURLOPT_USERAGENT => 'TradeAxis-MarketData-MarketStructureEvidence/1.0',
            CURLOPT_WRITEFUNCTION => function ($curl, $chunk) use (&$body, $expectedLength) {
                if (strlen($body) + strlen($chunk) > $expectedLength) {
                    return 0;
                }

                $body .= $chunk;

                return strlen($chunk);
            },
        ];
#if defined('CURLOPT_REDIR_PROTOCOLS')
        if ($followMirrorRedirect && defined('CURLPROTO_HTTPS')) {
            $options[CURLOPT_REDIR_PROTOCOLS] = CURLPROTO_HTTPS;
        }
#endif
        curl_setopt_array($handle, $options);

        $executed = curl_exec($handle);
        $errorNumber = curl_errno($handle);
        $error = curl_error($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $contentType = strtolower(trim((string) curl_getinfo($handle, CURLINFO_CONTENT_TYPE)));
        $effectiveUrl = (string) curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);
        curl_close($handle);

        if ($executed === false) {
            throw new \RuntimeException(
                'STAGE_7_AUTHORITY_FETCH_FAILED: evidence could not be verified (curl='.$errorNumber.'; '.$error.').'
            );
        }
        if ($status !== 200) {
            throw new \RuntimeException('STAGE_7_AUTHORITY_FETCH_FAILED: evidence returned HTTP '.$status.'.');
        }
        if (! $this->contentTypeMatches($expectedType, $contentType)) {
            throw new \RuntimeException('STAGE_7_DOCUMENT_TYPE_MISMATCH: response content type differs from the manifest.');
        }
        if ($followMirrorRedirect && ! $this->isDropboxTransport($url, $effectiveUrl)) {
            throw new \RuntimeException('STAGE_7_MIRROR_REDIRECT_FORBIDDEN: redirected evidence left the pinned Dropbox transport.');
        }
        if (strlen($body) !== $expectedLength) {
            throw new \RuntimeException(
                'STAGE_7_DOCUMENT_LENGTH_MISMATCH: response bytes differ from the manifest (expected='.
                $expectedLength.'; actual='.strlen($body).').'
            );
        }

        $actualHash = hash('sha256', $body);
        if (! hash_equals($expectedHash, $actualHash)) {
            throw new \RuntimeException(
                'STAGE_7_DOCUMENT_HASH_MISMATCH: response hash differs from the manifest (expected='.
                $expectedHash.'; actual='.$actualHash.').'
            );
        }
        $schemaFingerprint = $this->schemaFingerprint($body, $expectedType);
        $sample = substr($body, 0, self::BOUNDED_SAMPLE_BYTES);
        $boundedSample = json_encode([
            'encoding' => 'base64',
            'sample_byte_length' => strlen($sample),
            'sample_sha256' => hash('sha256', $sample),
            'sample_base64' => base64_encode($sample),
        ], JSON_UNESCAPED_SLASHES);
        if ($boundedSample === false) {
            throw new \RuntimeException('STAGE_7_EVIDENCE_SAMPLE_ENCODING_FAILED: bounded response sample could not be encoded.');
        }

        return [
            'document_sha256' => $actualHash,
            'document_byte_length' => strlen($body),
            'content_type' => $contentType,
            'http_status' => $status,
            'schema_fingerprint' => $schemaFingerprint,
            'payload_ref' => 'sha256:'.$actualHash,
            'bounded_payload_body' => $boundedSample,
        ];
    }

    private function schemaFingerprint($body, $expectedType)
    {
        if ($expectedType === 'application/pdf') {
            if (strncmp($body, '%PDF-', 5) !== 0) {
                throw new \RuntimeException('STAGE_7_DOCUMENT_SCHEMA_MISMATCH: PDF evidence lacks a PDF signature.');
            }

            return hash('sha256', 'application/pdf|'.substr($body, 0, 8));
        }

        if ($expectedType === 'application/json') {
            $decoded = json_decode($body, true);
            if (is_array($decoded) && json_last_error() === JSON_ERROR_NONE) {
                $keys = array_keys($decoded);
                sort($keys, SORT_STRING);

                return hash('sha256', 'application/json|'.implode('|', $keys));
            }

            // The pinned IDX press-release response contains a literal control byte inside a text
            // field while still being served as application/json. Exact response hash/length are
            // already enforced above; this fallback validates only its stable outer identity and
            // does not repair, normalize, or reinterpret the authoritative bytes.
            if (substr($body, 0, 1) !== '{'
                || strpos($body, '"Id":1928') === false
                || strpos($body, '"PublishedDate":') === false
                || strpos($body, '"Title":') === false) {
                throw new \RuntimeException(
                    'STAGE_7_DOCUMENT_SCHEMA_MISMATCH: JSON evidence lacks the pinned IDX press-release envelope.'
                );
            }

            return hash('sha256', 'application/json|idx-press-release|Id|PublishedDate|Title');
        }

        if (stripos($body, '<html') === false && stripos($body, '<!doctype html') === false) {
            throw new \RuntimeException('STAGE_7_DOCUMENT_SCHEMA_MISMATCH: HTML evidence lacks an HTML document marker.');
        }

        return hash('sha256', 'text/html|html-document');
    }

    private function contentTypeMatches($expected, $actual)
    {
        if ($expected === 'application/pdf') {
            return strpos($actual, 'application/pdf') === 0
                || strpos($actual, 'application/binary') === 0
                || strpos($actual, 'application/octet-stream') === 0;
        }

        return strpos($actual, $expected) === 0;
    }

    private function isDropboxTransport($originalUrl, $effectiveUrl)
    {
        $originalHost = strtolower((string) parse_url($originalUrl, PHP_URL_HOST));
        $effectiveHost = strtolower((string) parse_url($effectiveUrl, PHP_URL_HOST));

        return $originalHost === 'www.dropbox.com'
            && ($effectiveHost === 'www.dropbox.com'
                || substr($effectiveHost, -26) === '.dl.dropboxusercontent.com');
    }
}
