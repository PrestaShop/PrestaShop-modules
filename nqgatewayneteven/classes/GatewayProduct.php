<?php
/*
* 2007-2013 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class GatewayProduct extends Gateway
{
	public static $type_sku = 'reference';
	public static $shipping_by_product = false;
	public static $shipping_by_product_fieldname = false;
	public static $customizable_field = array();
	
	// @var array List of Gateway instance
	protected static $instance = array();

	public static function getInstance($client = NULL)
	{
		$wsdl = 0;
		if ($client != NULL)
			$wsdl = 1;
			
		if (!isset(self::$instance[$wsdl]))
			self::$instance[$wsdl] = new GatewayProduct($client);
		return self::$instance[$wsdl];
	}
	
	public function __construct($client = NULL)
	{
		parent::__construct($client);
		self::$shipping_by_product = Gateway::getConfig('SHIPPING_BY_PRODUCT');
		self::$shipping_by_product_fieldname = Gateway::getConfig('SHIPPING_BY_PRODUCT_FIELDNAME');
		
		if (Gateway::getConfig('CUSTOMIZABLE_FIELDS'))
		{
			$customizable_fields = explode('¤', Gateway::getConfig('CUSTOMIZABLE_FIELDS'));
			foreach ($customizable_fields as $customizable_field)
			{
				$customizable_value = explode('|', $customizable_field);
				self::$customizable_field[$customizable_value[0]] = $customizable_value[1];
			}
		}
	}

	/**
	 *
	 * @param bool $is_display
	 */
	public function updateOneProduct($id_product, $id_product_attribute = NULL, $is_display = false)
	{
		if (!self::$send_product_to_neteven)
			return;
		
		$products = $this->getOneProductAcreer(array(), $id_product, $id_product_attribute);
		$products = $this->delProductWithoutEAN($products, $is_display);
		$neteven_products = $this->getPropertiesForNetEven($products, $is_display);

		if (!$is_display)
			$this->addProductInNetEven($neteven_products);
		else
			Tools::p($neteven_products);

	}

	/**
	 *
	 * @param bool $is_display
	 */
	public function updateProduct($is_display = true)
	{
		$indice = 0;
		$products = $this->getAllProductAcreer(array(), $indice);

		if ($this->getValue('debug'))
			Toolbox::displayDebugMessage(self::getL('Quantity of recovered product').' : '.count($products));
		
		$products = $this->delProductWithoutEAN($products, $is_display);

		if ($this->getValue('debug'))
			Toolbox::displayDebugMessage(self::getL('Quantity of recovered product after removing products without EAN code').' : '.count($products));
		
		$security = 0;
		$control_while = true;
		while ($control_while)
		{
			$neteven_products = $this->getPropertiesForNetEven($products, $is_display);
			
			if ($is_display)
				Tools::p($neteven_products);
			
			if (!$is_display)
				$this->addProductInNetEven($neteven_products);

			$indice++;
			$products_base = $this->getAllProductAcreer(array(), $indice);
			
			if ($this->getValue('debug'))
				Toolbox::displayDebugMessage(self::getL('Quantity of recovered product').' : '.count($products_base));

			$products = $this->delProductWithoutEAN($products_base, $is_display);
	
			if ($this->getValue('debug'))
				Toolbox::displayDebugMessage(self::getL('Quantity of recovered product after removing products without EAN code').' : '.count($products));
			
			$security++;
			if ($security > 1000)
				$control_while = false;

			if ((is_array($products_base) && count($products_base) == 0) || !is_array($products_base))
				$control_while = false;

		}
	}

	private function getOneProductAcreer($products_exlusion = array(), $id_product, $id_product_attribute)
	{
		global $cookie;
		
		$separator = $this->getValue('separator');

		$id_lang = isset($cookie->id_lang) ? (int)$cookie->id_lang : (int)Configuration::get('PS_LANG_DEFAULT');
		$sql = '
			SELECT'.(self::$shipping_by_product && !empty(self::$shipping_by_product_fieldname) ? ' p.`'.pSQL(self::$shipping_by_product_fieldname).'`,' : '').'
				pl.`link_rewrite`,
				p.`id_category_default`,
				p.`id_product`,
				pl.`name`,
				pl.`description`,
				p.`id_category_default` as id_category,
				cl.`name` as category_name,
				p.`ean13`,
				pa.`ean13` as ean13_declinaison,
				pa.`id_product_attribute` as id_product_attribute,
				p.`quantity`,
				pa.`quantity` as pa_quantity,
				p.`wholesale_price`,
				m.`name` as name_manufacturer,
				p.`reference` as product_reference,
				pa.`reference` as product_attribute_reference,
				p.`additional_shipping_cost`,
				GROUP_CONCAT(distinct CONCAT(agl.`name`," {##} ",al.`name`) SEPARATOR "'.pSQL($separator).' ") as attribute_name
				
				'.((self::$type_sku != 'reference')?',(SELECT CONCAT(\'D\', pa2.`id_product_attribute`) FROM `'._DB_PREFIX_.'product_attribute` pa2 WHERE pa2.`id_product` = p.`id_product` AND `default_on` = 1 LIMIT 1) as declinaison_default': '').'
				'.((self::$type_sku == 'reference')?',(SELECT pa2.`reference` FROM `'._DB_PREFIX_.'product_attribute` pa2 WHERE pa2.`id_product` = p.`id_product` AND `default_on` = 1 LIMIT 1) as declinaison_default_ref': '').'
			FROM `'._DB_PREFIX_.'product` p
			LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = p.`id_product` AND pl.`id_lang` = '.(int)$id_lang.')
			LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (cl.`id_category` = p.`id_category_default` AND cl.`id_lang` = '.(int)$id_lang.')
			LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.`id_product` = p.`id_product`)
			LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.`id_product_attribute`=pa.`id_product_attribute`)
			LEFT JOIN `'._DB_PREFIX_.'attribute` a ON (a.`id_attribute`=pac.`id_attribute`)
			LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (al.`id_attribute`=a.`id_attribute` AND al.`id_lang`='.(int)$id_lang.')
			LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (agl.`id_attribute_group`=a.`id_attribute_group` AND agl.`id_lang`='.(int)$id_lang.')
			WHERE p.`active` = 1
			AND '.($id_product_attribute == 0 ? 'p.`id_product` = '.(int)$id_product : 'pa.`id_product_attribute` ='.(int)$id_product_attribute).'
			'.((is_array($products_exlusion) && count($products_exlusion) > 0) ? ' AND (p.`reference` NOT IN ('.implode(',', pSQL($products_exlusion)).') AND pa.`reference` NOT IN ('.implode(',', pSQL($products_exlusion)).'))' : '');
		$sql .= '
			GROUP BY p.`id_product`, pa.`id_product_attribute`
			';

		return Db::getInstance()->ExecuteS($sql);
	}
	
	/**
	 * Récupération de tous les produits du presta.
	 * @param array $product_exlusion
	 * @param int $indice
	 * @return mixed
	 */
	private function getAllProductAcreer($products_exlusion = array(), $indice = 0)
	{
		global $cookie;
		
		if ($this->getValue('debug'))
			$neteven_date_export_product = '';
		else
			$neteven_date_export_product = Configuration::get('neteven_date_export_product');
		
		$separator = $this->getValue('separator');

		$id_lang = isset($cookie->id_lang) ? (int)$cookie->id_lang : (int)Configuration::get('PS_LANG_DEFAULT');
		$sql = '
		SELECT
			'.(self::$shipping_by_product && !empty(self::$shipping_by_product_fieldname) ? 'p.`'.pSQL(self::$shipping_by_product_fieldname).'`,' : '').'
			pl.`link_rewrite`,
			p.`id_category_default`,
			p.`id_product`,
		 	pl.`name`,
			pl.`description`,
			p.`id_category_default` as id_category,
			cl.`name` as category_name,
			p.`ean13`,
			pa.`ean13` as ean13_declinaison,
			pa.`id_product_attribute` as id_product_attribute,
			p.`quantity`,
			pa.`quantity` as pa_quantity,
			p.`wholesale_price`,
			m.`name` as name_manufacturer,
			p.`reference` as product_reference,
			pa.`reference` as product_attribute_reference,
			p.`additional_shipping_cost`,
			GROUP_CONCAT(distinct CONCAT(agl.`name`," {##} ",al.`name`) SEPARATOR "'.pSQL($separator).' ") as attribute_name
			'.((self::$type_sku != 'reference')?',(SELECT CONCAT(\'D\', pa2.`id_product_attribute`) FROM `'._DB_PREFIX_.'product_attribute` pa2 WHERE pa2.`id_product` = p.`id_product` AND `default_on` = 1 LIMIT 1) as declinaison_default': '').'
				'.((self::$type_sku == 'reference')?',(SELECT pa2.`reference` FROM `'._DB_PREFIX_.'product_attribute` pa2 WHERE pa2.`id_product` = p.`id_product` AND `default_on` = 1 LIMIT 1) as declinaison_default_ref': '').'
		FROM `'._DB_PREFIX_.'product` p
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (pl.`id_product` = p.`id_product` AND pl.`id_lang` = '.(int)$id_lang.')
		LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (cl.`id_category` = p.`id_category_default` AND cl.`id_lang` = '.(int)$id_lang.')
		LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.`id_product` = p.`id_product`)
		LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute`=pa.`id_product_attribute`
		LEFT JOIN `'._DB_PREFIX_.'attribute` a ON (a.`id_attribute`=pac.`id_attribute`)
		LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (al.`id_attribute`=a.`id_attribute` AND al.`id_lang`='.(int)$id_lang.')
		LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (agl.`id_attribute_group`=a.`id_attribute_group` AND agl.`id_lang`='.(int)$id_lang.')
		WHERE '.(isset($_GET['product_no']) ? ' p.`active` = 0' : ' p.`active` = 1 AND p.`available_for_order` = 1').'
		'.((!empty($neteven_date_export_product)) ? ' AND p.`date_upd` > "'.pSQL($neteven_date_export_product).'"' : '').'
		'.((is_array($products_exlusion) && count($products_exlusion) > 0) ? ' AND (p.`reference` NOT IN ('.implode(',', pSQL($products_exlusion)).') AND pa.`reference` NOT IN ('.implode(',', pSQL($products_exlusion)).'))' : '');
		$sql .= '
		GROUP BY p.`id_product`, pa.`id_product_attribute`
		LIMIT '.($indice*100).', 100
		';

		if ($this->getValue('debug'))
			Toolbox::displayDebugMessage($sql);

		$products = Db::getInstance()->ExecuteS($sql);
	
		Toolbox::addLogLine(self::getL('Product to update or create').' '.count($products));
		Toolbox::writeLog();

		return $products;
	}


	/**
	 * Formatage des informations produit pour NetEven.
	 * @param $t_product
	 * @param bool $display
	 * @return array
	 */
	private function getPropertiesForNetEven($products, $display = false)
	{
		if (!sizeof($products))
			return false;

		global $cookie, $link;
		
		if (!$link)
			$link = new Link();
	
		$products_temp = array();

		$compteur_product_no_ean13 = 0 ;
		$compteur_product_no_ref = 0 ;
		foreach ($products as $product)
		{
			$product_reference = 'P'.$product['id_product'];
			if (!empty($product['id_product_attribute']))
				$product_reference = 'D'.$product['id_product_attribute'];

			if (self::$type_sku == 'reference')
			{
				$product_reference = $product['product_reference'];
				if(!empty($product['id_product_attribute']))
					$product_reference = $product['product_attribute_reference'];
				
			}

			$codeEan = sprintf('%013s', !empty($product['ean13_declinaison']) ? $product['ean13_declinaison'] : $product['ean13']);
			$id_product_attribute = NULL;
			if (!empty($product['id_product_attribute']))
				$id_product_attribute = (int)$product['id_product_attribute'];

			$product_price = Product::getPriceStatic((int)$product['id_product'], true, (int)$id_product_attribute, 2, NULL, false, true);
			$product_price_without_reduction = Product::getPriceStatic((int)$product['id_product'], true, (int)$id_product_attribute, 2, NULL, false, false);
			
			$categories = $this->getProductCategories($product);
			$classification = str_replace('//', '',implode('/', $categories));

			$quantity = Product::getQuantity((int)$product['id_product'], !empty($product['id_product_attribute']) ? (int)$product['id_product_attribute'] : NULL);
			
			$indice = count($products_temp);
			
			$shipping_price_local = $this->getValue('shipping_price_local');
			if (self::$shipping_by_product && !empty(self::$shipping_by_product_fieldname))
				$shipping_price_local = $product[self::$shipping_by_product_fieldname];
			
			$products_temp[$indice] = array(
				'Title' => $product['name'],
				'SKU' => $product_reference,
				'Description' => strip_tags($product['description']),
				'EAN' => $codeEan,
				'Quantity' => $quantity,
				'PriceFixed' => $product_price_without_reduction,
				'PriceRetail' => $product_price,
				'Etat' => 11,
				'SKUFamily' => self::$type_sku == 'reference' ? $product['declinaison_default_ref'] : $product['declinaison_default'],
				'Classification' => str_replace('Accueil/', '', $classification),
				'shipping_delay' => $this->getValue('shipping_delay'),
				'Comment' => $this->getValue('comment'),
				'Brand' => !empty($product['name_manufacturer']) ? $product['name_manufacturer'] : $this->getValue('default_brand')
			);
			
			if ($shipping_price_local == '-')
				unset($products_temp[$indice]['shipping_price_local']);
			
			if (empty($products_temp[$indice]['shipping_price_international']))
				unset($products_temp[$indice]['shipping_price_international']);
			
			$images = $this->getProductImages($product);
			foreach ($images as $key => $image)
				if (is_object($link))
				{
					$img_url = $link->getImageLink($product['link_rewrite'], (int)$product['id_product'].'-'.(int)$image['id_image'], Gateway::getConfig('IMAGE_TYPE_NAME'));
					$products_temp[$indice]['Image'.($key+1)] = 'http://'.str_replace('http://', '', $img_url);
				}

			// Attributes and fetures of product
			$products_temp[$indice]['ArrayOfSpecificFields'] = array();
			$products_temp[$indice]['ArrayOfSpecificFields'][] = array('Name' => 'categorie', 'Value' => $category_default->name);
			
			$sql = '
				SELECT GROUP_CONCAT(DISTINCT CONCAT(fl.`name`," {##} ",fvl.`value`) SEPARATOR "'.pSQL($this->getValue('separator')).' ") as feature_name
				FROM `'._DB_PREFIX_.'feature_product` fp
				LEFT JOIN `'._DB_PREFIX_.'feature_value_lang` fvl ON (fp.`id_feature_value` = fvl.`id_feature_value` AND fvl.id_lang='.(int)$cookie->id_lang.')
				LEFT JOIN `'._DB_PREFIX_.'feature_value` fv ON (fv.`id_feature_value` = fvl.`id_feature_value`)
				LEFT JOIN `'._DB_PREFIX_.'feature_lang` fl ON (fl.`id_feature` = fv.`id_feature` AND fl.`id_lang`='.(int)$cookie->id_lang.')
				WHERE fp.`id_product` = '.(int)$product['id_product'].'
			';
			
			$product['feature_name'] = Db::getInstance()->getValue($sql);
			
			if (empty($product['attribute_name']) && empty($product['feature_name']))
				continue;
			
			$features_attributes = array();
			if (!empty($product['attribute_name']))
				$features_attributes = explode($this->getValue('separator'), $product['attribute_name']);

			if (!empty($product['feature_name'])) 
				$features_attributes = array_merge($t_carac_attr, explode($this->getValue('separator'), $product['feature_name']));

			$feature_links = $this->getValue('feature_links');
			foreach ($features_attributes as $value)
			{
				$infos = explode(' {##} ', $value);
				if (count($infos) == 2 && !empty($infos[0]) && !empty($infos[1]) && !empty($feature_links[trim($infos[0])]))
				{
					$specific_name = $feature_links[trim($infos[0])];
					$products_temp[$indice]['ArrayOfSpecificFields'][] = array('Name' => $specific_name, 'Value' => $infos[1]);
				}
			}
			
			if (count(self::$customizable_field) > 0)
				foreach (self::$customizable_field as $key => $value)
					$products_temp[$indice]['ArrayOfSpecificFields'][] = array('Name' => $key, 'Value' => $value);

		}

		return $products_temp;
	}

	public function getProductCategories($product)
	{
		$category = $category_default = new Category((int)$product['id_category_default'], (int)$cookie->id_lang);
		$categories = array();
		$categories[] = $category->name;
		$security = 0;
		if ($category->id_parent != 1)
			while ($security < 200 && $category->id_parent != 1)
			{
				$category = new Category((int)$category->id_parent, (int)$cookie->id_lang);
				if (!empty($category->name))
					$categories[] = $category->name;

				$security++;
			}
		
		array_reverse($categories);
		
		return $categories;
	}
	
	public function getProductImages($product)
	{
		$images = Db::getInstance()->ExecuteS('
						SELECT `id_image`, `cover`
						FROM `'._DB_PREFIX_.'image`
						WHERE `id_product` = '.(int)$product['id_product'].'
						ORDER BY `cover` DESC
						LIMIT 6
					');

		if (!$product['id_product_attribute'])
			return $images;
			
		$images_attribute = Db::getInstance()->ExecuteS('
					SELECT i.`id_image`, i.`cover`
					FROM `'._DB_PREFIX_.'product_attribute_image` pai
					INNER JOIN `'._DB_PREFIX_.'image` i USING(id_image)
					WHERE i.`id_product` = '.(int)$product['id_product'].'
					AND pai.`id_product_attribute` = '.(int)$product['id_product_attribute'].'
					ORDER BY i.`cover` DESC
					LIMIT 6
				');

		if (!empty($images_attribute))
			return $images_attribute;
		return $images;
	}

	/**
	 * Envoie des produits a NetEven.
	 * @param $t_retour
	 * @return mixed
	 */
	private function addProductInNetEven($neteven_products)
	{
		if (count($neteven_products) == 0)
		{
			if ($this->getValue('debug'))
				Toolbox::displayDebugMessage(self::getL('No product to send !'));
			return;
		}

		try
		{
			Toolbox::addLogLine(self::getL('Number of product send to NetEven').' '.count($neteven_products));
			$params = array('items' => $neteven_products);
			
			$response = $this->_client->PostItems($params);
			$itemsStatus = $response->PostItemsResult->InventoryItemStatusResponse;

			if ($this->getValue('debug'))
				Toolbox::displayDebugMessage(self::getL('Sends data to NetEven'));

		}
		catch (Exception $e)
		{
			if ($this->getValue('debug'))
				Toolbox::displayDebugMessage(self::getL('Failed to send data to Neteven'));

			$erreur = '<pre>Last request:\n' .($this->_client->__getLastRequest()) . '</pre>\n';
			Toolbox::manageError($e, 'add product nombre => '.count($neteven_products).' '.$erreur);
			$response = '';
			$itemsStatus = '';
		}

		if ($this->getValue('send_request_to_mail'))
			$this->sendDebugMail($this->getValue('mail_list_alert'), self::getL('Debug - Control request').' addProductInNetEven', $this->_client->__getLastRequest());

		if ($response != '' && $itemsStatus != '')
		{
			if (is_array($response->PostItemsResult->InventoryItemStatusResponse))
			{
				foreach ($response->PostItemsResult->InventoryItemStatusResponse as $rep)
				{
					Toolbox::addLogLine($rep->ItemCode.' '.$rep->StatusResponse);
					if ($this->getValue('debug'))
						Toolbox::displayDebugMessage(self::getL('Add product').' : '.$rep->ItemCode.' '.$rep->StatusResponse);

				}
			}
			else
			{
				Toolbox::addLogLine($response->PostItemsResult->InventoryItemStatusResponse->ItemCode.' '.$response->PostItemsResult->InventoryItemStatusResponse->StatusResponse);
				if ($this->getValue('debug'))
					Toolbox::displayDebugMessage(self::getL('Add product').' : '.$response->PostItemsResult->InventoryItemStatusResponse->ItemCode.' '.$response->PostItemsResult->InventoryItemStatusResponse->StatusResponse);

			}
		}

		Toolbox::writeLog();
		Configuration::updateValue('neteven_date_export_product', date('Y-m-d H:i:s'));
	}


	/**
	 * On supprime tous les produits qui n'ont pas de code EAN13.
	 * @param $t_product
	 * @param $is_display
	 * @return array
	 */
	private function delProductWithoutEAN($products, $is_display)
	{
		return $products;
		
		$products_with_ean = array();
		foreach ($products as $key => $product)
		{
			if ((!empty($product['id_product_attribute']) && !empty($product['ean13_declinaison'])) || (empty($product['id_product_attribute']) && !empty($product['ean13'])))
				$products_with_ean[] = $product;
			else 
			{
				if ($this->getValue('debug'))
				{
					Toolbox::addLogLine(self::getL('No EAN13 for product').' : '.$product['id_product'].'/'.$product['id_produt_attribute']);
					Toolbox::displayDebugMessage(self::getL('No EAN13 for product').' : '.$product['id_product'].'/'.$product['id_produt_attribute']);
				}
			}
		}
		return $products_with_ean;
	}

	public function viewProductInNetEven()
	{	
		try 
		{
			$response = $this->_client->GetItems();
			$items = $response->items->InventoryItem;
		}
		catch (Exception $e)
		{
			$response = '';
			$items = '';
		}
		
		Tools::p($items);
	}
}