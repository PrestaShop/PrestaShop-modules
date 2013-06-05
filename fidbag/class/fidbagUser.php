<?php
/*
* 2007-2013 PrestaShop 
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
*  @copyright  2007-20131 PrestaShop SA
*  @version  Release: $Revision: 9844 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class FidbagUser
{
	private $_idUser;
	private $_idCustomer;
	private $_login;
	private $_password;
	private $_idCart;
	private $_cardNumber;
	private $_payed;

	public function __construct($id_customer)
	{
		$this->_idCustomer = $id_customer;
	}

	public function getFidBagUser()
	{
		//return the user by id_customer
		if ($query = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'fidbag_user` WHERE `id_customer` = "'.(int)$this->_idCustomer.'"'))
		{
			$this->_password = $query['password'];
			$this->_login = $query['login'];
			$this->_idCart = $query['id_cart'];
			$this->_payed = $query['payed'];
			$this->_cardNumber = $query['card_number'];
			return $this;
		}
		return false;
	}

	public function createFidBagUser($login = null, $password = null)
	{
		$this->_password = pSQL($password);
		$this->_login = pSQL($login);
		Db::getInstance()->autoExecute(''._DB_PREFIX_.'fidbag_user', array('id_customer' => (int)$this->_idCustomer, 'login' => $this->_login, 'password' => $this->_password), "INSERT");
	}

	public function setLoginPassword($login, $password = null)
	{
		$this->_password = pSQL($password);
		$this->_login = pSQL($login);
		Db::getInstance()->autoExecute(''._DB_PREFIX_.'fidbag_user', array('login' => $this->_login, 'password' => $this->_password), "UPDATE", 'id_customer = '.(int)$this->_idCustomer.'');
	}

	public function setIdCart($id_cart)
	{
		$this->_idCart = $id_cart;
		Db::getInstance()->autoExecute(''._DB_PREFIX_.'fidbag_user', array('id_cart' => (int)$id_cart), 'UPDATE','id_customer = '.(int)$this->_idCustomer.'');
	}

	public function setCartNumber($cartNumber)
	{
		Db::getInstance()->autoExecute(''._DB_PREFIX_.'fidbag_user', array('card_number' => pSQl($cartNumber)), 'UPDATE', 'id_customer = '.(int)$this->_idCustomer.'');
		$this->_cardNumber = $cartNumber;
	}

	public function setPayed($bool)
	{
		$this->_payed = $bool;
		Db::getInstance()->autoExecute(''._DB_PREFIX_.'fidbag_user', array('payed' => (int)$bool), 'UPDATE', 'id_customer = '.(int)$this->_idCustomer.'');
	}

	public function getLogin()
	{
		return $this->_login;
	}

	public function getPassword()
	{
		return $this->_password;
	}

	public function getIdCart()
	{
		return $this->_idCart;
	}

	public function getCardNumber()
	{
		return $this->_cardNumber;
	}

	public function getPayed()
	{
		return $this->_payed;
	}
}

?>
