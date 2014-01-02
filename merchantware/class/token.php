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

include_once(dirname(__FILE__).'/../../../config/config.inc.php');

class TokenTransaction
{
	private $_idCart;

	public function __construct($idCart)
	{
		$this->_idCart = $idCart;
	}

	public function getToken()
	{
		return Db::getInstance()->getValue('SELECT `token` FROM `'._DB_PREFIX_.'merchant_ware_token` WHERE `id_cart` = '.(int)$this->_idCart);
	}

	public function getStatus()
	{
		return Db::getInstance()->getValue('SELECT `status` FROM `'._DB_PREFIX_.'merchant_ware_token` WHERE `id_cart` = '.(int)$this->_idCart);
	}

	public function setToken($token)
	{
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'merchant_ware_token` (`id_cart`, `token`) VALUES ('.(int)$this->_idCart.', \''.pSQL($token).'\')');
		return $this;
	}

	public function setStatus($status)
	{
		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'merchant_ware_token` SET `status` = "'.pSQL($status).'" WHERE `id_cart` = '.(int)$this->_idCart);
		return $this;
	}
}
