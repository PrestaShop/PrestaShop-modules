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

class CodePostal
{
	private $_postal;
	
	public	function __construct($code)
	{
		$this->_postal = $code;
	}
	
	public function getCity($city)
	{
		if (strpos($this->_postal, '75') === 0 && (int)$this->_postal > 75000 && strpos($city, 'PARIS') === 0)
			return ('PARIS '.substr($this->_postal, -2));
		else if (strpos($this->_postal, '13') === 0 && (int)$this->_postal > 13000 && strpos($city, 'MARSEILLE') === 0)
			return ('MARSEILLE '.substr($this->_postal, -2));
		else if (strpos($this->_postal, '69') === 0 && (int)$this->_postal > 69000 && strpos($city, 'LYON') === 0)	
			return ('LYON '.substr($this->_postal, -2));
		else
			return $city;
	}
}
