<?php

namespace fdo;

/**
 * Class FDOStatement
 *
 * @author Aleksandar Babic <salebab@gmail.com>
 * @package fdo
 */
class FDOStatement implements \Iterator
{
    /**
     * @var string
     */
    public $queryString;

    /**
     * Copy of $queryString, used for binding and processing
     * $queryString is untouched
     * @var string
     */
    private $preparedQueryString;

    /**
     * @var \stdClass
     */
    protected $result;

    /**
     * @var FDO
     */
    protected $fdo;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var int
     */
    private $mode;


    /**
     * @var array
     */
    private $debug = array();

    /**
     * @param FDO $fdo
     */
    function __construct(FDO $fdo)
    {
        $this->fdo = $fdo;
        $this->mode = $this->fdo->getAttribute(FDO::ATTR_DEFAULT_FETCH_MODE);
    }

    function bindColumn()
    {
        throw new FDOException("Not yet implemented");
    }

    /**
     * Binds a parameter to the specified variable name
     *
     * @param string $parameter
     * @param mixed $variable
     * @param int $data_type
     * @throws FDOException
     */
    function bindParam($parameter, $variable, $data_type = FDO::PARAM_STR)
    {
        if(substr($parameter, 0, 1) !== ":") {
            $parameter = ":". $parameter;
        }

        $queryString = $this->getPreparedQueryString();

        if(strpos($queryString, $parameter) === false) {
            throw new FDOException("Parameter $parameter not found in the statement.");
        }

        $variable = $this->fdo->quote($variable, $data_type);
        $queryString = str_replace($parameter, $variable, $queryString);
        $this->setPreparedQueryString($queryString);
    }

    function bindValue()
    {
        throw new FDOException("Not yet implemented");
    }

    /**
     * Returns the number of columns in the result set
     *
     * @return int
     */
    function columnCount()
    {
        return $this->valid() ? count($this->current()) : 0;
    }

    /**
     * Executes a prepared statement
     *
     * @throws FDOException
     */
    function execute()
    {
        $this->rewind();

        $api = FDO::API_URL . urlencode($this->getPreparedQueryString());

        if($this->fdo->getAttribute(FDO::ATTR_ACCESS_TOKEN)) {
            $api .="&access_token=". $this->fdo->getAttribute(FDO::ATTR_ACCESS_TOKEN);
        }

        if($this->fdo->getAttribute(FDO::ATTR_BIGINT_PARSE) == FDO::BIGINT_PARSE_AS_STRING) {
            $api .= "&format=json-strings";
        }

        $this->debug["API"] = $api;
        $this->result = $this->getResultSet($api);

        if(property_exists($this->result, "error")) {
            throw new FDOException($this->result->error->message, $this->result->error->code);
        }

        if(!property_exists($this->result, "data")) {
            throw new FDOException("There is no data object in result set");
        }

        // reset statement
        $this->setPreparedQueryString($this->queryString);
    }

    /**
     * Fetches the next row from a result set
     *
     * @param int|null $mode
     * @return mixed
     */
    function fetch($mode = null)
    {
        if($mode !== null) {
            $this->setFetchMode($mode);
        }

        if($this->valid()) {
            $result = $this->current();
            $this->next();
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * Returns an array containing all of the result set rows
     *
     * TODO: implement fetch style
     *
     * @return mixed
     */
    function fetchAll()
    {
        return $this->result->data;
    }

    /**
     * Returns a single column from the next row of a result set
     *
     * @param string|int $column Column name or column index
     * @return mixed
     */
    function fetchColumn($column = 0)
    {
        if($this->valid()) {
            if(is_int($column)) {
                $row = array_values($this->fetch(FDO::FETCH_ASSOC));
                $result = $row[$column];
            } else {
                $row = $this->fetch(FDO::FETCH_ASSOC);
                $result = $row[$column];
            }
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * Fetches the next row and returns it as an object.
     *
     * @param string $className
     * @param array $constructorArgs
     * @return mixed|object
     */
    function fetchObject($className = "stdClass", $constructorArgs = array())
    {
        if($className == "stdClass") {
            return $this->fetch(FDO::FETCH_OBJ);
        }

        $reflection = new \ReflectionClass($className);
        $object = $reflection->newInstanceArgs($constructorArgs);

        foreach($this->fetch(FDO::FETCH_ASSOC) as $name => $value) {
            $object->{$name} = $value;
        }

        return $object;
    }

    /**
     * Set the default fetch mode for this statement
     *
     * @param int $mode
     */
    function setFetchMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return int
     */
    public function rowCount()
    {
        return count($this->result->data);
    }

    /**
     * Get a result set from Facebook by API URL
     *
     * @param string $url
     * @return \stdClass
     * @throws FDOException
     */
    private function getResultSet($url)
    {
        $ch = \curl_init();
        \curl_setopt($ch, CURLOPT_URL, $url);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        \curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if(false === ($data = \curl_exec($ch))) {
            $exception = new FDOException('Curl error: ' . \curl_error($ch), \curl_errno($ch));
            \curl_close($ch);
            throw $exception;
        }

        $contentType = \curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        if(strpos($contentType, "application/json") === false) {
            $exception = new FDOException("Invalid content type ($contentType).");
            \curl_close($ch);
            throw $exception;
        }

        return json_decode($data);
    }

    function rewind() {
        $this->position = 0;
    }

    function current() {
        $data = $this->result->data[$this->position];

        switch ($this->mode) {
            case FDO::FETCH_OBJ:
                return (object) $data;
                break;

            case FDO::FETCH_JSON:
                return json_encode($data);
                break;

            default:
                return (array) $data;
        }
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }

    function valid() {
        return isset($this->result->data[$this->position]);
    }

    /**
     * @return string
     */
    private function getPreparedQueryString()
    {
        return !empty($this->preparedQueryString) ? $this->preparedQueryString : $this->setPreparedQueryString($this->queryString);
    }

    /**
     * @param $queryString
     * @return mixed
     */
    private function setPreparedQueryString($queryString)
    {
        $this->preparedQueryString = $queryString;
        $this->debug["FQL"] = $queryString;
        return $this->preparedQueryString;
    }

    public function debugDumpParams()
    {
        foreach($this->debug as $key => $value) {
            echo $key ." ". $value . PHP_EOL;
        }
    }
}