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
 * Description of Transport
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class CertissimTransport extends CertissimXMLElement
{

	const RAPIDITE_STANDARD = 2;
	const RAPIDITE_EXPRESS = 1;
	const TYPE_RETRAIT_MARCHAND = 1;
	const TYPE_POINT_RELAIS = 2;
	const TYPE_RETRAIT_AGENCE = 3;
	const TYPE_TRANSPORTEUR = 4;
	const TYPE_TELECHARGEMENT = 5;

	public function __construct($type = '', $nom = '', $rapidite = '', $pointrelais = array())
	{
		parent::__construct();

		$this->childType($type);
		$this->childNom($nom);
		$this->childRapidite($rapidite);

		$this->childPointrelais($pointrelais);
	}

}