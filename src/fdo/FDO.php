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

    protected $statement;

    function __construct()
    {

    }

    /**
     * Executes a FQL statement, returning a result set as a FDOStatement object
     * @param string $statement
     * @return FDO
     */
    function query($statement)
    {

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
} 