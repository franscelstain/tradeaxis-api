<?php
require_once __DIR__.'/B19ArchivedExecutedEvidenceProofTest.php';

/** Per-obligation checks over retained actual primary inputs, independent fixtures and current actual exports. */
class B19ExecutedEvidencePredicateProofTest extends \PHPUnit\Framework\TestCase
{
    private static $world;
    private static function root(): string {return dirname(__DIR__,3);}
    private static function read(string $p): array {return json_decode(file_get_contents($p),true,512,JSON_THROW_ON_ERROR);}
    private static function world(): array {
        if(self::$world!==null)return self::$world;
        $root=self::root();$raw=$root.'/storage/app/market-data/evidence/MD-B19-A001/';
        $m=new \ReflectionMethod(B19ArchivedExecutedEvidenceProofTest::class,'bundle');$m->setAccessible(true);$b=$m->invoke(null);
        // E024 correction/test sources must be verified against the issued inventory as well as values.
        $record=self::read(glob($root.'/docs/market_data/records/evidence/E-MD-B19-A001-024_*.json')[0]);
        $a=$record['raw_artifacts'];$base=$root.'/'.$a['dir'].'/';
        if(hash_file('sha256',$base.$a['inventory'])!==$a['inventory_sha256'])throw new \RuntimeException('E024 inventory mismatch');
        foreach(['E024_changed_correction_evidence.json','E024_changed_sources_before_export.json','E024_green_real_correction_complete.execution.json','E024_green_real_correction_complete.txt'] as $f)
            if(hash_file('sha256',$base.$f)!==$a['files'][$f]['sha256'])throw new \RuntimeException('E024 source mismatch '.$f);
        foreach(['normal','historical','failed','missing_trail'] as $f)$b[$f]=self::read($raw.'E025_current_'.$f.'_evidence_pack.json');
        $b['mismatch']=self::read($raw.'E025_current_mismatch_replay_evidence_pack.json');$b['match']=self::read($raw.'E025_current_match_replay_evidence_pack.json');
        $b['primary']=self::read($raw.'E025_current_authoritative_inputs.json');
        $b['bindings']=self::read($raw.'E025_current_normal_artifact_manifest.json')['binding_contexts']['actual']['bindings'];
        $b['oracle']=self::read($root.'/tests/fixtures/replay/r0025-synthetic-v2-candidate-v5/expected/expected_publication_manifest.json');
        $b['exporter_hash']=hash_file('sha256',$root.'/app/Application/MarketData/Services/MarketDataEvidenceExportService.php');
        $b['current_execution']=self::read($raw.'E025_green_real_current.execution.json');
        if(hash_file('sha256',$raw.'E025_green_real_current.txt')!==$b['current_execution']['output_sha256']||$b['current_execution']['exit_code']!==0)throw new \RuntimeException('actual current execution missing');
        return self::$world=$b;
    }
    public static function predicates(): array {
        $out=[];
        foreach(['MD-S076'=>[6,7,8,9,10,12,13,14,15,16,17,18,19,21,22,23,24,25,26,28,29,30,31,32,34,35,36,38,40,41,42,53],
                 'MD-S068'=>[2,5,6,8,9,10,11,12,14,15,16,23,24,25,30,31,32,33,34,35,36],
                 'MD-S063'=>[2,3,4,5,7,8,9,10,11,12,14,15,16,151,152,153,154,155,168]] as $source=>$ids)
            foreach($ids as $n){$id=$source.'-R'.str_pad($n,4,'0',STR_PAD_LEFT);$out[$id]=[$id];}
        return $out;
    }
    /** Independent expected, actual observation, provenance. No expected value is read from its output. */
    private function facts(string $id,array $b): array {
        $n=(int)substr($id,-4);$source=substr($id,0,7);$r=$b['run'];$c=$b['correction'];$q=$b['replay'];$t=$b['test'];
        $normal=$b['normal'];$p=$b['primary'];$a=$normal['evidence_admission'];$bad=$b['missing_trail'];$hist=$b['historical'];$mis=$b['mismatch'];$hashes=$b['oracle']['payload']['artifacts'];
        $f=[];$add=function($expected,$actual,$from)use(&$f){$f[]=['expected'=>$expected,'actual'=>$actual,'source'=>$from];};
        $run=function()use($add,$r,$normal,$p){$add((int)$p['baseline_run_before_export']['run_id'],$normal['run_summary']['run_id'],'primary eod_runs before export');$add('SUCCESS',$normal['run_summary']['terminal_status'],'actual one controlled provider observation succeeds');$add($r['source']['run']['trade_date_requested'],$r['data']['trade_date_requested'],'retained E023 eod_runs');};
        $correction=function()use($add,$c){$add((int)$c['source']['correction']['correction_id'],$c['data']['correction_id'],'E024 primary approved correction');$add((int)$c['source']['prior']['publication_id'],$c['data']['prior_publication']['publication_id'],'E024 immutable primary baseline');$add((int)$c['source']['replacement']['publication_id'],$c['data']['new_publication']['publication_id'],'E024 primary replacement');};
        $replay=function()use($add,$mis,$p){$add('MISMATCH',$mis['replay_result']['comparison_result'],'separately authored wrong eligibility hash before comparator');$add('FAIL',$mis['replay_result']['replay_status'],'MISMATCH classification');$add($p['mismatch_expectation']['expected_artifact_context']['eligibility_batch_hash'],$mis['expected_state']['eligibility_batch_hash'],'independent expected fixture, not actual output');$add($p['baseline_publication_before_export']['eligibility_batch_hash'],$mis['actual_state']['eligibility_batch_hash'],'primary sealed publication');};
        $config=function()use($add,$r){$add(hash('sha256',$r['source']['config_snapshot']['resolved_config_json']),$r['manifest']['config_snapshot_hash'],'retained primary canonical config preimage');};
        $runhash=function()use($add,$r,$hashes){foreach(['bars','indicators','eligibility'] as $k)$add($hashes[$k],$r['data'][$k.'_batch_hash'],'immutable independently versioned v5 expected publication manifest');};
        $corrhash=function()use($add,$c){foreach(['bars','indicators','eligibility'] as $k){$f=$k.'_batch_hash';$add($c['source']['prior'][$f],$c['data']['old_hashes'][$f],'primary prior publication '.$f);$add($c['source']['replacement'][$f],$c['data']['new_hashes'][$f],'primary replacement publication '.$f);}};
        $test=function()use($add,$t){$add(0,$t['data']['exit_code'],'actual process exit code');$add($t['data']['output_sha256'],hash('sha256',$t['stdout']),'correlated immutable stdout digest');$add(true,strpos($t['test_id'],'B19CorrectionMariaDbProofTest::test_real_changed_unchanged_failed_and_historical_correction')===0,'actual test source ID and exact command');};
        $bound=function()use($add,$a,$b){$add('evidence_admission_v2',$a['criteria']['schema_version'],'explicit implementation criteria version');$add($b['exporter_hash'],$a['criteria']['exporter_sha256'],'independent current source bytes');$add(hash('sha256',json_encode($a['required_sections'],JSON_UNESCAPED_SLASHES)),$a['criteria']['required_sections_sha256'],'declared criteria list content binding, not completeness of undeclared requirements');$add(false,$a['claims']['output_correctness'],'frozen admission capability boundary');$add(false,$a['claims']['cross_version_equivalence'],'frozen admission version boundary');$add(false,$a['claims']['current_governed_verification'],'runtime output alone cannot create issued proof');};
        if($source==='MD-S076')switch($n){
            case 6:case 12:case 19:$run();break;
            case 7:$add('HELD',$b['held']['data']['terminal_status'],'retained E023 deliberately held real run');$add($b['held']['source']['run']['terminal_status'],$b['held']['data']['terminal_status'],'primary persisted held status');$add('FAILED',$b['failed']['run_summary']['terminal_status'],'actual401 failure');break;
            case 8:case 21:case 22:case 23:$correction();break;
            case 9:case 28:case 29:case 32:$replay();break;
            case 10:case 34:case 36:$test();break;
            case 13:$add($b['oracle']['publication_manifest_hash'],$r['manifest']['publication_manifest_hash'],'immutable independent manifest literal');break;
            case 14:$runhash();break;
            case 15:$add(count($r['source']['events']),$r['events']['event_count'],'primary individual event records before export');break;
            case 16:$counts=[];foreach($r['source']['events'] as $e)if(!empty($e['reason_code']))$counts[$e['reason_code']]=($counts[$e['reason_code']]??0)+1;ksort($counts);$got=$r['events']['reason_code_counts'];ksort($got);$add($counts,$got,'independent reason distribution from primary events');break;
            case 17:$config();break;
            case 18:$add($r['source']['run']['created_at'],$r['data']['created_at'],'primary persisted creation timestamp');break;
            case 24:$corrhash();break;
            case 25:$add(true,$c['data']['publication_switch'],'actual approved changed correction replaces prior; input105->106');$add((int)$c['source']['prior']['publication_id'],$c['data']['supersession']['supersedes_publication_id'],'primary supersession edge');break;
            case 26:$corrhash();$add(false,$c['data']['old_hashes']['bars_batch_hash']===$c['data']['new_hashes']['bars_batch_hash'],'controlled105->106 correction changes canonical values');$add('CHANGED',$c['data']['changed_decision'],'actual correction diff');break;
            case 30:$add(hash('sha256',$q['source']['config_snapshot']['resolved_config_json']),$q['data']['bound_inputs']['config_snapshot_hash'],'primary replay config snapshot preimage');break;
            case 31:$add($hashes['bars'],$q['data']['bars_batch_hash'],'immutable independent v5 canonical hash');break;
            case 35:$add(true,strpos($t['fixture_family'],'r0025-synthetic-v2-candidate-v5')===0,'actual command/test fixture source independently versioned');break;
            case 38:$test();$add((int)$c['source']['replacement']['publication_id'],$c['data']['new_publication']['publication_id'],'executed publication assertions, not a method-name claim');break;
            case 40:$run();$test();break;
            case 41:$runhash();$corrhash();break;
            case 42:$runhash();$test();break;
            case 53:$run();$correction();$replay();$test();$add('HELD',$b['held']['data']['terminal_status'],'fifth major actual class, retained primary held producer');break;
            default:throw new \LogicException('unmapped '.$id);
        }
        elseif($source==='MD-S068')switch($n){
            case 2:case 16:case 24:case 30:$run();$correction();$replay();$test();break;
            case 5:$correction();break;
            case 6:$test();break;
            case 8:case 23:case 25:$runhash();break;
            case 9:$add(count($p['event_trail_before_export']),$normal['run_event_summary']['event_count'],'primary events, not count copied from export');break;
            case 10:$add($p['baseline_run_before_export']['finished_at'],$a['evidence_created_at'],'persisted own finished_at');break;
            case 11:$run();$add($p['failed_run']['terminal_status'],$b['failed']['run_summary']['terminal_status'],'real401 persisted failed outcome');break;
            case 12:$add((int)$p['baseline_publication_before_export']['publication_id'],$normal['publication_context']['publication_id'],'primary actual sealed publication');break;
            case 14:$run();$add(true,is_int($a['selector_id'])&&$a['selector_id']>0,'positive real ID, symbolic/zero separately rejected through exporter helper');break;
            case 15:$add($p['baseline_run_before_export']['finished_at'],$a['evidence_created_at'],'actual own timestamp; missing/symbolic/invalid calendar production negatives in BoundaryTest');break;
            case 31:$add('ADMITTED_INCOMPLETE',$bad['evidence_admission']['evidence_admission_state'],'real otherwise complete world with own mandatory trail deleted');$add('INCOMPLETE',$bad['evidence_completeness']['evidence_completeness_state'],'final mandatory evidence excluded, not partially complete');$add(false,$bad['evidence_admission']['claims']['declared_evidence_completeness'],'same unavailable trail must not claim completeness');break;
            case 32:case 34:case 35:$bound();break;
            case 33:case 36:$replay();$add('ADMITTED_COMPLETE',$mis['evidence_admission']['evidence_admission_state'],'complete independently mismatching diagnostic evidence');$add(false,$mis['evidence_admission']['claims']['output_correctness'],'admitted FAIL must not imply data correctness');break;
            default:throw new \LogicException('unmapped '.$id);
        }
        elseif($source==='MD-S063')switch($n){
            case 2:case 7:case 14:$run();break;
            case 3:case 15:$correction();break;
            case 4:case 16:case 154:$replay();break;
            case 5:$run();$correction();$replay();$corrhash();break;
            case 8:$add('READABLE',$normal['run_summary']['publishability_state'],'actual sealed successful run');$add('HELD',$b['held']['data']['terminal_status'],'real held world, not falsely readable success');break;
            case 9:$add((int)$p['baseline_publication_before_export']['publication_id'],$normal['pointer_context']['pointer_publication_id'],'actual current pointer before correction');$add(false,$hist['publication_context']['is_current_publication'],'after real supersession selected baseline remains historical');$add(false,$hist['pointer_context']['readable_pointer_validated'],'historical selected publication cannot imply current readable pointer');break;
            case 10:$runhash();$corrhash();break;
            case 11:$add((int)$c['source']['prior']['publication_id'],$c['data']['baseline_historical_publication_proof']['publication_id'],'immutable prior historical proof');$add('SEALED',$c['data']['baseline_historical_publication_proof']['seal_state'],'old publication preserved independently of current pointer');break;
            case 12:$config();$runhash();foreach($p['declared_expected_before_replay']['expected_artifact_semantic_bindings'] as $field=>$value){$add($value,$b['bindings'][$field]['value'],'independently authored versioned fixture binding '.$field);$add('AVAILABLE',$b['bindings'][$field]['status'],'all applicable fixture bindings required and available, not merely matching NULL');}break;
            case 151:$add(count($p['event_trail_before_export']),$normal['run_event_summary']['event_count'],'diagnosis from exact persisted events');$corrhash();break;
            case 152:$run();$corrhash();$config();break;
            case 153:$correction();$corrhash();break;
            case 155:$add(false,$hist['publication_context']['is_current_publication'],'selected historical publication distinct from actual pointer');$add(false,$hist['pointer_context']['readable_pointer_validated'],'current readability not granted to historical publication');$add('FAILED',$b['failed']['run_summary']['terminal_status'],'real unsealed failed run');$add(null,$b['failed']['publication_context']['publication_id'],'missing publication not invented');break;
            case 168:$add('ADMITTED_INCOMPLETE',$bad['evidence_admission']['evidence_admission_state'],'incoherent eventless case not fully auditable');$add('INCOMPLETE',$bad['evidence_completeness']['evidence_completeness_state'],'single consistent completeness result');$replay();break;
            default:throw new \LogicException('unmapped '.$id);
        }
        else throw new \LogicException($id);
        return $f;
    }
    private function conflicts(array $facts): array {return array_values(array_filter($facts,function($f){return $f['actual']!==$f['expected'];}));}
    /** @dataProvider predicates */
    public function test_each_predicate_uses_independent_execution_values(string $id): void {
        $facts=$this->facts($id,self::world());$this->assertNotEmpty($facts,$id);$this->assertSame([],$this->conflicts($facts),$id.' precise independently sourced values');
        foreach($facts as $fact)$this->assertNotEmpty($fact['source']);
    }
    /** @dataProvider predicates */
    public function test_each_predicate_detects_missing_or_substituted_evidence(string $id): void {
        $facts=$this->facts($id,self::world());$this->assertSame([],$this->conflicts($facts));
        // Substitute each observed artifact value separately; keep the independently sourced expectation intact.
        foreach($facts as $k=>$fact){$changed=$facts;$changed[$k]['actual']=$fact['actual']===null?'fabricated_required_identity':null;$this->assertNotEmpty($this->conflicts($changed),$id.' observation '.$k);}
    }

    public function test_current_pack_companions_bind_changed_bytes_without_claiming_currentness_or_complete_missing_context(): void {
        $raw=self::root().'/storage/app/market-data/evidence/MD-B19-A001/';
        foreach(['normal','historical','failed','missing_trail','correction','match','mismatch'] as $kind){$prefix='E025_current_'.$kind.'_';$index=self::read($raw.$prefix.'artifact_manifest.json');$this->assertSame('Asia/Jakarta',$index['timestamp_context']['platform_timezone']);
            foreach($index['files'] as $name=>$member){$this->assertSame(hash_file('sha256',$raw.$prefix.$name),$member['sha256'],$kind.'/'.$name);$this->assertSame(filesize($raw.$prefix.$name),$member['bytes']);foreach($member['binding_refs'] as $ref)$this->assertArrayHasKey(substr($ref,strlen('#/binding_contexts/')),$index['binding_contexts']);}
            $a=self::read($raw.$prefix.'evidence_admission.json');$this->assertSame($kind==='failed'||$kind==='missing_trail'?'ADMITTED_INCOMPLETE':'ADMITTED_COMPLETE',$a['evidence_admission_state']);$this->assertSame($a['evidence_admission_state']==='ADMITTED_COMPLETE',$a['claims']['declared_evidence_completeness']);$this->assertFalse($a['claims']['output_correctness']);
        }
        $b=self::world();$this->assertSame($b['primary']['declared_expected_before_replay']['expected_artifact_semantic_bindings'],$b['primary']['mismatch_expectation']['expected_artifact_semantic_bindings'],'only explicit comparison expectation differs; no copied actual values');
        $this->assertNotSame($b['mismatch']['expected_state']['eligibility_batch_hash'],$b['mismatch']['actual_state']['eligibility_batch_hash']);
        $this->assertSame('NOT_CURRENT_POINTER',$b['historical']['publication_context']['current_pointer_status']);$this->assertFalse($b['historical']['pointer_context']['readable_pointer_validated']);
    }
}
