<?php
/**
 * Authors inputs/frozen_manifest_member_representation.json (candidate-v4).
 *
 * WHY. The V2 publication manifest hash is the hash of a canonical JSON document whose members have a JSON type: `1` and `"1"` hash differently.
 * No authority text fixes the type of `publication_version` inside that semantic payload (`Publication_Manifest_Contract_LOCKED.md` shows an integer in
 * the operational manifest view, and `DB_FIELDS_AND_METADATA.md` stores an INT column; neither defines the semantic payload). The type is a property of the
 * FROZEN GOVERNED PRODUCER, the executable build this candidate is frozen to (owner decision Q5 = B). Candidate-v3 hard-coded the string inside the
 * oracle without saying so (review finding, E-MD-B18-A002-097). Candidate-v4 declares it as a frozen input with its source, and the oracle reads it.
 *
 * AUTHORING TOOL, NOT THE ORACLE. It runs once, after author_build_identity.php. It reads the frozen build manifest (to take the sha256 the frozen
 * build records for the producer file) and the producer SOURCE TEXT (to record the exact rendering line and its line number). It reads no run,
 * publication, replay output or target hash, and it executes no application code. The oracle never runs it.
 *
 * Usage: php author_member_representation.php
 */
$root = dirname(__DIR__, 5);
$inputs = dirname(__DIR__).'/inputs';
$producer = 'app/Infrastructure/Persistence/MarketData/EodPublicationRepository.php';
$method = 'publicationManifestSemanticPayloadV2';

$sha = null;
foreach (explode("\n", (string) file_get_contents($inputs.'/frozen_build_manifest.txt')) as $line) {
    if (substr($line, 66) === $producer) {
        $sha = substr($line, 0, 64);
    }
}
if ($sha === null || hash_file('sha256', $root.'/'.$producer) !== $sha) {
    fwrite(STDERR, "the producer file is not the file the frozen build records\n");
    exit(2);
}
$lines = explode("\n", (string) file_get_contents($root.'/'.$producer));
$start = null;
foreach ($lines as $i => $l) {
    if (strpos($l, 'function '.$method.'(') !== false) {
        $start = $i;
        break;
    }
}
$found = null;
if ($start !== null) {
    for ($i = $start; $i < $start + 80 && $i < count($lines); $i++) {
        if (preg_match("/'publication_version'\s*=>\s*(.+),\s*$/", $lines[$i], $m)) {
            $found = ['line' => $i + 1, 'text' => trim($lines[$i]), 'rendering' => $m[1]];
            break;
        }
    }
}
if ($found === null || strpos($found['rendering'], '(string)') !== 0) {
    fwrite(STDERR, "the producer does not render publication_version as text\n");
    exit(2);
}
$document = [
    'label' => 'FROZEN_MANIFEST_MEMBER_REPRESENTATION',
    'scope' => 'Members of the V2 publication manifest semantic payload whose JSON type is not stated by an authority text.',
    'members' => [
        'publication_version' => [
            'representation' => 'TEXT_BASE10',
            'meaning' => 'the publication version is hashed as its base-10 text ("1"), not as a JSON integer (1)',
            'authority_state' => 'No authority text fixes the type of this member in the semantic payload. Publication_Manifest_Contract_LOCKED.md shows an integer in the operational manifest view; DB_FIELDS_AND_METADATA.md stores an INT column. Neither defines the hashed payload.',
            'basis' => 'FROZEN_GOVERNED_PRODUCER: the representation is a property of the executable build this candidate is frozen to (inputs/frozen_build_identity.json), not an inference from any run or target output.',
            'producer_source' => ['path' => $producer, 'method' => $method, 'line' => $found['line'], 'text' => $found['text'], 'sha256_in_frozen_build_manifest' => $sha],
            'governed_proof_of_the_member' => ['E-MD-B10-A002-016', 'E-MD-B10-A002-017 (MANIFEST.publication_version)', 'E-MD-B10-A003-002 (PublicationSemanticIdentityProductionPathTest::test_each_manifest_member_follows_its_persisted_source)'],
            'effect_if_wrong' => 'The manifest preimage and publication_manifest_hash would differ; the package would fail against the producer it is frozen to.',
        ],
    ],
];
file_put_contents($inputs.'/frozen_manifest_member_representation.json', json_encode($document, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
echo "authored frozen_manifest_member_representation.json (producer ".$producer.":".$found['line']." sha256 ".$sha.")\n";
