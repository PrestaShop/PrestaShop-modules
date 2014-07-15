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
	* Class for the <utilisateur> elements
	* 
	* @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
*/
class SceauCustomer extends SceauXMLElement
{
	const TYPE_ENTREPRISE = 1;
	const TYPE_PARTICULIER = 2;

	public function __construct()
	{
		parent::__construct('utilisateur');
	}

/**
	 * creates an object SceauXMLElement reprensenting <siteconso>, adds it to the current object and sets its children, then returns it
	 * 
	 * @param float $ca amount paid by the customer since his first order
	 * @param int $nb number of order the customer passed since the first one
	 * @param string $datepremcmd date of the very first order passed by the customer, current order not included. Format has to be Y-m-d H:i:s
	 * @param string $datederncmd date of the last order passed by the customer, current order not included. Format has to be Y-m-d H:i:s
	 * @return SceauXMLElement
 */
	public function createSiteconso($ca = null, $nb = null, $datepremcmd = null, $datederncmd = null)
	{
		$siteconso = $this->createChild('siteconso');
		if (!is_null($ca))
			$siteconso->createChild('ca', $ca);
		if (!is_null($nb))
			$siteconso->createChild('nb', $nb);
		if (!is_null($datepremcmd))
			$siteconso->createChild('datepremcmd', $datepremcmd);
		if (!is_null($datederncmd))
			$siteconso->createChild('datederncmd', $datederncmd);
		return $siteconso;
	}
}