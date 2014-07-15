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
 * Crypting and cURL
 *
 * @author  sweber <sw@boxdrop.com>
 * @package BoxdropSDK
 */
class BoxdropCrypt {

  /**
   * @var string $curl Our cURL handle
   */
  protected $curl = null;

  /**
   * @var string $hmac_key The HMAC key used for signing a request
   */
  protected $hmac_key = null;

  /**
   * @var string $password The password used for encryption, must be 32 bit hex
   */
  protected $password = null;

  /**
   * @var integer $user_id The API user id
   */
  protected $user_id = null;

  /**
   * @var string $iv
   */
  protected $iv = '35293228542622c2';

  /**
   * @var boolean $is_initialized
   */
  protected $is_initialized = false;

  /**
   * @var string $encrypted
   */
  protected $encrypted = null;

  /**
   * @var string $raw_request
   */
  protected $raw_request = null;

  /**
   * @var string $raw_response
   */
  protected $raw_response = null;


  /**
   * constructor
   *
   * @author sweber <sw@boxdrop.com>
   * @param  string $user_id
   * @param  string $password
   * @param  string $hmac_key
   */
  public function __construct($user_id, $password, $hmac_key) {

    if (empty($hmac_key)) {

      throw new BoxdropSDKException('You must specify an API HMAC key');
    }

    if (empty($password)) {

      throw new BoxdropSDKException('You must specify an API password');
    }

    if (empty($user_id)) {

      throw new BoxdropSDKException('You must specify an API user ID');
    }

    $this->hmac_key = $hmac_key;
    $this->password = $password;
    $this->user_id  = $user_id;
  }


  /**
   * destructor
   *
   * @author sweber <sw@boxdrop.com>
   */
  public function __destruct() {

    curl_close($this->curl);
  }


  /**
   * Encrypts the given json-encoded input string using AES-256 and returns it base64 encoded
   *
   * @author sweber <sw@boxdrop.com>
   * @param  string $plaintext
   * @return string
   */
  public function doEncrypt($plaintext) {

    if ($plaintext == '') {

      throw new BoxdropSDKException('Nothing to encrypt');
    }

    $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
    $key    = $this->password;

    if (mcrypt_generic_init($cipher, $key, $this->iv) != -1) {

      $encrypted = mcrypt_generic($cipher, $plaintext);
      mcrypt_generic_deinit($cipher);

      if ($encrypted == '') {

        throw new BoxdropSDKException('Encryption failed');
      }

      $this->encrypted = base64_encode($encrypted);
    } else {

      $this->encrypted = '';

      throw new BoxdropSDKException('Encryption initialization failed');
    }
  }


  /**
   * Decrypts the given base64-encoded input string using AES-256 and returns it json-encoded
   *
   * @author sweber <sw@boxdrop.com>
   * @param  string $encrypted
   * @return string
   */
  public function doDecrypt($encrypted) {

    if ($encrypted == '') {

      throw new BoxdropSDKException('Nothing to decrypt');
    }

    $encrypted = base64_decode($encrypted);
    $cipher    = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
    $key       = $this->password;

    if (mcrypt_generic_init($cipher, $key, $this->iv) != -1) {

      $decrypted = trim(mdecrypt_generic($cipher, $encrypted));
      mcrypt_generic_deinit($cipher);

      $decoded = json_decode($decrypted);

      if ($decoded == '') {

        throw new BoxdropSDKException('Decryption failed');
      }

      return $decoded;
    }

    return '';
  }


  /**
   * Verifies a requests signature (MAC-field)
   *
   * @author sweber <sw@boxdrop.com>
   * @return void
   */
  public function verifySignature($request) {

    $hmac_request = $request->element.$request->method.$request->time.$request->user_id;
    $test_mac     = hash_hmac('sha256', $hmac_request, $this->hmac_key);

    if ($test_mac != $request->mac) {

      throw new BoxdropSDKException('Signature invalid! '.$test_mac.' - vs - '.$request->mac);
    }

    return true;
  }


  /**
   * Inits the cURL connection to our endpoint
   *
   * @author sweber  <sw@boxdrop.com>
   * @param  string  $country The country the user has been igven access to
   * @param  boolean $is_test Triggers test system connection
   * @param  boolean $source  Optional source for statistics
   * @return void
   */
  public function initConnection($country, $is_test, $source = 'PHP-PureSDK') {

    $endpoint = 'https://api.boxdrop.net/'.BoxdropSDK::BOXDROP_API_VERSION.'/endpoint';

    if ($is_test) {

      $endpoint = 'http://alphatest.boxdrop.com/api.php/'.BoxdropSDK::BOXDROP_API_VERSION.'/endpoint';
    }

    $this->curl = curl_init($endpoint);

    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($this->curl, CURLOPT_USERAGENT,      'boxdrop PHP SDK '.BoxdropSDK::BOXDROP_PHP_SDK_VERSION);
    curl_setopt($this->curl, CURLOPT_AUTOREFERER,    false);
    curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($this->curl, CURLOPT_TIMEOUT,        10);
    curl_setopt($this->curl, CURLOPT_MAXREDIRS,      1);
    curl_setopt($this->curl, CURLOPT_HTTPHEADER,     array('X-boxdrop-User: '.$this->user_id,
                                                           'X-boxdrop-Country: '.$country,
                                                           'X-boxdrop-Source: '.$source));
    curl_setopt($this->curl, CURLOPT_POST,           1);
    curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($this->curl, CURLOPT_VERBOSE,        1);

    $this->is_initialized = true;
  }


  /**
   * Returns whether we have been initialized
   *
   * @author sweber <sw@boxdrop.com>
   * @return boolean
   */
  public function isInitalized() {

    return $this->is_initialized;
  }


  /**
   * returns encrypted data
   *
   * @author sweber <sw@boxdrop.com>
   * @return string
   */
  public function getEncrypted() {

    return $this->encrypted;
  }
}

?>