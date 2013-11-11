FDO - Facebook Data Object
==========================

The _**Facebook Data Object**_ (FDO) is a simple interface for querying data from Facebook Graph API using **FQL (Facebook Query Language)**. It uses CURL for querying.

_FDO interface is very similar to PDO, since PDO has a very understandable methods and majority of PHP developers are very familiar with them._
Examples
--------

**Fetch a single user object**

    use fdo\FDO;
    $fdo = new FDO();

    $fql = "SELECT uid, name, sex FROM user WHERE uid = :uid";
    $stmt = $fdo->prepare($fql);
    $stmt->bindValue(":uid", 4, FDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(FDO::FETCH_OBJ);

    var_dump($result);

**Fetch single column, fetch collection**

    use fdo\FDO;
    $fdo = new FDO($access_token); // Provide an active access_token

    // Count friends
    $fql = "SELECT friend_count FROM user WHERE uid = me()";
    echo "Count friends: ". $fdo->query($fql)->fetchColumn();

    // List me and all of my friends
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

**Important notice:** The library is still in a heavy development and some planed functionality is not implement yet. Please contribute if you think that the library is useful.
