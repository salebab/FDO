<?php
namespace fdo;

/**
 * FQL Query Builder
 * @package fdo
 * @author Aleksandar Babic <salebab@gmail.com>
 */
class FQL
{
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

    function __construct(FDO $fdo)
    {
        $this->fdo = $fdo;
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
     * @param int $param1
     * @param null|int $param2
     * @return $this
     */
    function limit($param1, $param2 = null)
    {
        $this->limit = $param1;
        if($param2) {
            $this->limit .= ",".$param2;
        }

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

        return $stmt;
    }

    /**
     * Execute FQL and return stmt
     * @return FDOStatement
     */
    function execute()
    {
        $stmt = $this->prepare();

        try {
            $stmt->execute();
        } catch(\Exception $e) {
            $stmt->debugDumpParams();
        }

        return $stmt;
    }
} 