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
 * Class for the <produits> elements
 * 
 * @author CYRILLE Yann <yann.cyrille at fia-net.com>
 */
class SceauOrderProducts extends SceauXMLElement
{

	public function __construct()
	{
		parent::__construct('produits');
	}

	/**
	 * creates a SceauProduct object representing element <produits>, adds it to the current element, adds sub-children, then returns it
	 * 
	 * @param string $codeean ean code product
	 * @param string $id id product
	 * @param int $categorie FIA-NET category id
	 * @param string $libelle product name
	 * @param float $montant product amount
	 * @param string $image product url image
	 * @return SceauProduct
	 */
	public function createProduct($codeean, $id, $categorie, $libelle, $montant, $image)
	{
		$product = $this->addChild(new SceauProduct());
		if (!is_null($codeean))
			$product->createChild('codeean', $codeean);
		$product->createChild('id', $id);
		$product->createChild('categorie', $categorie);
		$product->createChild('libelle', $libelle);
		$product->createChild('montant', $montant);
		if (!is_null($image))
			$product->createChild('image', $image);

		return $product;
	}
	/**
	 * create <urlwebservice>
	 * 
	 * @param string $url
	 */
	public function createUrlwebservice($url)
	{
		return $this->createChild('urlwebservice', $url);
	}
}