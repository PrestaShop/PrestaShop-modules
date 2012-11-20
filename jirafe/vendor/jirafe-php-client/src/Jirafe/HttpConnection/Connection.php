<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API CURL connection.
 *
 * @author knplabs.com
 */
abstract class Jirafe_HttpConnection_Connection implements Jirafe_HttpConnection_Interface
{
    /**
     * @see Jirafe_HttpConnection_Interface::get()
     */
    public function get($path, array $query = array())
    {
        return $this->makeRequest('GET', $path, $query);
    }

    /**
     * @see Jirafe_HttpConnection_Interface::head()
     */
    public function head($path, array $query = array())
    {
        return $this->makeRequest('HEAD', $path, $query);
    }

    /**
     * @see Jirafe_HttpConnection_Interface::post()
     */
    public function post($path, array $query = array(), array $parameters = array())
    {
        return $this->makeRequest('POST', $path, $query, $parameters);
    }

    /**
     * @see Jirafe_HttpConnection_Interface::put()
     */
    public function put($path, array $query = array(), array $parameters = array())
    {
        return $this->makeRequest('PUT', $path, $query, $parameters);
    }

    /**
     * @see Jirafe_HttpConnection_Interface::delete()
     */
    public function delete($path, array $query = array(), array $parameters = array())
    {
        return $this->makeRequest('DELETE', $path, $query, $parameters);
    }

    /**
     * Initializes response object.
     *
     * @param   string  $body           response body string
     * @param   array   $headers        response headers array
     * @param   integer $errorCode      response error number
     * @param   string  $errorMessage   response error message
     *
     * @return  Jirafe_HttpConnection_Response
     */
    protected function initializeResponse($body, array $headers, $errorCode, $errorMessage)
    {
        return new Jirafe_HttpConnection_Response($body, $headers, $errorCode, $errorMessage);
    }

    /**
     * Make HTTP request.
     *
     * @param   string  $method     HTTP method name
     * @param   string  $path       path to request
     * @param   array   $query      query parameters
     * @param   array   $parameters post parameters
     */
    abstract protected function makeRequest($method, $path, array $query = array(), array $parameters = array());
}
