<?php
require dirname(__DIR__,2).'/vendor/autoload.php';
$app=require dirname(__DIR__,2).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$database=$argv[1]??'';
if(!preg_match('/^tradeaxis_testing_b19_owner_[a-f0-9]{12}$/D',$database))throw new RuntimeException('UNSAFE_RESULT_TEST_DATABASE');
config()->set('database.connections.b19_result_worker',array_merge(config('database.connections.mysql'),['database'=>$database]));
config()->set('database.default','b19_result_worker');
\Illuminate\Support\Facades\DB::purge('b19_result_worker');
\Illuminate\Database\Eloquent\Model::setConnectionResolver(\Illuminate\Support\Facades\DB::getFacadeRoot());
class E049ResultWorkerStatement extends PDOStatement
{
    private static $announced=false;
    protected function __construct(){}
    public function execute($parameters=null)
    {
        if(!self::$announced && stripos($this->queryString,'md_replay_result_sequences')!==false && preg_match('/insert\s+ignore/i',$this->queryString)){
            self::$announced=true;
            echo "WAITING_SEQUENCE\n";flush();
        }
        if(!self::$announced && stripos($this->queryString,'md_replay_result_cases')!==false && preg_match('/for update/i',$this->queryString)){
            self::$announced=true;echo "WAITING_CASE\n";flush();
        }
        return parent::execute($parameters);
    }
}
$connection=\Illuminate\Support\Facades\DB::connection();
$id=(int)$connection->selectOne('SELECT CONNECTION_ID() AS id')->id;
$connection->getPdo()->setAttribute(PDO::ATTR_STATEMENT_CLASS,[E049ResultWorkerStatement::class,[]]);
echo 'READY '.$id."\n";flush();
try{
    $verifier=new \App\Application\MarketData\Services\ReplayVerificationService(
        new \App\Infrastructure\Persistence\MarketData\EodEvidenceRepository(),new \App\Infrastructure\Persistence\MarketData\EodPublicationRepository(),
        new \App\Infrastructure\Persistence\MarketData\ReplayResultRepository());
    $result=$verifier->verifyRunAgainstFixture((int)$argv[2],$argv[4],isset($argv[5])?(int)$argv[5]:null,(int)$argv[3]);
    echo json_encode(['outcome'=>'COMPLETED','connection'=>$id,'replay_id'=>$result['replay_id'],'replay_status'=>$result['replay_status'],
        'writer_execution_id'=>$result['writer_execution_id'],'result_reused'=>$result['result_reused']])."\n";
}catch(Throwable $e){echo json_encode(['outcome'=>'ERROR','connection'=>$id,'class'=>get_class($e),'reason'=>$e->getMessage()])."\n";exit(2);}
