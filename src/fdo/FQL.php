<?php
namespace fdo;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * FQL Query Builder and helpers
 *
 * @package fdo
 * @author Aleksandar Babic <salebab@gmail.com>
 */
class FQL
{
    const AUTO_LIMIT = 'AUTO_LIMIT';
    const DEFAULT_AUTO_LIMIT = 10;

    /**
     * @var FDO
     */
    private $fdo;

    private $select = array();
    private $from;
    private $where = array();
    private $limit;
    private $orderBy;

    private $params = array();
    private $types = array();


    private $autolimit = array(
        "in_use" => 0,
        "iteration" => 0,
        "from" => 0,
        "offset" => self::DEFAULT_AUTO_LIMIT,
        "max_limit" => 0
    );

    /**
     * @var LoggerInterface
     */
    private $logger;

    function __construct(FDO $fdo, $logger = null)
    {
        $this->fdo = $fdo;
        $this->logger = ($logger !== null) ? $logger : new NullLogger();
    }

    /**
     * @param FDO $fdo
     * @return FQL
     */
    static function create(FDO $fdo)
    {
        return new self($fdo);
    }

    /**
     * @param string|array $fieldList
     * @return $this
     */
    function select($fieldList)
    {
        if(is_string($fieldList)) {
            $fieldList = array($fieldList);
        }

        $this->select += $fieldList;

        return $this;
    }

    /**
     *
     * @param string $table
     * @return $this
     */
    function from($table) {
        $this->from = $table;
        return $this;
    }

    /**
     * @param string $statement
     * @param mixed $params array or scalar
     * @param array|int $types
     * @return $this
     */
    function where($statement, $params = null, $types = FDO::PARAM_STR)
    {
        $this->where[] = "(". $statement .")";

        if(!empty($params)) {
            if(!is_array($params)) {
                $params = array($params);
            }

            foreach($params as $index => $value) {
                $this->params[] = $value;
                end($this->params);
                $key = key($this->params);

                if(is_array($types) && array_key_exists($index, $types)) {
                    $type = $types[$index];
                } elseif(is_int($types)) {
                    $type = $types;
                } else {
                    $type = FDO::PARAM_STR;
                }
                $this->types[$key] = $type;
            }
        }

        return $this;
    }

    /**
     * @param string $column
     * @param mixed $param array or scalar
     * @param int|array $type
     * @return $this
     */
    function whereIn($column, $param = null, $type = FDO::PARAM_STR)
    {
        if(is_array($param)) {
            $placeholder = implode(",", array_fill(0, count($param), "?"));
        } else {
            $param = array($param);
            $placeholder = "?";
        }
        $this->where($column . " IN (". $placeholder . ")", $param, $type);

        return $this;
    }

    /**
     * @param int|string $param1 or AUTO_LIMIT
     * @param null|int $param2
     * @return $this
     */
    function limit($param1, $param2 = null)
    {

        if($param1 === self::AUTO_LIMIT) {
            $from = 0;
            $offset = ($param2 !== null) ? $param2 : self::DEFAULT_AUTO_LIMIT;
            $this->autolimit["in_use"] = 1;
            $this->autolimit["offset"] = $offset;

            return $this->limit($from, $offset);
        }

        $this->limit = $param1;
        if($param2) {
            $this->limit .= ",".$param2;
        }

        return $this;
    }

    /**
     * Only for AUTO LIMIT mode
     * @param $limit
     * @return $this
     */
    function setMaxLimit($limit)
    {
        $this->autolimit["max_limit"] = $limit;
        return $this;
    }

    /**
     * @param string|array $statement
     * @return $this
     */
    function orderBy($statement) {
        if(is_array($statement)) {
            $statement = implode(",", $statement);
        }
        $this->orderBy = $statement;

        return $this;
    }

    /**
     * @return string
     */
    function getQueryString()
    {
        return $this->prepare()->getQueryString();
    }

    /**
     * Prepare this FQL
     *
     * @return FDOStatement
     */
    function prepare()
    {
        $query = "SELECT ". implode(",", $this->select) ." "
            . "FROM ". $this->from ." "
            . "WHERE ". implode(" AND ", $this->where) ." ";
        if($this->orderBy) {
            $query .= "ORDER BY ". $this->orderBy ." ";
        }
        if($this->limit) {
            $query .= "LIMIT ". $this->limit ." ";
        }

        $stmt = $this->fdo->prepare($query);

        if(!empty($this->params)) {
            foreach($this->params as $parameter => $value) {
                $stmt->bindValue($parameter, $value, $this->types[$parameter]);
            }
        }

        $this->logger->debug("FQL: ". $stmt->getQueryString());

        return $stmt;
    }

    /**
     * Execute FQL and return stmt
     * @return FDOStatement
     */
    function execute()
    {
        $stmt = $this->prepare();
        $stmt->execute();
        return $stmt;
    }

    /**
     * Iterate trough row set and for each row provided callback will be executed, with row as param
     *
     * Usage:
     * $fql->iterate(function($row) { });
     *
     * @param $callback
     * @param int $mode
     */
    function iterate($callback, $mode = FDO::FETCH_OBJ)
    {
        $stmt = $this->prepare();
        $stmt->execute();

        $rowCount = $stmt->rowCount();

        if(!$rowCount) {
            return;
        }

        while($row = $stmt->fetch($mode)) {
            call_user_func($callback, $row);
        }

        if($this->autolimit["in_use"] && $rowCount <= $this->autolimit["offset"]) {
            $this->autolimit["from"] = $this->autolimit["from"] + $this->autolimit["offset"];

            if($this->autolimit["max_limit"] <= $this->autolimit["from"]) {
                return;
            }

            $this->limit($this->autolimit["from"], $this->autolimit["offset"]);
            $this->iterate($callback, $mode);
        }
    }
} 