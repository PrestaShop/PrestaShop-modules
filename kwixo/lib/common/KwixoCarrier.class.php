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
 * Class for the <transport> elements
 * 
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class KwixoCarrier extends KwixoXMLElement
{
	const SPEED_STANDARD = 2;
	const SPEED_EXPRESS = 1;
	const TYPE_WITHDRAWAL_AT_MERCHANT = 1;
	const TYPE_DROP_OFF_POINT = 2;
	const TYPE_WITHDRAWAL_AT_AGENCY = 3;
	const TYPE_CARRIER = 4;
	const TYPE_DOWNLOAD = 5;

	public function __construct()
	{
		parent::__construct('transport');
	}

	/**
	 * creates an object KwixoDropOffPoint representing the element <pointrelais>, adds it to the current object, then returns it
	 * 
	 * @param string $name
	 * @param string $id
	 * @param KwixoXMLElement $address
	 * @return KwixoDropOffPoint
	 */
	public function createDropOffPoint($name = null, $id = null, KwixoXMLElement $address = null)
	{
		$drop_off_point = $this->addChild(new KwixoDropOffPoint());
		if (!is_null($name))
			$drop_off_point->createChild('enseigne', $name);
		if (!is_null($id))
			$drop_off_point->createChild('identifiant', $id);
		if (!is_null($address))
			$drop_off_point->addChild($address);

		return $drop_off_point;
	}
}