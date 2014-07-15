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

/**
 * Class mother defining getters and setters methods
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
abstract class CertissimMother
{

	/**
	 * returns the name of the attribute $name
	 * 
	 * @param string $name attribute named to get
	 * @return string
	 */
	public function __get($name)
	{
		return $this->$name;
	}

	/**
	 * sets the attribute $name the value $value
	 * 
	 * @param string $name name of the attribute to set
	 * @param mixed $value value of the attribute to set
	 * @return bool
	 */
	public function __set($name, $value)
	{
		$this->$name = $value;
		return true;
	}

	/**
	 * magic methods called when a non existing method is called from an child of the class Mother
	 *
	 * @param string $name name of the method called
	 * @param array $params params given
	 * @return mixed
	 */
	public function __call($name, array $params)
	{
		if (preg_match('#^get(.+)$#', $name, $out))
			return $this->__get(Tools::strtolower($out[1]));
		if (preg_match('#^set(.+)$#', $name, $out))
			return $this->__set(Tools::strtolower($out[1]), $params[0]);
	}

}