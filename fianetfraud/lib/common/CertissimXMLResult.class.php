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
 * Classe abstraite pour les retours de script avec fonction magique retournant la valeur des attributs
 *
 * @version 3.1
 * @author ESPIAU Nicolas
 */
class CertissimXMLResult extends CertissimXMLElement
{

	public function __call($name, array $params)
	{
		//fonction returnItem : retourne la valeur de l'attribute Item si existant, null sinon
		if (preg_match('#^return.+$#', $name))
		{
			$elementname = Tools::strtolower(preg_replace('#^return(.+)$#', '$1', $name));

			return array_key_exists($elementname, $this->getAttributes()) ? $this->getAttribute($elementname) : null;
		}

		return parent::__call($name, $params);
	}

}