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

class BlockWishListViewModuleFrontController extends ModuleFrontController
{

	public function __construct()
	{
		parent::__construct();
		$this->context = Context::getContext();
		include_once($this->module->getLocalPath().'WishList.php');
	}

	public function initContent()
	{
		parent::initContent();
		$token = Tools::getValue('token');

		if ($token)
		{
			$wishlist = WishList::getByToken($token);

			WishList::refreshWishList($wishlist['id_wishlist']);
			$products = WishList::getProductByIdCustomer((int)$wishlist['id_wishlist'], (int)$wishlist['id_customer'], $this->context->language->id, null, true);

			$nb_products = count($products);
			for ($i = 0; $i < $nb_products; ++$i)
			{
				$obj = new Product((int)$products[$i]['id_product'], false, $this->context->language->id);
				if (!Validate::isLoadedObject($obj))
					continue;
				else
				{
					$quantity = Product::getQuantity((int)$products[$i]['id_product'], $products[$i]['id_product_attribute']);
					$products[$i]['attribute_quantity'] = $quantity;
					$products[$i]['product_quantity'] = $quantity;
					if ($products[$i]['id_product_attribute'] != 0)
					{
						$combination_imgs = $obj->getCombinationImages($this->context->language->id);
						if (isset($combination_imgs[$products[$i]['id_product_attribute']][0]))
							$products[$i]['cover'] = $obj->id.'-'.$combination_imgs[$products[$i]['id_product_attribute']][0]['id_image'];
						else
						{
							$cover = Product::getCover($obj->id);
							$products[$i]['cover'] = $obj->id.'-'.$cover['id_image'];
						}
					} else
					{
						$images = $obj->getImages($this->context->language->id);
						foreach ($images as $image)
							if ($image['cover'])
							{
								$products[$i]['cover'] = $obj->id.'-'.$image['id_image'];
								break;
							}
					}
					if (!isset($products[$i]['cover']))
						$products[$i]['cover'] = $this->context->language->iso_code.'-default';
				}
				$products[$i]['bought'] = false;
				/*
				for ($j = 0, $k = 0; $j < sizeof($bought); ++$j)
				{
					if ($bought[$j]['id_product'] == $products[$i]['id_product'] AND
						$bought[$j]['id_product_attribute'] == $products[$i]['id_product_attribute']
					)
						$products[$i]['bought'][$k++] = $bought[$j];
				}*/
			}

			WishList::incCounter((int)$wishlist['id_wishlist']);
			$ajax = Configuration::get('PS_BLOCK_CART_AJAX');

			$wishlists = WishList::getByIdCustomer((int)$wishlist['id_customer']);

			foreach ($wishlists as $key => $item)
			{
				if ($item['id_wishlist'] == $wishlist['id_wishlist'])
				{
					unset($wishlists[$key]);
					break;
				}
			}

			$this->context->smarty->assign(
				array(
					'current_wishlist' => $wishlist,
					'token' => $token,
					'ajax' => ((isset($ajax) && (int)$ajax == 1) ? '1' : '0'),
					'wishlists' => $wishlists,
					'products' => $products
				)
			);
		}
		$this->setTemplate('view.tpl');
	}
}