<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);
header('Content-type: text/plain; charset=utf-8');
include_once "../vendor/autoload.php";

use fdo\FDO;

$fdo = new FDO("", array(
    FDO::ATTR_BIGINT_PARSE => FDO::BIGINT_PARSE_AS_STRING // for 32bit OS
));

$fql = "SELECT object_id, comment_info FROM photo WHERE owner = ? LIMIT 5";

$stmt = $fdo->prepare($fql);

$stmt->bindValue(1, "19292868552", FDO::PARAM_STR);
$stmt->execute();

print_r($stmt->fetchAll());