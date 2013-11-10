<?php
include "vendor/autoload.php";
use fdo\FDO;

$fdo = new FDO();

$fql = "SELECT uid, name, sex FROM user WHERE uid IN(4,5)";
$stmt = $fdo->prepare($fql);
$stmt->execute();

while($object = $stmt->fetch()) {
	var_dump($object);
}

$fql = "SELECT uid, name FROM user WHERE uid = 4";
$stmt = $fdo->query($fql);
$stmt->setFetchMode(FDO::FETCH_JSON);
var_dump($stmt->fetch());