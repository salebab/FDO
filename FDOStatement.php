<?php
/**
 * Created by PhpStorm.
 * User: Sasa
 * Date: 10.11.13.
 * Time: 02.52
 */

namespace library\facebook;


class FDOStatement implements \Traversable
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
        $this->data = json_decode($this->getData(FDO::API_URL . $this->getQuery()), true, 5120, JSON_BIGINT_AS_STRING);

        if(array_key_exists("error", $this->data)) {
            throw new FDOException($this->data["error"]["message"], $this->data["error"]["code"]);
        }

        if(!array_key_exists("data", $this->data)) {
            throw new FDOException("There is no data object in result set");
        }

        $this->cursor = 0;
    }

    function fetch()
    {
        if(array_key_exists($this->cursor, $this->data["data"])) {
            $row = $this->data["data"][$this->cursor];
        } else {
            $row = false;
        }

        $this->cursor +=1;
        return $row;
    }

    function fetchAll()
    {
        return $this->data["data"];
    }

    function fetchObject()
    {

    }

    private function getQuery()
    {
        // TODO: build statement
        return "";
    }


    /**
     * @param $url
     * @return mixed
     * @throws FDOException
     */
    private function getData($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if(false === ($data = curl_exec($ch))) {
            $exception = new FDOException('Curl error: ' . curl_error($ch), curl_errno($ch));
            curl_close($ch);
            throw $exception;
        }
        curl_close($ch);
        return $data;
    }
}