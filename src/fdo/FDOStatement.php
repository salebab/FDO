<?php

namespace fdo;

class FDOStatement
{

    protected $data;
    protected $statement;


    /**
     * @var FDO
     */
    protected $fdo;

    private $cursor = 0;

    function __construct(FDO $fdo, $statement = "")
    {
        $this->fdo = $fdo;
        $this->statement = $statement;
    }

    function execute()
    {
        $api = FDO::API_URL . $this->getQuery();
        $this->data = json_decode($this->getData($api));

        if(property_exists($this->data, "error")) {
            throw new FDOException($this->data->error->message, $this->data->error->code);
        }

        if(!property_exists($this->data, "data")) {
            throw new FDOException("There is no data object in result set");
        }

        $this->cursor = 0;
    }

    function fetch()
    {
        if(array_key_exists($this->cursor, $this->data->data)) {
            $row = $this->data->data[$this->cursor];
        } else {
            $row = null;
        }

        $this->cursor +=1;
        
        return $row;
    }

    function fetchAll()
    {
        return $this->data->data;
    }

    function fetchObject()
    {

    }

    private function getQuery()
    {
        // TODO: build statement
        return urlencode($this->statement);
    }


    /**
     * @param $url
     * @return mixed
     * @throws FDOException
     */
    private function getData($url)
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
        return $data;
    }
}