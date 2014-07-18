<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Base API client
 * @see  https://app.syspay.com/bundles/emiuser/doc/merchant_api.html#emerchant-rest-api
 */
class Syspay_Merchant_Client
{
    const BASE_URL_PROD    = 'https://app.syspay.com';
    const BASE_URL_SANDBOX = 'https://app-sandbox.syspay.com';

    protected $username;
    protected $secret;
    protected $baseUrl;

    protected $body    = null;
    protected $headers = null;
    protected $data    = null;

    /**
     * Creates a new client object
     * @param string $username The Syspay API username
     * @param string $secret   The Syspay API shared secret
     * @param string $baseUrl  The base URL the request should be made to (optional, defaults to prod environment)
     */
    public function __construct($username, $secret, $baseUrl = null)
    {
        $this->username = $username;
        $this->secret   = $secret;
        $this->baseUrl  = (null === $baseUrl)?self::BASE_URL_PROD:$baseUrl;
    }

    /**
     * Generates the x-wsse header
     *
     * @param  string   $username The Syspay API username
     * @param  string   $secret   The Syspay API shared secret
     * @param  string   $nonce    A random string (optional, will be generated)
     * @param  DateTime $created  The creation date of this header (optional, defaults to now)
     * @return string   The value to give to the x-wsse header
     */
    protected function generateAuthHeader($username, $secret, $nonce = null, DateTime $created = null)
    {
        if (null === $nonce) {
            $nonce = md5(rand(), true);
        }

        if (null === $created) {
            $created = new DateTime();
        }

        $created = $created->format('U');

        $digest = base64_encode(sha1($nonce . $created . $secret, true));
        $b64nonce = base64_encode($nonce);

        return sprintf('AuthToken MerchantAPILogin="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
                            $username, $digest, $b64nonce, $created);
    }

    /**
     * Make a request to the Syspay API
     * @param  Syspay_Merchant_Request $request The request to send to the API
     * @return mixed The response to the request
     * @throws Syspay_Merchant_RequestException If the request could not be processed by the API
     */
    public function request(Syspay_Merchant_Request $request)
    {
        $this->body = $this->headers = $this->data = null;

        $headers = array(
            'Accept: application/json',
            'X-Wsse: ' . $this->generateAuthHeader($this->username, $this->secret)
        );


        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($request->getPath(), '/');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // TODO: verify ssl and provide certificate in package
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $method = Tools::strtoupper($request->getMethod());

        // Per-method special handling
        switch($method) {
            case 'PUT':
            case 'POST':
                $body = Tools::jsonEncode($request->getData());

                array_push($headers, 'Content-Type: application/json');
                array_push($headers, 'Content-Length: ' . Tools::strlen($body));

                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                break;

            case 'GET':
                $queryParams = $request->getData();
                if (is_array($queryParams)) {
                    $url .= '?' . http_build_query($queryParams);
                }
                break;

            case 'DELETE':
                break;

            default:
                throw new Exception('Unsupported method given: ' . $method);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        list($headers, $body) = explode("\r\n\r\n", $response, 2);
        $this->headers = $headers;
        $this->body    = $body;

        if (!in_array($httpCode, array(200, 201))) {
            throw new Syspay_Merchant_RequestException($httpCode, $headers, $body);
        }

        $decoded = Tools::jsonDecode($body);

        if (($decoded instanceof stdClass) && isset($decoded->data) && ($decoded->data instanceof stdClass)) {
            $this->data = $decoded->data;
            return $request->buildResponse($decoded->data);
        } else {
            throw new Syspay_Merchant_UnexpectedResponseException('Unable to decode response from json', $body);
        }

        return false;
    }

    /**
     * Get the raw body of the last request.
     * @return string The last request's response body, or null if the request failed.
     */
    public function getRawBody()
    {
        return $this->body;
    }

    /**
     * Get the raw headers of the last request.
     * @return string The last request's headers, or null if the request failed
     */
    public function getRawHeaders()
    {
        return $this->headers;
    }

    /**
     * Get the username
     * @return string Merchant username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the shared secret
     * @return string secret
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Get the base URL
     * @return string Base URL
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Get the (decoded) response data, if available
     * @return mixed Response data
     */
    public function getData()
    {
        return $this->data;
    }
}
