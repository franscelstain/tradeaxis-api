<?php
/**
 * Freezes the executable build identity of candidate-v5 (the FINAL post-A003 build: MD-B04-A003 snapshot reason-registry, as-known binding and F-MD-B18-A002-018 changed the build since candidate-v4).
 *
 *   inputs/frozen_build_identity.json    the build identity as a literal, with the method that produced it
 *   inputs/frozen_build_manifest.txt     one line per file of the build: "<sha256>  <path>", ordered by path
 *
 * AUTHORING TOOL, NOT THE ORACLE. The governed build-identity mechanism is `php_source_build_v1` (ProducerRegistrySnapshot::build): the build of
 * a run is the SHA-256 of the canonical JSON `{"files_base64":{<path>:<base64 of the file>,...},"schema_version":"php_source_build_v1"}` over every
 * `*.php` file of app, config and bootstrap (bootstrap/cache excluded), every file of vendor, and composer.json and composer.lock, ordered by path.
 * This script re-implements that mechanism independently of the application (it includes no application code), scans the tree ONCE at freeze time
 * and writes the result as a frozen input. The reference oracle never runs it and never inspects the tree: it reads only the frozen file.
 *
 * The candidate is valid for exactly this build. Any change to a file of the build changes the identity and requires a new candidate version.
 *
 * Usage: php -d memory_limit=1G author_build_identity.php
 */
$root = dirname(__DIR__, 5);
$inputs = dirname(__DIR__).'/inputs';
$files = [];
foreach (['app', 'config', 'bootstrap', 'vendor'] as $dir) {
    $count = 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (! $file->isFile() || ($dir !== 'vendor' && $file->getExtension() !== 'php')) {
            continue;
        }
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        if (strpos($relative, 'bootstrap/cache/') === 0) {
            continue;
        }
        $files[$relative] = file_get_contents($file->getPathname());
        $count++;
    }
    if ($count === 0) {
        fwrite(STDERR, "empty build root {$dir}\n");
        exit(2);
    }
}
foreach (['composer.json', 'composer.lock'] as $file) {
    $files[$file] = file_get_contents($root.'/'.$file);
}
ksort($files, SORT_STRING);
$manifest = '';
$encoded = [];
foreach ($files as $path => $bytes) {
    $manifest .= hash('sha256', $bytes).'  '.$path."\n";
    $encoded[$path] = base64_encode($bytes);
}
unset($files);
$content = json_encode(['files_base64' => $encoded, 'schema_version' => 'php_source_build_v1'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
$contentHash = hash('sha256', $content);
$archivePath = $root.'/storage/app/market-data/input-builds/'.$contentHash.'.json.gz';
$archive = null;
if (is_file($archivePath)) {
    $compressed = file_get_contents($archivePath);
    $archive = ['retained_archive_path' => 'storage/app/market-data/input-builds/'.$contentHash.'.json.gz', 'archive_sha256' => hash('sha256', $compressed), 'archive_bytes' => strlen($compressed),
        'archive_decompresses_to_this_content' => gzdecode($compressed) === $content];
}
$document = [
    'label' => 'FROZEN_EXECUTABLE_BUILD_IDENTITY',
    'method' => 'php_source_build_v1',
    'method_source' => 'ProducerRegistrySnapshot::build, re-implemented independently by derivation/author_build_identity.php',
    'build_id' => 'sha256:'.$contentHash,
    'content_hash' => $contentHash,
    'file_count' => count($encoded),
    'manifest_file' => 'inputs/frozen_build_manifest.txt',
    'manifest_sha256' => hash('sha256', $manifest),
    'archive' => $archive,
    'frozen_at' => (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->format(DATE_ATOM),
    'validity' => 'This candidate is valid only for this build. A change to any file of the build changes the identity and requires a new candidate version, review and approval.',
    'how_to_verify' => [
        '1. every line of inputs/frozen_build_manifest.txt equals the sha256 of that file of the tree the candidate was frozen from',
        '2. sha256 of canonical JSON {"files_base64":{path: base64(file)},"schema_version":"php_source_build_v1"} over those files equals content_hash',
        '3. when the archive is present: it decompresses to that same content',
    ],
];
file_put_contents($inputs.'/frozen_build_manifest.txt', $manifest);
file_put_contents($inputs.'/frozen_build_identity.json', json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
echo $document['build_id'], "\nfiles: ", $document['file_count'], "\n", $archive === null ? "archive: not retained yet\n" : 'archive matches content: '.($archive['archive_decompresses_to_this_content'] ? 'YES' : 'NO')."\n";
