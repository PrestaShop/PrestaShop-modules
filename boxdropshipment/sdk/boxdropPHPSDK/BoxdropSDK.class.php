<?php
/*
 * 2014 boxdrop Group AG
 *
 * NOTICE OF LICENSE
 *
 * Copyright (C) 2014 boxdrop Group AG
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
 * associated documentation files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
 * LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author     boxdrop Group AG
 * @copyright  boxdrop Group AG
 * @license    http://opensource.org/licenses/MIT The MIT License (MIT)
 * International Registered Trademark & Property of boxdrop Group AG
 */

/**
 * boxdrop PHP SDK
 *
 * Access the boxdrop API v1_0
 *
 * @author  sweber <sw@boxdrop.com>
 * @package BoxdropSDK
 */
require_once dirname(__FILE__).'/BoxdropCrypt.class.php';
require_once dirname(__FILE__).'/BoxdropSDKException.class.php';

class BoxdropSDK extends BoxdropCrypt {

  const BOXDROP_API_VERSION     = 'v1_0';
  const BOXDROP_PHP_SDK_VERSION = 'v1_0';

  private static $instance   = null;

  /**
   * Returns an instance
   *
   * @author sweber <sw@boxdrop.com>
   * @param  string $user_id
   * @param  string $password
   * @param  string $hmac_key
   */
  public static function getInstance($user_id = null, $password = null, $hmac_key = null) {

    if (self::$instance === null) {

      self::$instance = new BoxdropSDK($user_id, $password, $hmac_key);
    }

    return self::$instance;
  }


  /**
   * Initialization
   *
   * @author sweber <sw@boxdrop.com>
   * @param  string $user_id
   * @param  string $password
   * @param  string $hmac_key
   */
  public function __construct($user_id, $password, $hmac_key) {

    parent::__construct($user_id, $password, $hmac_key);
  }


  /**
   * Request information from the API. Returns a JSON object with the response
   *
   * Will automatically handle all request encryption and response decryption.
   *
   * @author sweber <sw@boxdrop.com>
   * @param  string $element
   * @param  string $method
   * @param  array  $params
   * @param  JSONobject
   */
  public function request($element, $method, $params = array()) {

    $this->buildRequest($element, $method, $params);
    $this->doEncrypt($this->raw_request);
    $this->sendRequest();

    return $this->decryptResponse();
  }


  /**
   * Builds up the request array and signs it
   *
   * @author sweber <sw@boxdrop.com>
   * @param  string $element
   * @param  string $method
   * @param  array  $params
   * @param  void
   */
  private function buildRequest($element, $method, $params = array()) {

    $payload = array('element' => $element,
                     'method'  => $method,
                     'params'  => $params,
                     'time'    => time(),
                     'user_id' => $this->user_id);

    $hmac_request      = $element.$method.$payload['time'].$this->user_id;
    $payload['mac']    = hash_hmac('sha256', $hmac_request, $this->hmac_key);
    $this->raw_request = json_encode($payload);
  }


  /**
   * Sends a signed and encrypted request to receive an encrypted response
   *
   * @author sweber <sw@boxdrop.com>
   * @return void
   */
  private function sendRequest() {

    $data = array('payload' => $this->encrypted);

    curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($data));

    $this->raw_response = curl_exec($this->curl);

    if ($this->raw_response == '') {

      throw new BoxdropSDKException('Empty / no response from remote host');
    }
  }


  /**
   * Decrypts a response from API endpoints
   *
   * @author sweber <sw@boxdrop.com>
   * @return string
   */
  private function decryptResponse() {

    $this->raw_response = json_decode($this->raw_response);

    if ($this->raw_response !== null) {

      $response = $this->doDecrypt($this->raw_response->payload);
      $this->verifySignature($response);

      return $response;
    }

    return '';
  }
}