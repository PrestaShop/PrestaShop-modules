<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API HTTP connection response.
 *
 * @author knplabs.com
 */
class Jirafe_HttpConnection_Response
{
    private $response;
    private $headers;
    private $errorCode;
    private $errorMessage;

    /**
     * Initializes response object.
     *
     * @param   string  $response       response body
     * @param   array   $headers        response headers
     * @param   integer $errorCode      response error number
     * @param   string  $errorMessage   response error message
     */
    public function __construct($response, array $headers, $errorCode, $errorMessage)
    {
        $this->response     = $response;
        $this->headers      = $headers;
        $this->errorCode    = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Returns response body.
     *
     * @return  string
     */
    public function getBody()
    {
        return $this->response;
    }

    /**
     * Returns response body.
     *
     * @return  string
     */
    public function __toString()
    {
        return $this->getBody();
    }

    /**
     * Returns decoded JSON object from response body.
     *
     * @return  mixed
     */
    public function getJson()
    {
        return json_decode($this->getBody(), true);
    }

    /**
     * Returns response headers.
     *
     * @return  array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Checks whether response has error.
     *
     * @return  Boolean
     */
    public function hasError()
    {
        return 0 !== $this->errorCode;
    }

    /**
     * Returns response error code.
     *
     * @return  integer
     */
    public function getErrorCode()
    {
        return $this->hasError() ? $this->errorCode : null;
    }

    /**
     * Returns response error message.
     *
     * @return  string
     */
    public function getErrorMessage()
    {
        return $this->hasError() ? $this->errorMessage : null;
    }
}
