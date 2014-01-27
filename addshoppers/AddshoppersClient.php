<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class AddshoppersClient
{
  const WIDGET_STOCK_IN_STOCK = 'InStock';
  const WIDGET_STOCK_OUT_OF_STOCK = 'OutOfStock';

  const REG_LOGIN_EXISTS = -1;
  const REG_ACCOUNT_NOT_CREATED = 0;
  const REG_ACCOUNT_CREATED = 1;
  const REG_PASSWORD_TOO_SHORT = 2;
  const REG_PASSWORD_CONSECUTIVE_CHARS = 8;
  const REG_PASSWORD_COMMON = 9;
  const REG_PARAM_MISSING = 10;
  const REG_DOMAIN_BANNED = 17;
  const REG_CATEGORY_INVALID = 19;

  const LOGIN_ACCOUNT_CREATED = 1;
  const LOGIN_MISSING_PARAMETER = 10;
  const LOGIN_WRONG_CREDENTIALS = 11;
  const LOGIN_SITE_EXISTS = 15;

  /**
   * @var string Platform name
   */
  public $platform;

  /**
   * @var array Login messages mapped from response code
   */
  public $loginMessages = array(self::LOGIN_ACCOUNT_CREATED => 'Account authenticated successfuly', self::LOGIN_MISSING_PARAMETER => 'Please fill in all the fields',
													self::LOGIN_WRONG_CREDENTIALS => 'Wrong credentials', self::LOGIN_SITE_EXISTS => 'Site is already registered');

  /**
   * @var array Registration messages mapped from response code
   */
  public $registrationMessages = array(self::REG_LOGIN_EXISTS => 'Login already exists', self::REG_ACCOUNT_NOT_CREATED => 'Account was not created due to unknown error',
																 self::REG_ACCOUNT_CREATED => 'Account was successfuly created!', self::REG_PASSWORD_TOO_SHORT => 'Password is too short',
																 self::REG_PASSWORD_CONSECUTIVE_CHARS => 'Password must consist of different characters', self::REG_PASSWORD_COMMON => 'Password is too weak',
																 self::REG_PARAM_MISSING => 'Request was invalid', self::REG_DOMAIN_BANNED => 'Your domain is banned');

  protected $endpoint = 'http://api.addshoppers.com/1.0';
  protected $defaultShopId = '500975935b3a42793000002b';

  /**
   * Constructs AddShoppers API Client with specified
   * platform identification string.
   *
   * @param string $platform Platform identification string (default: unknown)
   */
  public function __construct($platform = 'unknown')
  {
    $this->platform = $platform;
  }

  /**
   * Gets General Purpose Shop ID for AddShoppers tracking utility
   * @return string Shop ID
   */
  public function getDefaultShopId()
  {
    return $this->defaultShopId;
  }

  /**
   * Send AddShoppers account registration request
   *
   * @param array $parameters Array of string parameters
   * @return array JSON response decoded into associative array
   */
  public function sendRegistrationRequest($parameters)
  {
    return $this->sendCurlRequest('/registration', array('email' => $parameters['addshoppers_email'], 'password' => $parameters['addshoppers_password'],
				'url' => Configuration::get('PS_SHOP_DOMAIN'), 'category' => $parameters['addshoppers_category'], 'phone' => $parameters['addshoppers_phone'],
				'platform' => $this->platform));
  }

  /**
   * Send AddShoppers login request
   *
   * @param array $parameters Array of string parameters
   * @return array JSON response decoded into associative array
   */
  public function sendLoginRequest($parameters)
  {
    return $this->sendCurlRequest('/login', array('login' => $parameters['addshoppers_email'], 'password' => $parameters['addshoppers_password'],
				'url' => Configuration::get('PS_SHOP_DOMAIN'), 'category' => 'Other', 'site_name' => Configuration::get('PS_SHOP_NAME'), 'platform' => $this->platform));
  }

  /**
   * Returns social buttons code.
   *
   * @return array associative array
   */
  public function getButtonsCode()
  {
    return array(
      'buttons' => array(
        'button2' => '<div class="share-buttons share-buttons-panel"'
          .' data-style="medium" data-counter="true" data-oauth="true"'
          .' data-hover="true" data-buttons="twitter,facebook,pinterest"></div>',
        'button1' => '<div class="share-buttons-multi">'
          .'<div class="share-buttons share-buttons-fb-like" data-style="button_count"></div>'
          .'<div class="share-buttons share-buttons-tweet" data-style="horizontal"></div>'
          .'<div class="share-buttons share-buttons-gplus" data-style="medium_bubble"></div>'
          .'<div class="share-buttons share-buttons-panel" data-style="wide_h" data-counter="true" '
          .'data-oauth="true" data-hover="true"></div></div>',
        'open-graph' => '<div class="share-buttons-multi">'
          .'<div class="share-buttons share-buttons-fb-like" data-style="standard"></div>'
          .'<div class="share-buttons share-buttons-og" data-action="want" data-counter="false"></div>'
          .'<div class="share-buttons share-buttons-og" data-action="own" data-counter="false"></div></div>',
      ),
     );
  }

  protected function sendCurlRequest($path, $data)
  {
    $curl = curl_init($this->endpoint . $path);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($curl);
    curl_close($curl);

    return Tools::jsonDecode($result, true);
  }
}
