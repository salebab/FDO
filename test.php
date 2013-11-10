<?php
include "vendor/autoload.php";
use fdo\FDO;

class User {

	function __construct() {
	}
}

$fdo = new FDO();

$fql = "SELECT uid, name, sex FROM user WHERE uid IN(4,5)";
//$stmt = $fdo->prepare($fql);
//$stmt->execute();

foreach($fdo->query($fql) as $k => $object) {
	var_dump($object);
}

$fql = "SELECT uid, name FROM user WHERE uid = :uid";
$stmt = $fdo->prepare($fql);
$stmt->setFetchMode(FDO::FETCH_OBJ);
$stmt->bindParam(":uid", 5, FDO::PARAM_INT);
$stmt->execute();
echo "Row count: ". $stmt->rowCount();
var_dump($stmt->fetchObject("User"));