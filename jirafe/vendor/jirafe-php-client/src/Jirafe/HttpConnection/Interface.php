<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API HTTP connection interface.
 *
 * @author knplabs.com
 */
interface Jirafe_HttpConnection_Interface
{
    /**
     * Makes get request to the service.
     *
     * @param   string  $path   relative resource path
     * @param   array   $query  resource query string
     *
     * @return  Jirafe_HttpConnection_Response
     */
    function get($path, array $query = array());

    /**
     * Makes head request to the service.
     *
     * @param   string  $path   relative resource path
     * @param   array   $query  resource query string
     *
     * @return  Jirafe_HttpConnection_Response
     */
    function head($path, array $query = array());

    /**
     * Makes post request to the service.
     *
     * @param   string  $path       relative resource path
     * @param   array   $query      resource query string
     * @param   array   $parameters resource post parameters
     *
     * @return  Jirafe_HttpConnection_Response
     */
    function post($path, array $query = array(), array $parameters = array());

    /**
     * Makes put request to the service.
     *
     * @param   string  $path       relative resource path
     * @param   array   $query      resource query string
     * @param   array   $parameters resource post parameters
     *
     * @return  Jirafe_HttpConnection_Response
     */
    function put($path, array $query = array(), array $parameters = array());

    /**
     * Makes delete request to the service.
     *
     * @param   string  $path       relative resource path
     * @param   array   $query      resource query string
     * @param   array   $parameters resource post parameters
     *
     * @return  Jirafe_HttpConnection_Response
     */
    function delete($path, array $query = array(), array $parameters = array());
}
