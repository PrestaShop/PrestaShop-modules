<?php
/**
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class MobileDetectKwixo
{

	protected $accept;
	protected $user_agent;
	protected $is_mobile = false;
	protected $is_android = null;
	protected $is_androidtablet = null;
	protected $is_iphone = null;
	protected $is_ipad = null;
	protected $is_blackberry = null;
	protected $is_blackberrytablet = null;
	protected $is_opera = null;
	protected $is_palm = null;
	protected $is_windows = null;
	protected $is_windowsphone = null;
	protected $is_generic = null;
	protected $devices = array(
		'android' => 'android.*mobile',
		'androidtablet' => 'android(?!.*mobile)',
		'blackberry' => 'blackberry',
		'blackberrytablet' => 'rim tablet os',
		'iphone' => '(iphone|ipod)',
		'ipad' => '(ipad)',
		'palm' => '(avantgo|blazer|elaine|hiptop|palm|plucker|xiino)',
		'windows' => 'windows ce; (iemobile|ppc|smartphone)',
		'windowsphone' => 'windows phone os',
		'generic' => '(kindle|mobile|mmp|midp|pocket|psp|symbian|smartphone|treo|up.browser|up.link|vodafone|wap|opera mini)'
	);

	public function __construct()
	{
		$this->user_agent = $_SERVER['HTTP_USER_AGENT'];
		$this->accept = $_SERVER['HTTP_ACCEPT'];

		if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE']))
			$this->is_mobile = true;
		elseif (strpos($this->accept, 'text/vnd.wap.wml') > 0 || strpos($this->accept, 'application/vnd.wap.xhtml+xml') > 0)
			$this->is_mobile = true;
		else
		{
			foreach ($this->devices as $device => $regexp)
			{
				$regexp;
				if ($this->isDevice($device))
					$this->is_mobile = true;
			}
		}
	}

	/**
	 * is_android() | is_androidtablet() | is_iphone() | is_ipad() | is_blackberry() | is_blackberrytablet()
	 *  | is_palm() | is_windowsphone() | is_windows() | is_generic() par isDevice()
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return bool
	 */
	public function __call($name, $arguments)
	{
		if ($arguments == null)
			unset($arguments);
		$device = Tools::substr($name, 2);
		if ($name == 'is_'.$device && array_key_exists(Tools::strtolower($device), $this->devices))
			return $this->isDevice($device);
		else
			trigger_error("Methode $name inconnue", E_USER_WARNING);
	}

	/**
	 * Retourne true si c'est un mobile, peu importe le type, faux sinon
	 * @return bool
	 */
	public function isMobile()
	{
		return $this->is_mobile;
	}

	protected function isDevice($device)
	{
		$var = 'is_'.$device;
		$return = $this->$var === null ? (bool)preg_match('/'.$this->devices[Tools::strtolower($device)].'/i', $this->user_agent) : $this->$var;
		if ($device != 'generic' && $return == true)
			$this->is_generic = false;

		return $return;
	}

}
