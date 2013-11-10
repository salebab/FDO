<?php
/**
 * Created by PhpStorm.
 * User: Sasa
 * Date: 10.11.13.
 * Time: 02.52
 */

namespace library\facebook;


class FDO
{
    const API_URL = "https://graph.facebook.com/fql?q=";

    protected $statement;

    function __construct()
    {

    }

    /**
     * Executes an FQL statement, returning a result set as a FDOStatement object
     * @param string $statement
     * @return FDO
     */
    function query($statement)
    {

    }

    /**
     * Prepares a statement for execution and returns a statement object
     * @param $statement
     */
    function prepare($statement)
    {
        $this->statement = $statement;
    }
} 