<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API resource abstraction.
 *
 * @author knplabs.com
 */
abstract class Jirafe_Api_Resource
{
    private $id;
    private $collection;
    private $client;

    /**
     * Initializes resource instance.
     *
     * @param   mixed                   $id         resource ID
     * @param   Jirafe_Api_Collection   $collection collection instance
     * @param   Jirafe_Client           $client     API client
     */
    public function __construct($id, Jirafe_Api_Collection $collection, Jirafe_Client $client)
    {
        $this->id = $id;
        $this->collection = $collection;
        $this->client = $client;
    }

    /**
     * Returns resource ID.
     *
     * @return  mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns resource collection.
     *
     * @return  Jirafe_Api_Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Returns API client.
     *
     * @return  Jirafe_Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Returns current resource path.
     *
     * @return  string
     */
    public function getPath()
    {
        return sprintf('%s/%s', $this->collection->getPath(), $this->id);
    }

    /**
     * Makes get request to the resource.
     *
     * @param   array   $query  resource query parameters
     * @param   string  $token  override token
     *
     * @return  Jirafe_HttpConnection_Response
     */
    public function doGet(array $query = array(), $token = null)
    {
        return $this->client->get($this->getPath(), $query, $token);
    }

    /**
     * Makes head request to the resource.
     *
     * @param   array   $query  resource query parameters
     * @param   string  $token  override token
     *
     * @return  Jirafe_HttpConnection_Response
     */
    public function doHead(array $query = array(), $token = null)
    {
        return $this->client->head($this->getPath(), $query, $token);
    }

    /**
     * Makes put request to the resource.
     *
     * @param   array   $query      resource query parameters
     * @param   array   $parameters resource post parameters
     * @param   string  $token      override token
     *
     * @return  Jirafe_HttpConnection_Response
     */
    public function doPut(array $query = array(), array $parameters = array(), $token = null)
    {
        return $this->client->put($this->getPath(), $query, $parameters, $token);
    }

    /**
     * Makes delete request to the resource.
     *
     * @param   array   $query      resource query parameters
     * @param   array   $parameters resource post parameters
     * @param   string  $token      override token
     *
     * @return  Jirafe_HttpConnection_Response
     */
    public function doDelete(array $query = array(), array $parameters = array(), $token = null)
    {
        return $this->client->delete($this->getPath(), $query, $parameters, $token);
    }
}
