<?php
define('EXT','.php');
include 'vendor\autoload.php';
//\Ant\Autoloader::register();
//\Ant\Autoloader::addNamespace('Ant\\Database','System'.DIRECTORY_SEPARATOR.'Database');

function show($msg){
    echo "<pre>";
    var_dump($msg);
    echo "</pre>";
}
function debug(){
    echo "<pre>";
    var_dump(func_get_args());
    echo "</pre>";
    die;
}
//loli

$config = [
    'dsn'=>'mysql:dbname=test;host=127.0.0.1',
    'user'=>'root',
    'password'=>'123456',
];

//$stream = fopen('php://temp', 'w+');
//stream_copy_to_stream(fopen('php://input', 'r'), $stream);
//rewind($stream);
//try{
//    $stream = fopen('php://input','r');
//    $stream = new Ant\Http\Stream($stream);
//    echo $stream->read(5)."\n";
//    $stream->seek(1,SEEK_CUR);
//    echo $stream->getContents()."\n";
//    var_dump($stream->eof());
//}catch(\InvalidArgumentException $e){
//    echo $e->getMessage();
//}catch (Exception $e){
//    echo $e->getMessage();
//}
$abc = function(){
    echo "this is abc start \n";
    yield 1;
    echo "this is abc end \n";
};

$qwe = function (){
    echo "this is qwe start \n";
    yield 2;
    echo "this is qwe end \n";
};
//闭包函数
$handlers = [$abc,$qwe];
$stack = [];
$result = null;
foreach ($handlers as $handler) {
    // reset before each loop, only save last handler return value
    $result = null;
    $generator = call_user_func_array($handler, []);

    if ($generator instanceof \Generator) {
        $stack[] = $generator;

        $yield_value = $generator->current();
    } elseif ($generator !== null) {
        // return without yield
        $result = $generator;
    }
}

$return = ($result !== null);
while ($generator = array_pop($stack)) {
    if ($return) {
        $generator->send($result);
    }

    $generator->next();

    // PHP7之前没有Generator::getReturn()
    if (version_compare(PHP_VERSION, '7.0.0', '<')) {
        $return = true;
    }

    if (!$return) {
        $return = true;
        $result = $generator->getReturn();
    }
}



//
//$pdo = new Ant\Database\Connector\Mysql($config);
//
//$stat = $pdo->table('info')
//    ->whereNotIn('name',['aulun','alex','ajax'])
//    ->get();
////    ->select(['a.name','a.sex','a.age'])
////    ->where(['name'=>'in','age'=>'<>'],[['aulun','alex','ajax'],18])
////    ->where('age = ?','18')
////    ->orWhere(['sex'=>'in'],[['woman','man']])
////    ->where(['name'=>'ajax','age'=>'18'])
////    ->order('age','DESC')
////    ->alias('a')
////    ->get();
//
//show($stat->getAll());
//
