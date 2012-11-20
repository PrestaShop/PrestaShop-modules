<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API collection abstraction.
 *
 * @author knplabs.com
 */
abstract class Jirafe_Api_Collection
{
    private $parent;
    private $client;

    /**
     * Initialize collection instance.
     *
     * @param   Jirafe_Api_Resource $parent parent resource
     * @param   Jirafe_Client       $client API client
     */
    public function __construct(Jirafe_Api_Resource $parent = null, Jirafe_Client $client)
    {
        $this->parent = $parent;
        $this->client = $client;
    }

    /**
     * Returns parent resource.
     *
     * @return  Jirafe_Api_Resource
     */
    public function getParent()
    {
        return $this->parent;
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
     * Returns current resource URL path.
     *
     * @return  string
     */
    public function getPath()
    {
        $collectionName = $this->getCollectionName();

        if (null !== $this->parent) {
            return sprintf('%s/%s', $this->parent->getPath(), $collectionName);
        }

        return $collectionName;
    }

    /**
     * Makes get request to the API with current resource path.
     *
     * @param   array   $query  query string parameters
     * @param   string  $token  override token
     *
     * @return  Jirafe_HttpConnection_Response
     */
    public function doGet(array $query = array(), $token = null)
    {
        return $this->client->get($this->getPath(), $query, $token);
    }

    /**
     * Makes post request to the API with current resource path.
     *
     * @param   array   $query      query string parameters
     * @param   array   $parameters post parameters
     * @param   string  $token      override token
     *
     * @return  Jirafe_HttpConnection_Response
     */
    public function doPost(array $query = array(), array $parameters = array(), $token = null)
    {
        return $this->client->post($this->getPath(), $query, $parameters, $token);
    }

    /**
     * Returns current collection name.
     *
     * @return  string
     */
    protected function getCollectionName()
    {
        return strtolower(preg_replace('/[^_]+_/', '', get_class($this)));
    }
}
