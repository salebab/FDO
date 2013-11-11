<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);
header('Content-type: text/plain; charset=utf-8');
include_once "../vendor/autoload.php";

use fdo\FDO;
$access_token = "";

$fdo = new FDO($access_token, array(
    FDO::ATTR_BIGINT_PARSE => FDO::BIGINT_PARSE_AS_STRING // for 32bit OS
));

$fql = "SELECT friend_count FROM user WHERE uid = me()";
echo "Count friends: ". $fdo->query($fql)->fetchColumn();

echo PHP_EOL;

$fql = "SELECT uid, name FROM user WHERE uid = :me OR uid IN (SELECT uid2 FROM friend WHERE uid1 = :me) ORDER BY name";
$stmt = $fdo->prepare($fql);
$stmt->bindValue(":me", "me()", FDO::PARAM_FUNC);
$stmt->execute();

echo "Friends:". PHP_EOL;
echo str_pad("num", 4, " ", STR_PAD_LEFT) . " ". str_pad("uid", 22) . "name" . PHP_EOL;
$i = 0;
while($friend = $stmt->fetch(FDO::FETCH_OBJ)) {
    echo str_pad(++$i, 4, " ", STR_PAD_LEFT) . " " . str_pad($friend->uid, 22) . $friend->name . PHP_EOL;
}


$stmt->debugDumpParams();
