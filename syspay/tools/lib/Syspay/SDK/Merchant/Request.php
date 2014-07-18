<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Base class for requests
 */
abstract class Syspay_Merchant_Request
{
    /**
     * Get the HTTP method to use to make the request
     * @return string HTTP method
     */
    abstract public function getMethod();

    /**
     * Get the path to call to make the request
     * @return string API path
     */
    abstract public function getPath();

    /**
     * Build an object based on the decoded response received from the API (turned to a stdClass object)
     * @param  stdClass $response The data to build the object from
     * @return mixed    The object built
     */
    abstract public function buildResponse(stdClass $response);

    /**
     * Get the data to send to the API for the request
     * @return array An array of data that will be json-encoded by the Syspay_Merchant_Client
     */
    public function getData()
    {
        return null;
    }

}
