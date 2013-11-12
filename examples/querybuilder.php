<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);
header('Content-type: text/plain; charset=utf-8');
include_once "../vendor/autoload.php";

use fdo\FDO;
use fdo\FQL;

$fdo = new FDO("", array(
    FDO::ATTR_BIGINT_PARSE => FDO::BIGINT_PARSE_AS_STRING // for 32bit OS
));

$fql = new FQL($fdo);
$subquery = FQL::create($fdo)->select("uid")->from("user")->whereIN("uid", array(4,5,6,7,8,9,10), FDO::PARAM_INT)->getQueryString();

$stmt = $fql->select(array("uid", "name"))
    ->from("user")
    ->where("uid = ? OR uid IN(?)", array(4, $subquery), array(FDO::PARAM_INT, FDO::PARAM_SUB_QUERY))
    ->orderBy("name")
    ->limit(1,10)
    ->execute();

//echo $stmt; die;

echo "Result:". PHP_EOL;
echo str_pad("num", 4, " ", STR_PAD_LEFT) . " ". str_pad("uid", 22) . "name" . PHP_EOL;
$i = 0;
while($friend = $stmt->fetch(FDO::FETCH_OBJ)) {
    echo str_pad(++$i, 4, " ", STR_PAD_LEFT) . " " . str_pad($friend->uid, 22) . $friend->name . PHP_EOL;
}


$stmt->debugDumpParams();
