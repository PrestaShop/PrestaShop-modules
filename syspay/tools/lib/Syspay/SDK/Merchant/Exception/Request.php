<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Exception thrown when an API request fails
 */
class Syspay_Merchant_RequestException extends RuntimeException
{
    private $httpCode;
    private $headers;
    private $body;

    public function __construct($httpCode, $headers = null, $body = null, Exception $previous = null)
    {
        $this->httpCode = $httpCode;
        $this->headers  = $headers;
        $this->body     = $body;
        $message = '';
        $code = 0;
        // Look if the body contains an actual error
        if ($decoded = Tools::jsonDecode($this->body)) {
            if (isset($decoded->error) && ($decoded->error instanceof stdClass)) {
                $code = $decoded->error->code;
                $message = $decoded->error->message;
            }
        }
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            parent::__construct($message, $code, $previous);
        } else {
            parent::__construct($message, $code);
        }
    }

    /**
     * Gets the response HTTP code
     * @return int HTTP code
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Gets the raw response headers
     * @return string HTTP headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Gets the raw response body
     * @return string body
     */
    public function getBody()
    {
        return $this->body;
    }
}
