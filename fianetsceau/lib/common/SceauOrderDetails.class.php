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
	* Class for the <infocommande> elements
	* 
	* @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
*/
class SceauOrderDetails extends SceauXMLElement
{
	public function __construct()
	{
		parent::__construct('infocommande');
	}

/**
	* creates a SceauCarrier object representing element <transport>, adds it to the current element, adds sub-children, then returns it
	* 
	* @param string $name carrier name
	* @param string $type carrier type (1|2|3|4|5)
	* @param type $speed carrier speed (1 means express, 2 means standard)
	* @return SceauCarrier
*/
	public function createCarrier($name, $type, $speed)
	{
		$carrier = $this->addChild(new SceauCarrier());
		$carrier->createChild('nom', $name);
		$carrier->createChild('type', $type);
		$carrier->createChild('rapidite', $speed);

		return $carrier;
	}

/**
	 * creates a SceauProductList object representing element <list>, adds it to the current element, then returns it
	 * 
	 * @return SceauProductList
 */
	public function createProductList()
	{
		$product_list = $this->addChild(new SceauProductList());
		return $product_list;
	}

}