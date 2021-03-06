<?php
/**
 * FDO - Facebook Data Object
 */
namespace fdo;

/**
 * FDO - Facebook Data Object
 *
 * @author Aleksandar Babic <salebab@gmail.com>
 * @package fdo
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
    const PARAM_FUNC = 3;
    const PARAM_SUB_QUERY = 4;

    const FETCH_JSON = 0;
    const FETCH_ASSOC = 1;
    const FETCH_CLASS = 2;
    const FETCH_INTO = 3;
    const FETCH_OBJ = 4;

    const BIGINT_PARSE_AUTO = 0;
    const BIGINT_PARSE_REGULAR = 1;
    const BIGINT_PARSE_AS_STRING = 2;

    const ATTR_DEFAULT_FETCH_MODE = 1;
    const ATTR_ACCESS_TOKEN = 2;
    const ATTR_BIGINT_PARSE = 3;

    /**
     * @var array
     */
    private $attr = array(
        self::ATTR_DEFAULT_FETCH_MODE => self::FETCH_ASSOC,
        self::ATTR_BIGINT_PARSE => self::BIGINT_PARSE_AUTO
    );

    /**
     * @param string|null $access_token
     * @param array $attributes
     */
    function __construct($access_token = null, $attributes = array())
    {
        $this->setAttribute(self::ATTR_ACCESS_TOKEN, $access_token);

        if(!empty($attributes)) {
            $this->attr = array_merge($this->attr, $attributes);
        }
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
     * @param $string
     * @param int $type
     * @return int|string
     */
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

            case FDO::PARAM_FUNC:
            case FDO::PARAM_SUB_QUERY:
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

    /**
     * @param $attribute
     * @param $value
     */
    function setAttribute($attribute, $value)
    {
        $this->attr[$attribute] = $value;
    }

    /**
     * @param $attribute
     * @return int
     */
    function getAttribute($attribute)
    {
        if(array_key_exists($attribute, $this->attr)) {
            return $this->attr[$attribute];
        } else {
            return 0;
        }
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
}