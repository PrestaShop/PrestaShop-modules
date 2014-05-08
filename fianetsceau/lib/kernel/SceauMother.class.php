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
	* Mother class, providing getters and setters *
	* 
	* @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
	* 
	* @method string getAttributename() returns the value of the attribute named Attributename
	* Usage :
	* <code>
	* $user->setFirstname('Jonny'); //creates an attribute 'firstname' in the object, and sets it to 'Jonny'
	* echo $user->getFirstname(); //displays the value of the attribute 'firstname' : Jonny
	* </code>
*/

abstract class SceauMother
{
	/**
	 * returns the value of the attribute $name
	 * 
	 * @param string $name
	 * @return mixed 
	 */
	public function __get($name)
	{
		return $this->$name;
	}

	/**
	 * sets the attribute value
	 * 
	 * @param string $name name of the attribute to set
	 * @param mixed $value value to set
	 */
	public function __set($name, $value)
	{
		$this->$name = $value;
	}

	public function __call($name, array $params)
	{
		if (preg_match('#^get(.+)$#', $name, $out))
			return $this->__get(Tools::strtolower($out[1]));

		if (preg_match('#^set(.+)$#', $name, $out))
			return $this->__set(Tools::strtolower($out[1]), $params[0]);
	}

}