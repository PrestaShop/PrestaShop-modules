<?php

class EbaySynchronizer
{
	public static function syncProducts($products, $context, $id_lang) 
	{
		$count = 0;
		$count_success = 0;
		$count_error = 0;
		$date = date('Y-m-d H:i:s');
		$ebay = new EbayRequest();
		$category_cache = array();

		// Get errors back
		if (file_exists(dirname(__FILE__).'/../log/syncError.php')) 
			include(dirname(__FILE__).'/../log/syncError.php');
		else
			$tab_error = array();				

		// Up the time limit
		@set_time_limit(3600);
		
		$sync_blacklisted_product_ids = EbaySyncBlacklistProduct::getBlacklistedProductIds();

		// Run the products list
		foreach ($products as $p) 
		{
			// Product instanciation
			$product = new Product((int)$p['id_product'], true, $id_lang);
			
			if (version_compare(_PS_VERSION_, '1.5', '<')) 
			{
				$product_for_quantity = new Product((int)$p['id_product']);
				$quantity_product = $product_for_quantity->quantity;
			}
			else
				$quantity_product = $product->quantity;

			if (Validate::isLoadedObject($product))
			{
				if (!$product->active || in_array($product->id, $sync_blacklisted_product_ids))
					$ebay = EbaySynchronizer::endProductOnEbay($ebay, $product);					
				elseif ($product->id_category_default) // if product exists in the db and has a default category
				{
					$category_cache = EbaySynchronizer::_updateCategoryCache($category_cache, $product->id_category_default);

					// Load Pictures
					$pictures = array();
					$picturesMedium = array();
					$picturesLarge = array();
					foreach ($product->getImages($id_lang) as $image) 
					{
						$large_pict = EbaySynchronizer::_getPictureLink($product->id, $image['id_image'], $context->link, 'large');
						$pictures[] = $large_pict;					
						$picturesMedium[] = EbaySynchronizer::_getPictureLink($product->id, $image['id_image'], $context->link, 'medium');
						$picturesLarge[] = $large_pict;
					}
				
					// Load Variations
					list($variations, $variationsList) = EbaySynchronizer::_loadVariations($product, $context, $category_cache);

					// Load basic price
					$price = Product::getPriceStatic((int)$product->id, true);
					$price_original = $price;
					if (preg_match('#[-]{0,1}[0-9]{1,2}%$#is', $category_cache[$product->id_category_default]['percent'])) 
						$price *= (1 + ($category_cache[$product->id_category_default]['percent'] / 100));
					else 
						$price += $category_cache[$product->id_category_default]['percent'];
					$price = round($price, 2);

					// Generate array and try insert in database
					$data = array(
							'id_product' 				=> $product->id,
							'reference' 				=> $product->reference,
							'name' 							=> str_replace('&', '&amp;', $product->name),
							'brand' 						=> $product->manufacturer_name,
							'description' 			=> $product->description,
							'description_short' => $product->description_short,
							'price' 						=> $price,
							'quantity' 					=> $quantity_product,
							'categoryId' 				=> $category_cache[$product->id_category_default]['id_category_ref'],
							'variationsList' 		=> $variationsList,
							'variations' 				=> $variations,
							'pictures' 					=> $pictures,
							'picturesMedium' 		=> $picturesMedium,
							'picturesLarge' 		=> $picturesLarge,
							'condition'				  => EbaySynchronizer::_getEbayCondition($product),
							'shipping'					=> EbaySynchronizer::_getShippingDetailsForProduct($product),
					);

					// Fix hook update product
					if (isset($context->employee) 
						&& (int)$context->employee->id 
						&& Tools::getValue('submitProductAttribute') 
						&& Tools::getValue('id_product_attribute') 
						&& Tools::getValue('attribute_mvt_quantity')
						&& Tools::getValue('id_mvt_reason')) 
					{
						$id_product_attribute_fix = (int)Tools::getValue('id_product_attribute');
						$key = $product->id.'-'.$id_product_attribute_fix;

						if (substr(_PS_VERSION_, 0, 3) == '1.3') 
						{
							$quantity_fix = (int)Tools::getValue('attribute_quantity');
							if ($id_product_attribute_fix > 0 && $quantity_fix > 0 && isset($data['variations'][$key]['quantity']))
								$data['variations'][$key]['quantity'] = (int)$quantity_fix;
						}
						else 
						{
							$action = Db::getInstance()->getValue('SELECT `sign` 
								FROM `'._DB_PREFIX_.'stock_mvt_reason` 
								WHERE `id_stock_mvt_reason` = '.(int)Tools::getValue('id_mvt_reason'));
							$quantity_fix = (int)Tools::getValue('attribute_mvt_quantity');
							if ($id_product_attribute_fix > 0 
								&& $quantity_fix > 0 
								&& isset($data['variations'][$key]['quantity'])
								&& $action)
									$data['variations'][$key]['quantity'] += (int)$action * (int)$quantity_fix;
						}
					}

					// Price Update
					if (isset($p['noPriceUpdate']))
						$data['noPriceUpdate'] = $p['noPriceUpdate'];

					$category_cache[$product->id_category_default]['percent'] = preg_replace('#%$#is', '', $category_cache[$product->id_category_default]['percent']);

					// Save percent and price discount
					if ($category_cache[$product->id_category_default]['percent'] < 0) 
					{
						$data['price_original'] = round($price_original, 2);
						$data['price_percent'] = round($category_cache[$product->id_category_default]['percent']);
					}

					$data['description'] = EbaySynchronizer::_getEbayDescription($product, $id_lang);
				
					// Export on eBay
					if (count($data['variations'])) 
					{
						// Variations Case
						if ($category_cache[$product->id_category_default]['is_multi_sku'] == 1) 
						{
							// Load eBay Description
							$data['description'] = EbaySynchronizer::_fillDescription($data['description'], $data['picturesMedium'], $data['picturesLarge'], '', '');

							// Multi Sku case
							if ($item_id = EbayProduct::getIdProductRefByIdProduct($product->id)) //if product exists on eBay
							{
								// Update
								$data['itemID'] = $item_id;
								if ($ebay->reviseFixedPriceItemMultiSku($data))
									EbayProduct::updateByIdProductRef($item_id, array('date_upd' => pSQL($date)));

								// if product not on eBay we add it
								if ($ebay->errorCode == 291) 
								{
									// We delete from DB and Add it on eBay
									EbayProduct::deleteByIdProductRef($data['itemID']);
									$ebay->addFixedPriceItemMultiSku($data);
									if ($ebay->itemID > 0)
										EbaySynchronizer::_insertEbayProduct($product->id, $ebay->itemID, $date);
								}
							}
							else 
							{
								// Add
								$ebay->addFixedPriceItemMultiSku($data);
								if ($ebay->itemID > 0)
										EbaySynchronizer::_insertEbayProduct($product->id, $ebay->itemID, $date);
							}
						}
						else 
						{
							// No Multi Sku case
							foreach ($data['variations'] as $variation) 
							{
								$data_variation = EbaySynchronizer::_getVariationData($data, $variation);

								// Check if product exists on eBay
								if ($itemID = EbayProduct::getIdProductRefByIdProduct($product->id, $data_variation['id_attribute'])) 
								{
									$data_variation['itemID'] = $itemID;

									// Delete or Update
									if ($data_variation['quantity'] < 1) 
											if ($ebay->endFixedPriceItem($data_variation['itemID'], $data_variation['id_product'])) // Delete
												EbayProduct::deleteByIdProductRef($data_variation['itemID']);
									else 
									{
										// Update
										if ($ebay->reviseFixedPriceItem($data_variation))
											EbayProduct::updateByIdProductRef($itemID, array('date_upd' => pSQL($date)));

										// if product not on eBay we add it
										if ($ebay->errorCode == 291) 
										{
											// We delete from DB and Add it on eBay
											EbayProduct::deleteByIdProductRef($data_variation['itemID']);
											$ebay->addFixedPriceItem($data_variation);
											if ($ebay->itemID > 0)
												EbaySynchronizer::_insertEbayProduct($product->id, $ebay->itemID, $date, $data_variation['id_attribute']);
										}
									}
								}
								else 
								{
									// Add
									$ebay->addFixedPriceItem($data_variation);
									if ($ebay->itemID > 0)
										EbaySynchronizer::_insertEbayProduct($product->id, $ebay->itemID, $date, $data_variation['id_attribute']);
								}
							}
						}
					}
					else 
					{
						// No variations case
						// Load eBay Description
						$data['description'] = EbaySynchronizer::_fillDescription($data['description'], $data['picturesMedium'], $data['picturesLarge'], Tools::displayPrice($data['price']), isset($data['price_original']) ? 'au lieu de <del>'.Tools::displayPrice($data['price_original']).'</del> (remise de '.round($data['price_percent']).')' : '');

						// Check if product exists on eBay
						if ($itemID = EbayProduct::getIdProductRefByIdProduct($product->id)) 
						{
							$data['itemID'] = $itemID;

							// Delete or Update
							if ($data['quantity'] < 1) 
							{
								// Delete
								if ($ebay->endFixedPriceItem($data['itemID'], $data['id_product']))
									EbayProduct::deleteByIdProductRef($data['itemID']);
							}
							else 
							{
								// Update
								if ($ebay->reviseFixedPriceItem($data))
									EbayProduct::updateByIdProductRef($itemID, array('date_upd' => pSQL($date)));

								// if product not on eBay we add it
								if ($ebay->errorCode == 291) 
								{
									// We delete from DB and Add it on eBay
									EbayProduct::deleteByIdProductRef($data['itemID']);
									$ebay->addFixedPriceItem($data);
									if ($ebay->itemID > 0)
										EbaySynchronizer::_insertEbayProduct($product->id, $ebay->itemID, $date);
								}
							}
						}
						else 
						{
							// Add
							$ebay->addFixedPriceItem($data);
							if ($ebay->itemID > 0)
								EbaySynchronizer::_insertEbayProduct($product->id, $ebay->itemID, $date);
						}
					}

					// Check error
					if (!empty($ebay->error)) 
					{
						$error_key = md5($ebay->error);
						$tab_error[$error_key]['msg'] = $ebay->error;
					
						if (!isset($tab_error[$error_key]['products']))
								$tab_error[$error_key]['products'] = array();
					
						if (count($tab_error[$error_key]['products']) < 10)
								$tab_error[$error_key]['products'][] = $data['name'];
					
						if (count($tab_error[$error_key]['products']) == 10)
								$tab_error[$error_key]['products'][] = '...';
					
						$count_error++;
					}
					else
						$count_success++;                    
					$count++;
				}
			}
		}

		if ($count_error)
			file_put_contents(dirname(__FILE__).'/../log/syncError.php', '<?php $tab_error = '.var_export($tab_error, true).'; '.($ebay->itemConditionError ? '$itemConditionError = true; ' : '$itemConditionError = false;').' ?>');
	}
	
	private static function _updateCategoryCache($category_cache, $id_category)
	{
		if (!isset($category_cache[$id_category]))
			$category_cache[$id_category] = EbayCategory::getEbayCategoryByCategoryId($id_category);
					
		if ($category_cache[$id_category]['is_multi_sku'] != 1)
			$category_cache[$id_category]['is_multi_sku'] = EbaySynchronizer::findIfCategoryParentIsMultiSku($category_cache[$id_category]['id_category_ref']);
		
		return $category_cache;
	}
	
	private static function _loadVariations($product, $context, $category_cache)
	{
		$variations = array();
		$variationsList = array();
		$combinations = $product->getAttributeCombinations($context->cookie->id_lang);
		if (isset($combinations))
			foreach ($combinations as $combinaison) 
			{
				$price = Product::getPriceStatic((int)$combinaison['id_product'], true, (int)$combinaison['id_product_attribute']);						

				$variationsList[$combinaison['group_name']][$combinaison['attribute_name']] = 1;
				
				$variation_key = $combinaison['id_product'].'-'.$combinaison['id_product_attribute'];
				$variations[$variation_key] = array(
					'id_attribute' => $combinaison['id_product_attribute'],
					'reference' 	 => $combinaison['reference'],
					'quantity' 		 => $combinaison['quantity'],
					'price_static' => $price
				);
				$variations[$variation_key]['variations'][] = array(
					'name'  => $combinaison['group_name'], 
					'value' => $combinaison['attribute_name']);
				
				$price_original = $price;						
				if (preg_match('#[-]{0,1}[0-9]{1,2}%$#is', $category_cache[$product->id_category_default]['percent']))
					$price *= (1 + ($category_cache[$product->id_category_default]['percent'] / 100));
				else 
					$price += $category_cache[$product->id_category_default]['percent'];
				$variations[$variation_key]['price'] = round($price, 2);
				
				if ($category_cache[$product->id_category_default]['percent'] < 0) 
				{
					$variations[$variation_key]['price_original'] = round($price_original, 2);
					$variations[$variation_key]['price_percent'] = round($category_cache[$product->id_category_default]['percent']);
				}
			}

		// Load Variations Pictures
		$combination_images = $product->getCombinationImages(2);
		if (!empty($combination_images))
			foreach ($combination_images as $combination_image)
				foreach ($combination_image as $image)
					$variations[$product->id.'-'.$image['id_product_attribute']]['pictures'][] = EbaySynchronizer::_getPictureLink($product->id, $image['id_image'], $context->link, 'large'); // if issue, it's because of https/http in the url		
		
		return array($variations, $variationsList);
	}
	
	private static function _getEbayDescription($product, $id_lang)
	{
		$features_html = '';
		foreach ($product->getFrontFeatures((int)$id_lang) as $feature)
			$features_html .= '<b>'.$feature['name'].'</b> : '.$feature['value'].'<br/>';
	
		return str_replace(
					array(
						'{DESCRIPTION_SHORT}',
						'{DESCRIPTION}',
						'{FEATURES}',
						'{EBAY_IDENTIFIER}',
						'{EBAY_SHOP}',
						'{SLOGAN}',
						'{PRODUCT_NAME}'),
					array(
						$product->description_short,
						$product->description,
						$features_html,
						Configuration::get('EBAY_IDENTIFIER'),
						Configuration::get('EBAY_SHOP'),
						'', 
						$product->name),
						Configuration::get('EBAY_PRODUCT_TEMPLATE')
		);
	}
	
	public static function endProductOnEbay($ebay, Product $product)
	{
		if(($ebay_item_id = EbayProduct::getIdProductRefByIdProduct($product->id))
			&& $ebay->endFixedPriceItem($ebay_item_id))
			EbayProduct::deleteByIdProductRef($ebay_item_id);

		return $ebay;
	}
	
	private static function _fillDescription($description, $medium_pictures, $large_pictures, $product_price = '', $product_price_discount = '')
	{
		return str_replace(
				array('{MAIN_IMAGE}', '{MEDIUM_IMAGE_1}', '{MEDIUM_IMAGE_2}', '{MEDIUM_IMAGE_3}', '{PRODUCT_PRICE}', '{PRODUCT_PRICE_DISCOUNT}'), array(
			(isset($data['picturesLarge'][0]) ? '<img src="'.$data['picturesLarge'][0].'" class="bodyMainImageProductPrestashop" />' : ''),
			(isset($data['picturesMedium'][1]) ? '<img src="'.$data['picturesMedium'][1].'" class="bodyFirstMediumImageProductPrestashop" />' : ''),
			(isset($data['picturesMedium'][2]) ? '<img src="'.$data['picturesMedium'][2].'" class="bodyMediumImageProductPrestashop" />' : ''),
			(isset($data['picturesMedium'][3]) ? '<img src="'.$data['picturesMedium'][3].'" class="bodyMediumImageProductPrestashop" />' : ''),
			$product_price,
			$product_price_discount
				), $description
		);		
	}
	
	private static function _insertEbayProduct($id_product, $ebay_item_id, $date, $id_attribute = 0)
	{
		EbayProduct::insert(array(
			'id_country' => 8, 
			'id_product' => (int)$id_product, 
			'id_attribute' => (int)$id_attribute, 
			'id_product_ref' => pSQL($ebay_item_id), 
			'date_add' => pSQL($date), 
			'date_upd' => pSQL($date)));	
	}
	
	private static function _getVariationData($data, $variation)
	{
		if (!empty($variation['pictures']))
				$data['pictures'] = $variation['pictures'];
		if (!empty($variation['picturesMedium']))
				$data['picturesMedium'] = $variation['picturesMedium'];
		if (!empty($variation['picturesLarge']))
				$data['picturesLarge'] = $variation['picturesLarge'];
		
		foreach ($variation['variations'] as $variation_label) 
		{
			$data['name'] .= ' '.$variation_label['value'];
			$data['attributes'][$variation_label['name']] = $variation_label['value'];
		}
		$data['price'] = $variation['price'];
		if (isset($variation['price_original'])) 
		{
			$data['price_original'] = $variation['price_original'];
			$data['price_percent'] = $variation['price_percent'];
		}
		$data['quantity'] = $variation['quantity'];
		$data['id_attribute'] = $variation['id_attribute'];
		unset($data['variations']);
		unset($data['variationsList']);

		// Load eBay Description
		$data['description'] = EbaySynchronizer::_fillDescription(
			$data['description'], 
			$data['picturesMedium'], 
			$data['picturesLarge'], 
			Tools::displayPrice($data['price']), 
			isset($data['price_original']) ? 'au lieu de <del>'.Tools::displayPrice($data['price_original']).'</del> (remise de '.round($data['price_percent']).')' : '');

		$data['id_product'] .= '-'.(int)$data['id_attribute'];
		
		return $data;
	}

	private static function _getShippingDetailsForProduct($product)
	{
		$national_ship = array();
		$international_ship = array();

		//Get National Informations : service, costs, additional costs, priority
		$service_priority = 1; 
		foreach (EbayShipping::getNationalShippings() as $carrier) 
		{
			$national_ship[$carrier['ebay_carrier']] = array(
				'servicePriority' 			 => $service_priority, 
				'serviceAdditionalCosts' => $carrier['extra_fee'], 
				'serviceCosts' 					 => EbaySynchronizer::_getShippingPriceForProduct($product, Configuration::get('EBAY_ZONE_NATIONAL'), $carrier['ps_carrier'])
			);  
			$service_priority++;
		}


		//Get International Informations
		$service_priority = 1; 
		foreach (EbayShipping::getInternationalShippings() as $carrier) 
		{
			$international_ship[$carrier['ebay_carrier']] = array(
				'servicePriority' 			 => $service_priority, 
				'serviceAdditionalCosts' => $carrier['extra_fee'], 
				'serviceCosts' 					 => EbaySynchronizer::_getShippingPriceForProduct($product, Configuration::get('EBAY_ZONE_INTERNATIONAL'), $carrier['ps_carrier']),
				'locationsToShip' 			 => EbayShippingInternationalZone::getIdEbayZonesByIdEbayShipping($carrier['id_ebay_shipping'])
			); 
			$service_priority++;
		}

		return array(
			'excludedZone' 			=> EbayShippingZoneExcluded::getExcluded(),
			'nationalShip' 			=> $national_ship,
			'internationalShip' => $international_ship
		);
	}
	
	private static function _getPictureLink($id_product, $id_image, $context_link, $size)
	{
		//Fix for payment modules validating orders out of context, $link will not  generate fatal error.
		$link = is_object($context_link) ? $context_link : new Link();
		
		$prefix = (substr(_PS_VERSION_, 0, 3) == '1.3' ? Tools::getShopDomain(true).'/' : '');		
		
		return str_replace('https://', 'http://', $prefix.$link->getImageLink('ebay', $id_product.'-'.$id_image, $size.(version_compare(_PS_VERSION_, '1.5.1', '>=') ? '_default' : '')));		
	}

	private static function _getShippingPriceForProduct($product, $zone, $carrier_id)
	{
		$carrier = new Carrier($carrier_id);

		if (Configuration::get('PS_SHIPPING_METHOD') == 1) // Shipping by weight
			$price = $carrier->getDeliveryPriceByWeight($product->weight, $zone);
		else // Shipping by price
			$price = $carrier->getDeliveryPriceByPrice($product->price, $zone);
		
		if ($carrier->shipping_handling) //Add shipping handling fee
			$price += Configuration::get('PS_SHIPPING_HANDLING');

		$price += $price * Tax::getCarrierTaxRate($carrier_id) / 100;

		return $price;
	}
	
	public static function findIfCategoryParentIsMultiSku($id_category_ref) 
	{
		$row = Db::getInstance()->getRow('SELECT `id_category_ref_parent`, `is_multi_sku` 
			FROM `'._DB_PREFIX_.'ebay_category` 
			WHERE `id_category_ref` = '.(int)$id_category_ref);
		if ($row['id_category_ref_parent'] != $id_category_ref)
			return EbaySynchronizer::findIfCategoryParentIsMultiSku($row['id_category_ref_parent']);
		return $row['is_multi_sku'];
	}
	
	public static function getNbSynchronizableProducts()
	{
		if (version_compare(_PS_VERSION_, '1.5', '>')) 
		{
			// Retrieve total nb products for eBay (which have matched categories)
			$nb_products = Db::getInstance()->getValue('
					SELECT COUNT( * ) FROM (
						SELECT COUNT(p.id_product) AS nb
							FROM  `'._DB_PREFIX_.'product` AS p
							INNER JOIN  `'._DB_PREFIX_.'stock_available` AS s ON p.id_product = s.id_product
							WHERE s.`quantity` >0
							AND  `id_category_default` 
							IN (
								SELECT  `id_category` 
								FROM  `'._DB_PREFIX_.'ebay_category_configuration` 
								WHERE  `id_ebay_category` > 0
								AND `id_ebay_category` > 0'.
								(Configuration::get('EBAY_SYNC_MODE') != 'A' ? ' AND `sync` = 1' : ''). 
							')
							'.EbaySynchronizer::_addSqlRestrictionOnLang('s').'
							GROUP BY p.id_product
					)TableReponse');
		} 
		else
		{
			// Retrieve total nb products for eBay (which have matched categories)
			$nb_products = Db::getInstance()->getValue('
					SELECT COUNT(`id_product`)
					FROM `'._DB_PREFIX_.'product`
					WHERE `quantity` > 0 
					AND `id_category_default` IN (
						SELECT `id_category` 
						FROM `'._DB_PREFIX_.'ebay_category_configuration` 
						WHERE `id_category` > 0 
						AND `id_ebay_category` > 0'.
						(Configuration::get('EBAY_SYNC_MODE') != 'A' ? ' AND `sync` = 1' : '').'
					)');
		}
		
		return $nb_products;
	}
	
	public static function getProductsToSynchronize($option)
	{
		if (version_compare(_PS_VERSION_, '1.5', '>')) 
		{
			$sql = '
				SELECT p.id_product
				FROM  `'._DB_PREFIX_.'product` AS p
					INNER JOIN  `'._DB_PREFIX_.'stock_available` AS s ON p.id_product = s.id_product
				WHERE s.`quantity` >0
				AND  `id_category_default` 
					IN (
						SELECT  `id_category` 
						FROM  `'._DB_PREFIX_.'ebay_category_configuration` 
						WHERE  `id_category` > 0
						AND  `id_ebay_category` > 0'.
						(Configuration::get('EBAY_SYNC_MODE') != 'A' ? ' AND `sync` = 1' : '').
					')
				'.($option == 1 ? EbaySynchronizer::_addSqlCheckProductInexistence('p') : '').'
					AND p.`id_product` > '.(int)Configuration::get('EBAY_SYNC_LAST_PRODUCT').'
					'.EbaySynchronizer::_addSqlRestrictionOnLang('s').'
				ORDER BY  p.`id_product` 
				LIMIT 1';
		}
		else
		{
			$sql = '
				SELECT `id_product` 
				FROM `'._DB_PREFIX_.'product`
				WHERE `quantity` > 0 
				AND `id_category_default` IN (
					SELECT `id_category` 
					FROM `'._DB_PREFIX_.'ebay_category_configuration` 
					WHERE `id_category` > 0 
					AND `id_ebay_category` > 0'.
					(Configuration::get('EBAY_SYNC_MODE') != 'A' ? ' AND `sync` = 1' : '').'
				)
				'.($option == 1 ? EbaySynchronizer::_addSqlCheckProductInexistence('p') : '').'
				AND `id_product` > '.(int)Configuration::get('EBAY_SYNC_LAST_PRODUCT').'
				ORDER BY `id_product`
				LIMIT 1';
		}		
		
		return Db::getInstance()->executeS($sql);
	}
	
	public static function getNbProductsLess($option, $ebay_sync_last_product)
	{
		if (version_compare(_PS_VERSION_, '1.5', '>')) 
		{
			$sql = '
				SELECT COUNT(id_supplier) FROM(
					SELECT id_supplier 
						FROM  `'._DB_PREFIX_.'product` AS p
							INNER JOIN  `'._DB_PREFIX_.'stock_available` AS s ON p.id_product = s.id_product
						WHERE s.`quantity` >0
						AND  `active` =1
						AND  `id_category_default` 
						IN (
							SELECT  `id_category` 
							FROM  `'._DB_PREFIX_.'ebay_category_configuration` 
							WHERE  `id_category` >0
							AND  `id_ebay_category` >0'.
							(Configuration::get('EBAY_SYNC_MODE') != 'A' ? ' AND `sync` = 1' : '').								
						')
						'.(Tools::getValue('option') == 1 ? EbaySynchronizer::_addSqlCheckProductInexistence('p') : '').'
						AND p.`id_product` >'.$ebay_sync_last_product.'
						'.EbaySynchronizer::_addSqlRestrictionOnLang('s').'
						GROUP BY p.id_product
				)TableRequete';
		} 
		else 
		{
			$sql = '
				SELECT COUNT(`id_product`) 
				FROM `'._DB_PREFIX_.'product`
				WHERE `quantity` > 0 
				AND `active` = 1
				AND `id_category_default` IN (
					SELECT `id_category` 
					FROM `'._DB_PREFIX_.'ebay_category_configuration` 
					WHERE `id_category` > 0 
					AND `id_ebay_category` > 0'. 
					(Configuration::get('EBAY_SYNC_MODE') != 'A' ? ' AND `sync` = 1' : '').'
				)
				'.(Tools::getValue('option') == 1 ? EbaySynchronizer::_addSqlCheckProductInexistence('p') : '').'
				AND `id_product` > '.$ebay_sync_last_product;
		}
			
		return Db::getInstance()->getValue($sql);
	}
	
	private static function _getEbayCondition($product)
	{
		switch ($product->condition)
		{
			case 'new' :
				return Configuration::get('EBAY_CONDITION_NEW');
			case 'used' : 
				return Configuration::get('EBAY_CONDITION_USED');
			case 'refurbished' : 
				return Configuration::get('EBAY_CONDITION_REFURBISHED');
			default:
				return Configuration::get('EBAY_CONDITION_NEW');
		}
	}
	
	private static function _addSqlRestrictionOnLang($alias) 
	{
		if (version_compare(_PS_VERSION_, '1.5', '>'))
			Shop::addSqlRestrictionOnLang($alias);
	}

	private static function _addSqlCheckProductInexistence($alias = null)
	{
		return 'AND '.($alias ? $alias.'.' : '').'`id_product` NOT IN (
			SELECT `id_product` 
			FROM `'._DB_PREFIX_.'ebay_product`
		)';		
	}
		
}