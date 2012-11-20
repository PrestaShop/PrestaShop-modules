<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API client.
 *
 * @author knplabs.com
 */
class Jirafe_Client
{
    private $token;
    private $connection;
    private $version = '2.0';

    /**
     * Initializes client.
     *
     * @param   string                          $token      API access token
     * @param   Jirafe_HttpConnection_Interface $connection optional connection instance
     */
    public function __construct($token = null, Jirafe_HttpConnection_Interface $connection = null)
    {
        $this->token = $token;

        if (null === $connection) {
            $version = $this->version;
            $connection = new Jirafe_HttpConnection_Curl('https://api.jirafe.com/v1',443, 10, "jirafe-ecommerce-phpclient/{$version}");
        }

        $this->connection = $connection;
    }

    /**
     * Returns currently used token.
     *
     * @return  string
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * Set token for subsequent requests.
     *
     * @return  string
     */
    public function setToken($token)
    {
        return $this->token = $token;
    }

    /**
     * Returns applications collection or single application if you provide ID.
     *
     * @param   integer $id application id. If specified - method will return object instead of
     *                      collection
     * @return  Jirafe_Api_Collection_Applications|Jirafe_Api_Resource_Application
     */
    public function applications($id = null)
    {
        $applications = new Jirafe_Api_Collection_Applications($this);

        if (null !== $id) {
            return $applications->get($id);
        }

        return $applications;
    }

    /**
     * Returns users collection or single user if you provide ID.
     *
     * @param   integer $id user id. If specified - method will return object instead of
     *                      collection
     * @return  Jirafe_Api_Collection_Users|Jirafe_Api_Resource_User
     */
    public function users($id = null)
    {
        $users = new Jirafe_Api_Collection_Users($this);

        if (null !== $id) {
            return $users->get($id);
        }

        return $users;
    }

    /**
     * Makes get request to the service.
     *
     * @param   string  $path   relative resource path
     * @param   array   $query  resource query string
     * @param   string  $token  optional token override
     *
     * @return  Jirafe_HttpConnection_Response
     */
    public function get($path, array $query = array(), $token = null)
    {
        $token = null !== $token ? $token : $this->token;
        if (false !== $token) {
            $query += array('token' => $token);
        }

        return $this->connection->get($path, $query);
    }

    /**
     * Makes head request to the service.
     *
     * @param   string  $path   relative resource path
     * @param   array   $query  resource query string
     * @param   string  $token  optional token override
     *
     * @return  Jirafe_HttpConnection_Response
     */
    public function head($path, array $query = array(), $token = null)
    {
        $token = null !== $token ? $token : $this->token;
        if (false !== $token) {
            $query += array('token' => $token);
        }

        return $this->connection->head($path, $query);
    }

    /**
     * Makes post request to the service.
     *
     * @param   string  $path       relative resource path
     * @param   array   $query      resource query string
     * @param   array   $parameters resource post parameters
     * @param   string  $token      optional token override
     *
     * @return  Jirafe_HttpConnection_Response
     */
    public function post($path, array $query = array(), array $parameters = array(), $token = null)
    {
        $token = null !== $token ? $token : $this->token;
        if (false !== $token) {
            $query += array('token' => $token);
        }

        return $this->connection->post($path, $query, $parameters);
    }

    /**
     * Makes put request to the service.
     *
     * @param   string  $path       relative resource path
     * @param   array   $query      resource query string
     * @param   array   $parameters resource post parameters
     * @param   string  $token      optional token override
     *
     * @return  Jirafe_HttpConnection_Response
     */
    public function put($path, array $query = array(), array $parameters = array(), $token = null)
    {
        $token = null !== $token ? $token : $this->token;
        if (false !== $token) {
            $query += array('token' => $token);
        }

        return $this->connection->put($path, $query, $parameters);
    }

    /**
     * Makes delete request to the service.
     *
     * @param   string  $path       relative resource path
     * @param   array   $query      resource query string
     * @param   array   $parameters resource post parameters
     * @param   string  $token      optional token override
     *
     * @return  Jirafe_HttpConnection_Response
     */
    public function delete($path, array $query = array(), array $parameters = array(), $token = null)
    {
        $token = null !== $token ? $token : $this->token;
        if (false !== $token) {
            $query += array('token' => $token);
        }

        return $this->connection->delete($path, $query, $parameters);
    }
}
