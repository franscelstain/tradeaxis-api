<?php

namespace App\Infrastructure\MarketData\Source;

use App\Application\MarketData\Ports\AuthoritativeDocumentEvidenceVerifier;

class KseiAuthoritativeDocumentEvidenceVerifier implements AuthoritativeDocumentEvidenceVerifier
{
    const MAX_DOCUMENT_BYTES = 5242880;

    public function verify(array $sourceDocument)
    {
        if (! function_exists('curl_init')) {
            throw new \RuntimeException('STAGE_6_AUTHORITY_FETCH_UNAVAILABLE: PHP cURL is required to verify authoritative document bytes.');
        }

        $url = (string) ($sourceDocument['document_url'] ?? '');
        $expectedHash = (string) ($sourceDocument['document_sha256'] ?? '');
        $expectedLength = (int) ($sourceDocument['document_byte_length'] ?? 0);
        if ($expectedLength <= 0 || $expectedLength > self::MAX_DOCUMENT_BYTES) {
            throw new \RuntimeException('STAGE_6_DOCUMENT_LENGTH_INVALID: authoritative document exceeds the bounded verification limit.');
        }

        $body = '';
        $handle = curl_init($url);
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => ['Accept: application/pdf'],
            CURLOPT_USERAGENT => 'TradeAxis-MarketData-AuthorityEvidence/1.0',
            CURLOPT_WRITEFUNCTION => function ($curl, $chunk) use (&$body, $expectedLength) {
                if (strlen($body) + strlen($chunk) > $expectedLength) {
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
        curl_close($handle);

        if ($executed === false) {
            throw new \RuntimeException(
                'STAGE_6_AUTHORITY_FETCH_FAILED: KSEI document could not be verified (curl='.$errorNumber.'; '.$error.').'
            );
        }
        if ($status !== 200) {
            throw new \RuntimeException('STAGE_6_AUTHORITY_FETCH_FAILED: KSEI document returned HTTP '.$status.'.');
        }
        if (strpos($contentType, 'application/pdf') !== 0) {
            throw new \RuntimeException('STAGE_6_DOCUMENT_TYPE_MISMATCH: KSEI response is not application/pdf.');
        }
        if (strlen($body) !== $expectedLength) {
            throw new \RuntimeException('STAGE_6_DOCUMENT_LENGTH_MISMATCH: KSEI response bytes differ from the manifest.');
        }

        $actualHash = hash('sha256', $body);
        if (! hash_equals($expectedHash, $actualHash)) {
            throw new \RuntimeException('STAGE_6_DOCUMENT_HASH_MISMATCH: KSEI response hash differs from the manifest.');
        }

        return [
            'document_sha256' => $actualHash,
            'document_byte_length' => strlen($body),
            'content_type' => 'application/pdf',
            'http_status' => $status,
        ];
    }
}
