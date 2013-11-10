<?php
include "vendor/autoload.php";

$fdo = new fdo\FDO();

$fql = "SELECT uid, name, sex FROM user WHERE uid IN(4,5)";
$stmt = $fdo->prepare($fql);
$stmt->execute();

while($object = $stmt->fetch()) {
	var_dump($object);
}