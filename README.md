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
    $stmt->bindParam(":uid", 4, FDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(FDO::FETCH_OBJ);

    var_dump($result);


**Important notice:** The library is still in a heavy development and some planed functionality is not implement yet. Please contribute if you think that the library is useful.
