<?php

namespace fdo;

class FDOStatement implements \Iterator
{

    /**
     * 
     * @var string
     */
    public $queryString;

    /**
     * 
     * @var string
     */ 
    private $preparedQueryString;

    /**
     * @var array
     */
    protected $result;
    protected $public;
    protected $params;


    /**
     * @var FDO
     */
    protected $fdo;

    private $position = 0;

    private $mode;

    function __construct(FDO $fdo)
    {
        $this->fdo = $fdo;
        $this->mode = $this->fdo->getAttribute(FDO::ATTR_DEFAULT_FETCH_MODE);
    }

    function execute()
    {
        $this->rewind();

        $api = FDO::API_URL . urlencode($this->getPreparedQueryString());
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

    function setFetchMode($mode)
    {
        $this->mode = $mode;
    }

    function fetch()
    {
        if($this->valid()) {
            $result = $this->current();
            $this->next();
        } else {
            $result = null;
        }

        return $result;
    }

    function fetchAll()
    {
        return $this->result->data;
    }

    function fetchObject()
    {

    }

    /**
     * @param $url
     * @return mixed
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
        echo $queryString;
        $this->setPreparedQueryString($queryString);
    }

    private function getPreparedQueryString()
    {
        return !empty($this->preparedQueryString) ? $this->preparedQueryString : $this->setPreparedQueryString($this->queryString);
    }

    private function setPreparedQueryString($queryString)
    {
        $this->preparedQueryString = $queryString;
        return $this->preparedQueryString;
    }
}