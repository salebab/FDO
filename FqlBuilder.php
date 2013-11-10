<?php
/**
 * Created by PhpStorm.
 * User: Sasa
 * Date: 10.11.13.
 * Time: 00.16
 */

namespace library\facebook;


class FqlBuilder
{
    const URL = "https://graph.facebook.com/";


    private $from;
    private $fields = array();
    private $where = array();
    private $limit;
    private $orderBy;

    function __construct()
    {

    }


    function addField($column)
    {
        $this->fields[] = $column;
    }

    function addFields(array $columns)
    {
        $this->fields = array_merge($this->fields, $columns);
    }


    function getQuery()
    {
        $query =  "SELECT ". implode(",", $this->fields) ." FROM ". $this->from ." ";

        if(!empty($this->where)) {
            $query .= "WHERE ". implode(" AND ", $this->where);
        }

        return $query;
    }


    /**
     * Create "SELECT [fields] statement
     *
     * @param array|string|null $fields Comma separated fields or array
     * @return FqlBuilder
     */
    function select($fields = null)
    {
        if(is_array($fields)) {
            $this->addFields($fields);
        } elseif(!empty($fields)) {
            $fields = explode(",", $fields);
            $this->addFields($fields);
        }

        return $this;
    }

    /**
     * @param $table
     * @return $this
     */
    function from($table) {
        $this->from = $table;
        return $this;
    }

    /**
     * @param $condition
     * @param string|array $params
     * @return FqlBuilder
     */
    function where($condition, $params = array())
    {
        if(!is_array($params)) {
            $params = array($params);
        }

        $values = array();

        foreach($params as $param) {
            list($qm, $v) = $this->prepareParam($param);
            $values[] = $v;
            $find = strpos($condition, "?");
            $condition = substr_replace($condition, $qm, $find, 1);
        }
        $this->where[] = vsprintf($condition, $values);

        return $this;
    }

    /**
     * @param $field
     * @param array|string $params
     * @return $this
     */
    function whereIn($field, $params)
    {
        $qm = array_fill(0, count($params), "?");
        $this->where($field . " IN (". implode(",", $qm) .")", $params);

        return $this;
    }

    private function prepareParam($param)
    {
        if(is_int($param)) {
            return array("%d", $param);
        }
        else {
            return array("'%s'", str_replace("'", "\\'", $param));
        }
    }


    /**
     * Convert array to 'value1','value2' ...
     *
     * @param $params
     * @return string
     */
    private function array2set($params)
    {
        return vsprintf(implode(",", array_fill(0, count($params), "'%s'")), $params);
    }


    function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    function orderBy($orderBy)
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    function getResult()
    {
        return $this->getJson(self::URL ."/fql?q=". urlencode($this->getQuery()));
    }

}