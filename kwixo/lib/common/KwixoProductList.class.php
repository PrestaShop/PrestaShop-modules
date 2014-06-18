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
 * Class for the <list> elements
 * 
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class KwixoProductList extends KwixoXMLElement
{
	public function __construct()
	{
		parent::__construct('list');
	}

	/**
	 * adds a product into the list and increases the attribute nbproduit of the current object
	 * 
	 * @param KwixoXMLElement $produit
	 * @param array $attrs
	 * @return KwixoXMLElement
	 */
	public function createProduct($label, $ref, $type, $price, $nb)
	{
		$product = $this->createChild('produit', $label, array('ref' => $ref, 'type' => $type, 'prixunit' => $price, 'nb' => $nb));
		$nbproducts = (int)$this->getAttribute('nbproduit');
		$this->setAttribute('nbproduit', $nbproducts + $nb);

		return $product;
	}
}