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

    const FETCH_JSON = 0;
    const FETCH_ASSOC = 1;
    const FETCH_CLASS = 2;
    const FETCH_INTO = 3;
    const FETCH_OBJ = 4;

    CONST ATTR_DEFAULT_FETCH_MODE = 1;

    protected $statement;

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
        $stmt = new FDOStatement($this, $statement);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Prepares a statement for execution and returns a statement object
     * @param $statement
     * @return FDOStatement
     */
    function prepare($statement)
    {
        $this->statement = $statement;

        return new FDOStatement($this, $this->statement);
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
} 