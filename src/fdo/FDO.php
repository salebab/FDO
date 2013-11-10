<?php

namespace fdo;

/**
 * FDO - Facebook Data Object
 */
class FDO
{
    /**
     * Facebook's Graph API url
     */
    const API_URL = "https://graph.facebook.com/fql?q=";

    const PARAM_BOOL = 0;
    const PARAM_INT = 1;
    const PARAM_STR = 2;


    const FETCH_JSON = 0;
    const FETCH_ASSOC = 1;
    const FETCH_CLASS = 2;
    const FETCH_INTO = 3;
    const FETCH_OBJ = 4;

    CONST ATTR_DEFAULT_FETCH_MODE = 1;

    protected $attr = array(
        self::ATTR_DEFAULT_FETCH_MODE => self::FETCH_ASSOC
    );

    function __construct($access_token = null, $attributes = array())
    {
        $this->access_token = $access_token;

        if(!empty($attributes)) {
            $this->attr = array_merge($this->attr, $attributes);
        }

    }

    /**
     * Executes a FQL statement, returning a result set as a FDOStatement object
     * @param string $statement
     * @return FDOStatement
     */
    function query($statement)
    {
        $stmt = $this->prepare($statement);
        $stmt->execute();
        return $stmt;
    }

    /**
     * @param string $queryString
     * @return FDOStatement
     */ 
    private function createStatement($queryString)
    {
        $stmt = new FDOStatement($this);
        $stmt->queryString = $queryString;

        return $stmt;
    }

    /**
     * Prepares a statement for execution and returns a statement object
     * @param $statement
     * @return FDOStatement
     */
    function prepare($statement)
    {
        return $this->createStatement($statement);
    }

    function setAttribute($attribute, $value)
    {
        $this->attr[$attribute] = $value;
    }

    function getAttribute($attribute)
    {
        if(array_key_exists($attribute, $this->attr)) {
            return $this->attr[$attribute];
        } else {
            return 0;
        }
    }

    function quote($string, $type = FDO::PARAM_STR)
    {
        switch ($type) {
            case FDO::PARAM_BOOL:
                $string = (bool) $string;
                $string = ($string) ? "true" : "false";
                $result = "'". $string ."'";
                break;            

            case FDO::PARAM_INT:
                $string = (int) $string;
                $result = $string;
                break;

            case FDO::PARAM_STR:
            default:
                $string = (string) $string;
                $result =  "'". str_replace("'", "\'", $string) ."'";
                break;
        }
        return $result;
    }
} 