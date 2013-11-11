<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

include_once "../vendor/autoload.php";

use fdo\FDO;

$fdo = new FDO();

$fql = "SELECT uid, name, sex FROM user WHERE uid = :uid";
$stmt = $fdo->prepare($fql);
$stmt->bindValue(":uid", 4, FDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(FDO::FETCH_OBJ);

var_dump($result);

