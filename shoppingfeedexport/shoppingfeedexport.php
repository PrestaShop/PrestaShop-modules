<?php
/*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 9074 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class ShoppingFeedExport extends Module
{
	public function __construct()
	{
	 	$this->name = 'shoppingfeedexport';
	 	$this->tab = 'smart_shopping';
	 	$this->version = '2.0.2';
		$this->author = 'PrestaShop';
		$this->limited_countries = array('us');

	 	parent::__construct();

		$this->displayName = $this->l('Export Shopping Feed');
		$this->description = $this->l('Exportez vos produits vers plus de 100 comparateurs de prix et places de marché');
		$this->confirmUninstall = $this->l('Êtes-vous sûr de vouloir supprimer ce module ?');
	}

	public function install()
	{
		return (parent::install() && $this->_initHooks() && $this->_initConfig());
	}
	
	/* REGISTER HOOKS */
	private function _initHooks()
	{
		if (!$this->registerHook('newOrder') ||
			!$this->registerHook('footer') ||
			!$this->registerHook('postUpdateOrderStatus') ||
			!$this->registerHook('adminOrder') ||
			!$this->registerHook('updateProduct') ||
			!$this->registerHook('backOfficeTop') ||
			!$this->registerHook('updateProductAttribute') ||
			!$this->registerHook('top')) 
			return false;

		return true;
	}
	
	/* SET DEFAULT CONFIGURATION */
	private function _initConfig()
	{
		//Avoid servers IPs
		Db::getInstance()->Execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'customer_ip` (
			`id_customer_ip` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`id_customer` int(10) unsigned NOT NULL,
			`ip` varchar(32) DEFAULT NULL,
			PRIMARY KEY (`id_customer_ip`),
			KEY `idx_id_customer` (`id_customer`)
			) ENGINE='._MYSQL_ENGINE_.'  DEFAULT CHARSET=utf8;');
		
		if (!Configuration::updateValue('SHOPPING_FLUX_TOKEN', md5(rand())) ||
			!Configuration::updateValue('SHOPPING_FLUX_TRACKING','') ||
			!Configuration::updateValue('SHOPPING_FLUX_BUYLINE','') ||
			!Configuration::updateValue('SHOPPING_FLUX_ORDERS','') ||
			!Configuration::updateValue('SHOPPING_FLUX_STATUS','') ||
			!Configuration::updateValue('SHOPPING_FLUX_LOGIN','') ||
			!Configuration::updateValue('SHOPPING_FLUX_INDEX','http://'.Tools::getHttpHost().__PS_BASE_URI__) ||
			!Configuration::updateValue('SHOPPING_FLUX_STOCKS',''))
			return false;

		return true;

	}

	public function uninstall()
	{
		if (!Configuration::deleteByName('SHOPPING_FLUX_TOKEN') ||
			!Configuration::deleteByName('SHOPPING_FLUX_TRACKING') ||
			!Configuration::deleteByName('SHOPPING_FLUX_BUYLINE') ||
			!Configuration::deleteByName('SHOPPING_FLUX_ORDERS') ||
			!Configuration::deleteByName('SHOPPING_FLUX_STATUS') ||
			!Configuration::deleteByName('SHOPPING_FLUX_LOGIN') ||
			!Configuration::deleteByName('SHOPPING_FLUX_INDEX') ||
			!Configuration::deleteByName('SHOPPING_FLUX_STOCKS') ||
			!parent::uninstall())
				return false;
		
		return true;
	}

	public function getContent()
	{
		$statusXML = $this->_checkToken();
		$status = $statusXML->Response->Status;
		$price = (float)$statusXML->Response->Price;
		
		switch ($status)
		{
			case 'Client':
				$this->_html .= $this->_clientView();
				break;
			case 'Prospect':
				$this->_html .= $this->displayConfirmation($this->l('Votre enregistrement Shopping Feed est effectif, vous serez contacté sous peu.'));
				// No break, we want the code below to be executed
			case 'New':
			default:
				$this->_html .= $this->_defaultView($price);
				Configuration::updateValue('SHOPPING_FLUX_TRACKING','');
				Configuration::updateValue('SHOPPING_FLUX_BUYLINE','');
				Configuration::updateValue('SHOPPING_FLUX_ORDERS','');
				Configuration::updateValue('SHOPPING_FLUX_STOCKS','');
				break;
		}
		
		if (!in_array('curl', get_loaded_extensions()))
			$this->_html .= '<br/><strong>'.$this->l('Vous devez installer / activer l\'extension CURL pour pouvoir bénéficier de la remontée des commandes. Contactez votre administrateur pour savoir comment procéder').'</strong>';

		return $this->_html;
	}
        
	/* Check wether the Token is known by Shopping Feed */
	private function _checkToken()
	{
		return $this->_callWebService('IsClient');
	}
	
	/* Default view when site isn't in Shopping Feed DB */
	private function _defaultView($price = 0)
	{
		global $cookie;
		
		//uri feed
		$uri = 'http://'.Tools::getHttpHost().__PS_BASE_URI__.'modules/shoppingfeedexport/feed.php?token='.Configuration::get('SHOPPING_FLUX_TOKEN');
		//uri images
		$uri_img = 'http://'.Tools::getHttpHost().__PS_BASE_URI__.'modules/shoppingfeedexport/screens/';
		//owner object
		$owner = new Employee($cookie->id_employee);
		//post process
		$send_mail = Tools::getValue('send_mail');
		if (isset($send_mail) && $send_mail != NULL)
			$this->sendMail();
		
		//first fieldset
		$html = '<h2>'.$this->displayName.'</h2>
		<fieldset>
			<legend>'.$this->l('Informations').'</legend>
			<p><b>'.$this->l('Shopping Feed vous permettra de :').'</b></p>
			<p>
					<ol>
							<li>'.$this->l('Promouvoir vos produits sur plus de 100 Comparateurs de Prix (Google Shopping, Leguide.com, Kelkoo, Cherchons.com, etc...)').'</li>
							<li>'.$this->l('Choisir les produits que vous souhaitez diffuser et en calculer la rentabilité en fonction du Comparateur de Prix').'</li>
							<li>'.$this->l('Vendre sur toutes les Places de Marché (PriceMinister, Amazon, RueDuCommerce, eBay, Cdiscount, Fnac, etc...)').'</li>
							<li>'.$this->l('Ré-importer vos commandes Places de Marché directement dans votre Prestashop').'</li>
							<li>'.$this->l('Créer en masse des campagnes Adwords pour chacune de vos fiches produit.').'</li>
					</ol>
			</p>';
			
		if ($price!=0)
			$html .= '<p>'.$this->l('A partir de 69€ H.T/mois. ');
			
		$html .= $this->l('Bénéficiez de 1 mois de test gratuit et sans engagement.').'</p>
			<br/>
			<p>'.$this->l('Le tout, via une interface unique, pratique et agréable d\'utilisation').' :</p>
			<p style="text-align:center">';

		//add 6 screens
		for ($i = 1; $i <= 6; $i++)
			$html .= '<a href="'.$uri_img.$i.'.jpg" target="_blank"><img style="margin:10px" src="'.$uri_img.$i.'.jpg" width="250" /></a>';
		$html .= '</p></fieldset><br/>';

		//second fieldset
		$html .= '
		<form method="post" action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'">
			<fieldset>
				<legend>'.$this->l('Demandez ici votre clé d\'activation').'</legend>
				<p style="margin-bottom:20px" >'.$this->l('Ce module vous est offert par Shopping Feed et est utilisable via une souscription mensuelle au service. Envoyez-nous simplement ce formulaire :').'</p>
				<p><label>'.$this->l('Nom du site').' : </label><input type="text" name="site" value="'.Tools::safeOutput(Configuration::get('PS_SHOP_NAME')).'"></p>
				<p><label>'.$this->l('Nom').' : </label><input type="text" name="nom" value="'.Tools::safeOutput($owner->lastname).'"></p>
				<p><label>'.$this->l('Prenom').' : </label><input type="text" name="prenom" value="'.Tools::safeOutput($owner->firstname).'"></p>
				<p><label>'.$this->l('E-mail').' : </label><input type="text" name="email" value="'.Tools::safeOutput(Configuration::get('PS_SHOP_EMAIL')).'"></p>
				<p><label>'.$this->l('Téléphone').' : </label><input type="text" name="telephone" value="'.Tools::safeOutput(Configuration::get('PS_SHOP_PHONE')).'"></p>
				<input type="hidden" name="flux" value="'.Tools::safeOutput($uri).'"/>
				<p style="text-align:center" ><input type="submit" value="'.$this->l('Envoyer la demande').'" name="send_mail" class="button"/></p>
			</fieldset>
		</form>';
		
		return $html;
	}
	
	/* View when site is client */
	private function _clientView()
	{
		$rec_config = Tools::getValue('rec_config');
		if (isset($rec_config) && $rec_config != NULL)
			$this->_treatForm();
	
		$configuration = Configuration::getMultiple(array('SHOPPING_FLUX_TOKEN','SHOPPING_FLUX_TRACKING','SHOPPING_FLUX_BUYLINE', 
			'SHOPPING_FLUX_ORDERS', 'SHOPPING_FLUX_STATUS', 'SHOPPING_FLUX_LOGIN', 'SHOPPING_FLUX_STOCKS', 'SHOPPING_FLUX_INDEX','PS_LANG_DEFAULT'));

		$html = $this->_getFeedContent($configuration);
		$html .= $this->_getParametersContent($configuration);

		return $html;
		
	}
	
	/* Fieldset for params */
	private function _getParametersContent($configuration)
	{
		return '<form method="post" action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'">
					<fieldset>
						<legend>'.$this->l('Vos paramètres').'</legend>
						<p><label>Login Shopping Feed : </label><input type="text" name="SHOPPING_FLUX_LOGIN" value="'.Tools::safeOutput($configuration['SHOPPING_FLUX_LOGIN']).'"/></p>
						<p><label>Token Shopping Feed : </label><input type="text" name="SHOPPING_FLUX_TOKEN" value="'.Tools::safeOutput($configuration['SHOPPING_FLUX_TOKEN']).'" style="width:auto"/></p>
						<p><label>Buyline : </label><input type="checkbox" name="SHOPPING_FLUX_BUYLINE" '.Tools::safeOutput($configuration['SHOPPING_FLUX_BUYLINE']).'/> les origines de toutes vos commandes seront trackées.</p>
						<p><label>Tracking ventes : </label><input type="checkbox" name="SHOPPING_FLUX_TRACKING" '.Tools::safeOutput($configuration['SHOPPING_FLUX_TRACKING']).'/> les commandes venant des comparateurs seront trackées.</p>
						<p><label>Remontée commandes : </label><input type="checkbox" name="SHOPPING_FLUX_ORDERS" '.Tools::safeOutput($configuration['SHOPPING_FLUX_ORDERS']).'/> les commandes venant des places de marché seront automatiquement importées.</p>
						<p><label>Mise à jour des statuts : </label><input type="checkbox" name="SHOPPING_FLUX_STATUS" '.Tools::safeOutput($configuration['SHOPPING_FLUX_STATUS']).'/> les commandes qui passeront en statut <b>'.$this->_getOrderStates($configuration['PS_LANG_DEFAULT']).'</b> seront mises à jour sur les places de marché.</p>
						<p><label>Synchronisation des stocks et des prix : </label><input type="checkbox" name="SHOPPING_FLUX_STOCKS" '.Tools::safeOutput($configuration['SHOPPING_FLUX_STOCKS']).'/> chaque mouvement de stock ou de prix sera répercuté sur les places de marché.</p>
						<p style="margin-top:20px"><input type="submit" value="Valider" name="rec_config" class="button"/></p>
					</fieldset>
				</form>';
	}
	
	/* Fieldset for feed URI */
	private function _getFeedContent($configuration)
	{
		return '
		<img style="margin:10px" src="'.Tools::safeOutput($configuration['SHOPPING_FLUX_INDEX']).'modules/shoppingfeedexport/logo.jpg" height="75" />
		<fieldset>
			<legend>'.$this->l('Vos flux produits').'</legend>
			<p>
				<a href="'.Tools::safeOutput($configuration['SHOPPING_FLUX_INDEX']).'modules/shoppingfeedexport/feed.php?token='.Tools::safeOutput($configuration['SHOPPING_FLUX_TOKEN']).'" target="_blank">
					'.Tools::safeOutput($configuration['SHOPPING_FLUX_INDEX']).'modules/shoppingfeedexport/feed.php?token='.Tools::safeOutput($configuration['SHOPPING_FLUX_TOKEN']).'
				</a>
			</p>
		</fieldset>
		<br/>';
	}
	
	/* Form record */
	private function _treatForm()
	{
		$configuration = Configuration::getMultiple(array('SHOPPING_FLUX_TRACKING','SHOPPING_FLUX_BUYLINE', 
			'SHOPPING_FLUX_ORDERS', 'SHOPPING_FLUX_STATUS', 'SHOPPING_FLUX_LOGIN', 'SHOPPING_FLUX_STOCKS'));

		foreach ($configuration as $key => $val)
		{
			$value = Tools::getValue($key, '');
			Configuration::updateValue($key, $value == 'on' ? 'checked' :  $value);
		}
	}
	
	/* Send mail to PS and Shopping Feed */
	private function sendMail()
	{
		$this->_html .= $this->displayConfirmation($this->l('Votre enregistrement Shopping Feed est effectif, vous serez contacté sous peu.')).'
		<img src="http://www.prestashop.com/partner/shoppingflux/image.php?site='.Tools::safeOutput(Tools::getValue('site')).'&nom='.Tools::safeOutput(Tools::getValue('nom')).'&prenom='.Tools::safeOutput(Tools::getValue('prenom')).'&email='.Tools::safeOutput(Tools::getValue('email')).'&telephone='.Tools::safeOutput(Tools::getValue('telephone')).'&flux='.Tools::safeOutput(Tools::getValue('flux')).'" border="0" />';

		$xml  = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<AddProspect>';
		$xml .= '<LastName><![CDATA['.Tools::safeOutput(Tools::getValue('nom')).']]></LastName>';
		$xml .= '<FirstName><![CDATA['.Tools::safeOutput(Tools::getValue('prenom')).']]></FirstName>';
		$xml .= '<Site><![CDATA['.Tools::safeOutput(Tools::getValue('site')).']]></Site>';
		$xml .= '<Email><![CDATA['.Tools::safeOutput(Tools::getValue('email')).']]></Email>';
		$xml .= '<Phone><![CDATA['.Tools::safeOutput(Tools::getValue('telephone')).']]></Phone>';
		$xml .= '<Feed><![CDATA['.Tools::safeOutput(Tools::getValue('flux')).']]></Feed>';
		$xml .= '</AddProspect>';

		if (in_array('curl',get_loaded_extensions()))
			$this->_callWebService('AddProspectPrestashop', $xml);
	}
	
	/* Clean XML tags */
	private function clean($string)
	{
		return str_replace("\r\n", '', strip_tags($string));
	}
        
	/* Feed content */
	public function generateFeed()
	{
		if (Tools::getValue('token') == '' || Tools::getValue('token') != Configuration::get('SHOPPING_FLUX_TOKEN'))
			die("<?xml version='1.0' encoding='utf-8'?><error>Invalid Token</error>");

		$configuration = Configuration::getMultiple(array('PS_TAX_ADDRESS_TYPE','PS_CARRIER_DEFAULT','PS_COUNTRY_DEFAULT', 
			'PS_LANG_DEFAULT', 'PS_SHIPPING_FREE_PRICE', 'PS_SHIPPING_HANDLING', 'PS_SHIPPING_METHOD', 'PS_SHIPPING_FREE_WEIGHT'));

		$carrier = new Carrier((int)$configuration['PS_CARRIER_DEFAULT']);
		$products = Product::getSimpleProducts($configuration['PS_LANG_DEFAULT'], null, true);

		echo '<?xml version="1.0" encoding="utf-8"?>';
		echo '<produits>';

		foreach ($products as $productArray)
		{
			$product = new Product((int)($productArray['id_product']), true, $configuration['PS_LANG_DEFAULT']);
			$link = new Link();

			if ($product->active == 1)
			{
				echo '<produit>';
				echo $this->_getBaseData($product, $configuration, $link, $carrier);
				echo $this->_getImages($product, $configuration, $link);
				echo $this->_getUrlCategories($product, $configuration, $link);			
				echo $this->_getFeatures($product, $configuration);	
				echo $this->_getCombinaisons($product, $configuration, $link, $carrier);
				echo $this->_getFilAriane($product, $configuration);
				echo '<manufacturer><![CDATA['.$product->manufacturer_name.']]></manufacturer>';
				echo '<supplier><![CDATA['.$product->supplier_name.']]></supplier>';
				if (is_array($product->specificPrice))
				{
					echo '<from><![CDATA['.$product->specificPrice['from'].']]></from>';
					echo '<to><![CDATA['.$product->specificPrice['to'].']]></to>';
				}
				else
				{
					echo '<from/>';
					echo '<to/>';
				}
				echo '<url-fournisseur><![CDATA['.$link->getSupplierLink($product->id_supplier, NULL, $configuration['PS_LANG_DEFAULT']).']]></url-fournisseur>';
				echo '<url-fabricant><![CDATA['.$link->getManufacturerLink($product->id_manufacturer, NULL, $configuration['PS_LANG_DEFAULT']).']]></url-fabricant>';
				echo '</produit>';
			}
		}

		echo '</produits>';
	}
	
	/* Default data, in Product Class */
	private function _getBaseData($product, $configuration, $link, $carrier)
	{
		$ret = '';
	
		$titles = array(
			0 => 'id',
			1 => 'nom',
			2 => 'url',
			4 => 'description',
			5 => 'description-courte',
			6 => 'prix',
			7 => 'prix-barre',
			8 => 'frais-de-port',
			9 => 'delai-livraison',
			10 => 'marque',
			11 => 'rayon',
			13 => 'quantite',
			14 => 'ean',
			15 => 'poids',
			16 => 'ecotaxe',
			17 => 'tva',
			18 => 'ref-constructeur',
			19 => 'ref-fournisseur'
		);

		$data[0]  = $product->id;
		$data[1]  = $product->name;
		$data[2]  = $link->getProductLink($product);
		$data[4]  = $product->description;
		$data[5]  = $product->description_short;
		$data[6]  = $product->getPrice(true, NULL, 2, NULL, false, true, 1);
		$data[7]  = $product->getPrice(true, NULL, 2, NULL, false, false, 1);
		$data[8]  = $this->_getShipping($product, $configuration, $carrier);
		$data[9]  = $carrier->delay[$configuration['PS_LANG_DEFAULT']];
		$data[10] = $product->manufacturer_name;
		$data[11] = $this->_getCategories($product, $configuration);
		$data[13] = $product->quantity;
		$data[14] = $product->ean13;
		$data[15] = $product->weight;
		$data[16] = $product->ecotax;
		$data[17] = $product->tax_rate;
		$data[18] = $product->reference;
		$data[19] = $product->supplier_reference;

		foreach ($titles as $key => $balise)
			$ret .= '<'.$balise.'><![CDATA['.$data[$key].']]></'.$balise.'>';

		return $ret;
	}
	
	/* Shipping prices */
	private function _getShipping($product, $configuration, $carrier, $attribute_id = null, $attribute_weight = null)
	{
		$default_country = new Country($configuration['PS_COUNTRY_DEFAULT'], $configuration['PS_LANG_DEFAULT']);
		$id_zone = (int)$default_country->id_zone;
		$this->id_address_delivery = 0;
		$carrier_tax = Tax::getCarrierTaxRate((int)$carrier->id, (int)$this->{$configuration['PS_TAX_ADDRESS_TYPE']});

		$shipping = 0;

		$product_price = $product->getPrice(true, $attribute_id, 2, NULL, false, true, 1);
		$shipping_free_price = $configuration['PS_SHIPPING_FREE_PRICE'];
		$shipping_free_weight = isset($configuration['PS_SHIPPING_FREE_WEIGHT']) ? $configuration['PS_SHIPPING_FREE_WEIGHT'] : 0;

		if (!(((float)$shipping_free_price > 0) && ($product_price >= (float)$shipping_free_price)) &&
			!(((float)$shipping_free_weight > 0) && ($product->weight + $attribute_weight >= (float)$shipping_free_weight)))
		{
			if (isset($configuration['PS_SHIPPING_HANDLING']) && $carrier->shipping_handling)
				$shipping = (float)($configuration['PS_SHIPPING_HANDLING']);

			if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT)
				$shipping += $carrier->getDeliveryPriceByWeight($product->weight, $id_zone);
			else
				$shipping += $carrier->getDeliveryPriceByPrice($product_price, $id_zone);

			$shipping *= 1 + ($carrier_tax / 100);
			$shipping = (float)(Tools::ps_round((float)($shipping), 2));
		}

		return $shipping;
	}
	
	/* Product category */
	private function _getCategories($product, $configuration)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT cl.`name`
			FROM `'._DB_PREFIX_.'product` p
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category`)
			WHERE p.`id_product` = '.(int)$product->id.'
			AND cl.`id_lang` = '.(int)$configuration['PS_LANG_DEFAULT']);
	}
	
	/* Images URIs */
	private function _getImages($product, $configuration, $link)
	{
		$images = $product->getImages($configuration['PS_LANG_DEFAULT']);
		$ret = '<images>';
		
		if ($images != false)
		{
			foreach ($images as $image)
			{
				$ids = $product->id.'-'.$image['id_image'];
				$ret .= '<image><![CDATA[http://'.$link->getImageLink($product->link_rewrite, $ids, 'large').']]></image>';
				$ret = str_replace('http://http://', 'http://', $ret);
			}
		}
		$ret .= '</images>';
		return $ret;
	}
	
	/* Categories URIs */
	private function _getUrlCategories($product, $configuration, $link)
	{
		$ret = '<uri-categories>';
		foreach ($this->_getProductCategoriesFull($product->id, $configuration['PS_LANG_DEFAULT']) as $key => $categories)
		{
			$ret .= '<uri><![CDATA['.$link->getCategoryLink($key, null, $configuration['PS_LANG_DEFAULT']).']]></uri>';
		}
		$ret .= '</uri-categories>';
		return $ret;
	}
	
	/* All product categories */
	private function _getProductCategoriesFull($id_product, $id_lang)
	{
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT cp.`id_category`, cl.`name`, cl.`link_rewrite` FROM `'._DB_PREFIX_.'category_product` cp
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (cp.`id_category` = cl.`id_category`)
			WHERE cp.`id_product` = '.(int)$id_product.'
			AND cl.`id_lang` = '.(int)$id_lang.'
			ORDER BY cp.`position` DESC');

		$ret = array();
		foreach ($row as $val)
			$ret[$val['id_category']] = $val;
		return $ret;
	}
	
	/* Features */
	private function _getFeatures($product, $configuration)
	{
		$ret = '<caracteristiques>';
		foreach ($product->getFrontFeatures($configuration['PS_LANG_DEFAULT']) as $feature)
		{
			$feature['name'] = $this->_clean($feature['name']);

			if (!empty($feature['name']))
				$ret .= '<'.$feature['name'].'><![CDATA['.$feature['value'].']]></'.$feature['name'].'>';
		}
		$ret .= '</caracteristiques>';
		return $ret;
	}
	
	/* Product attributes */
	private function _getCombinaisons($product, $configuration, $link, $carrier)
	{
		$combinations = array();

		$ret = '<declinaisons>';

		foreach ($product->getAttributeCombinaisons($configuration['PS_LANG_DEFAULT']) as $combinaison)
		{
			$combinations[$combinaison['id_product_attribute']]['attributes'][$combinaison['group_name']] = $combinaison['attribute_name'];
			$combinations[$combinaison['id_product_attribute']]['ean13'] = $combinaison['ean13'];
			$combinations[$combinaison['id_product_attribute']]['quantity'] = $combinaison['quantity'];
		}

		foreach ($combinations as $id => $combination)
		{
			$ret .= '<declinaison>';
			$ret .= '<id><![CDATA['.$id.']]></id>';
			$ret .= '<ean><![CDATA['.$combination['ean13'].']]></ean>';
			$ret .= '<quantite><![CDATA['.$combination['quantity'].']]></quantite>';
			$ret .= '<prix><![CDATA['.$product->getPrice(true, $id, 2, NULL, false, true, 1).']]></prix>';
			$ret .= '<prix-barre><![CDATA['.$product->getPrice(true, $id, 2, NULL, false, false, 1).']]></prix-barre>';
			$ret .= '<frais-de-port><![CDATA['.$this->_getShipping($product, $configuration, $carrier, $id, $combination['weight']).']]></frais-de-port>';
			$ret .= '<images>';

			$image_child = true;

			foreach ($product->_getAttributeImageAssociations($id) as $image)
			{
				if (empty($image))
				{
					$image_child = false;
					break;
				}
				$ret .= '<image><![CDATA['.$link->getImageLink($product->link_rewrite, $product->id.'-'.$image, 'large').']]></image>';
			}

			if (!$image_child)
			{
				foreach ($product->getImages($configuration['PS_LANG_DEFAULT']) as $images)
				{
					$ids = $product->id.'-'.$images['id_image'];
					$ret .= '<image><![CDATA[http://'.$link->getImageLink($product->link_rewrite, $ids, 'large').']]></image>';
					$ret = str_replace('http://http://', 'http://', $ret);
				}
			}

			$ret .= '</images>';
			$ret .= '<attributs>';

			asort($combination['attributes']);
			foreach ($combination['attributes'] as $attributeName => $attributeValue)
			{
				$attributeName = $this->_clean($attributeName);
				if (!empty($attributeName))
					$ret .= '<'.$attributeName.'><![CDATA['.$attributeValue.']]></'.$attributeName.'>';
			}

			$ret .= '</attributs>';
			$ret .= '</declinaison>';
		}

		$ret .= '</declinaisons>';

		return $ret;
	}
	
	/* Category tree XML */
	private function _getFilAriane($product, $configuration)
	{
		$category = '';
		$ret = '<fil-ariane>';
		foreach ($this->_getProductFilAriane($product->id, $configuration['PS_LANG_DEFAULT']) as $categories)
			$category .= $categories.' > ';
		$ret .= '<![CDATA['.substr($category, 0, -3).']]></fil-ariane>';
		return $ret;
	}
	
	/* Category tree */
	private function _getProductFilAriane($id_product, $id_lang)
	{
		$ret = array();
		$id_parent = '';

		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT cl.`name`, p.`id_category_default` as id_category, c.`id_parent` FROM `'._DB_PREFIX_.'product` p
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (p.`id_category_default` = cl.`id_category`)
			LEFT JOIN `'._DB_PREFIX_.'category` c ON (p.`id_category_default` = c.`id_category`)
			WHERE p.`id_product` = '.(int)$id_product.'
			AND cl.`id_lang` = '.(int)$id_lang);

		foreach ($row as $val)
		{
			$ret[$val['id_category']] = $val['name'];
			$id_parent = $val['id_parent'];
		}

		while ($id_parent != 0)
		{
			$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
				SELECT cl.`name`, c.`id_category`, c.`id_parent` FROM `'._DB_PREFIX_.'category_lang` cl
				LEFT JOIN `'._DB_PREFIX_.'category` c ON (c.`id_category` = '.(int)$id_parent.')
				WHERE cl.`id_category` = '.(int)$id_parent.'
				AND cl.`id_lang` = '.(int)$id_lang);
			foreach ($row as $val)
			{
				$ret[$val['id_category']] = $val['name'];
				$id_parent = $val['id_parent'];
			}
		}

		$ret = array_reverse($ret);
		return $ret;
	}

	public function hookbackOfficeTop()
	{
		if (Tools::getValue('controller') == 'adminorders' && Configuration::get('SHOPPING_FLUX_ORDERS') != '' && in_array('curl', get_loaded_extensions()))
		{
		
			$ordersXML = $this->_callWebService('GetOrders');

			foreach ($ordersXML->Response->Orders->Order as $order)
			{
				$this->_validOrders((string)$order->IdOrder, (string)$order->Marketplace);
				$mail = strval($order->BillingAddress->Email);

				$email = (empty($mail)) ? pSQL($order->IdOrder.'@'.$order->Marketplace.'.sf') : pSQL($mail);

				$id_customer = $this->_getCustomer($email, strval($order->BillingAddress->LastName), strval($order->BillingAddress->FirstName));
				$id_customer_shipping = $this->_getCustomer($email, strval($order->ShippingAddress->LastName), strval($order->ShippingAddress->FirstName));
				$id_address_billing = $this->_getAddress($order->BillingAddress, $id_customer, 'Billing');
				$id_address_shipping = $this->_getAddress($order->ShippingAddress, $id_customer_shipping, 'Shipping');
				$products_available = $this->_checkProducts($order->Products);

				$current_customer = new Customer((int)$id_customer);
				$last_cart = new Cart($current_customer->getLastCart());
				$add = true;
				
				if ($last_cart->id)
				{
					$date_cart = date_create($last_cart->date_add);
					$date = new Datetime();
					$date->modify('-5 min');
					if ($date<$date_cart)
						$add = false;
				}

				if ($products_available && $id_address_shipping && $id_address_billing && $id_customer && $id_customer_shipping && $add)
				{
					$cart = $this->_getCart($id_customer, $id_address_billing, $id_address_shipping, $order->Products);

					if ($cart)
					{
						Db::getInstance()->autoExecute(_DB_PREFIX_.'customer', array('email' => 'do-not-send@alerts-shopping-feed.com'), 'UPDATE', '`id_customer` = '.(int)$id_customer);
						
                        $customerClear = new Customer();
						
						if (method_exists($customerClear, 'clearCache'))
							$customerClear->clearCache(true);

						$payment = $this->_validateOrder($cart, $order->Marketplace);
                        $id_order = $payment->currentOrder;
                        $reference_order = $payment->currentOrderReference;
                                                
						Db::getInstance()->autoExecute(_DB_PREFIX_.'customer', array('email' => pSQL($email)), 'UPDATE', '`id_customer` = '.(int)$id_customer);

						Db::getInstance()->autoExecute(_DB_PREFIX_.'message', array('id_order' => $id_order, 'message' => 'Numéro de commande '.$order->Marketplace.' :'.$order->IdOrder, 'date_add' => date('Y-m-d H:i:s')), 'INSERT');
						$this->_updatePrices($id_order, $order, $reference_order);
                                                
					}
				}
                                
				$cartClear = new Cart();

				if (method_exists($cartClear, 'clearCache'))
						$cartClear->clearCache(true);

				$addressClear = new Address();

				if (method_exists($addressClear, 'clearCache'))
						$addressClear->clearCache(true);

				$customerClear = new Customer();

				if (method_exists($customerClear, 'clearCache'))
						$customerClear->clearCache(true);
			}
		}
	}
    
	public function hookNewOrder($params)
	{	
		$ip = Db::getInstance()->getValue('SELECT `ip` FROM `'._DB_PREFIX_.'customer_ip` WHERE `id_customer` = '.(int)$params['order']->id_customer);
		if (empty($ip))
			$ip = $_SERVER['REMOTE_ADDR'];

		if ((Configuration::get('SHOPPING_FLUX_TRACKING')!=''||Configuration::get('SHOPPING_FLUX_BUYLINE')!='')&&!in_array($params['order']->payment, $this->_getMarketplaces()))
			file_get_contents('http://tracking.shopping-feed.com/?ip='.$ip.'&cl='.Configuration::get('SHOPPING_FLUX_LOGIN').'&mt='.$params['order']->total_paid_real.'&cmd='.$params['order']->id.'&index='.Configuration::get('SHOPPING_FLUX_INDEX'));

		if (Configuration::get('SHOPPING_FLUX_STOCKS')!=''&&!in_array($params['order']->payment, $this->_getMarketplaces()))
		{
			foreach ($params['cart']->getProducts() as $product)
			{
				$id = (isset($product['id_product_attribute'])) ? (int)$product['id_product'].'_'.(int)$product['id_product_attribute'] : (int)$product['id_product'];
				$qty = (int)$product['stock_quantity'] - (int)$product['quantity'];

				$xml  = '<?xml version="1.0" encoding="UTF-8"?>';
				$xml .= '<UpdateProduct>';
				$xml .= '<Product>';
				$xml .= '<SKU>'.$id.'</SKU>';
				$xml .= '<Quantity>'.$qty.'</Quantity>';
				$xml .= '</Product>';
				$xml .= '</UpdateProduct>';

				$this->_callWebService('UpdateProduct', $xml);
			}
		}
	}

	public function hookFooter()
	{
		if (Configuration::get('SHOPPING_FLUX_BUYLINE') != '')
			return '<script type="text/javascript" src="http://tracking.shopping-feed.com/gg.js"></script>';
		return '';
	}

	public function hookPostUpdateOrderStatus($params)
	{
		if (Configuration::get('SHOPPING_FLUX_STATUS') != '' &&
			$this->_getOrderStates(Configuration::get('PS_LANG_DEFAULT')) == $params['newOrderStatus']->name)
		{
			$order = new Order((int)$params['id_order']);

			if (in_array($order->payment, $this->_getMarketplaces()))
			{
				$message = $order->getFirstMessage();
				$id_order_marketplace = explode(':', $message);

				$xml  = '<?xml version="1.0" encoding="UTF-8"?>';
				$xml .= '<UpdateOrders>';
				$xml .= '<Order>';
				$xml .= '<IdOrder>'.$id_order_marketplace[1].'</IdOrder>';
				$xml .= '<Marketplace>'.$order->payment.'</Marketplace>';
				$xml .= '<Status>Shipped</Status>';
				$xml .= '</Order>';
				$xml .= '</UpdateOrders>';

				$responseXML = $this->_callWebService('UpdateOrders', $xml);

				if (!$responseXML->Response->Error)
					Db::getInstance()->autoExecute(_DB_PREFIX_.'message', array('id_order' => $order->id, 'message' => 'Statut mis à jour sur '.$order->payment.' : '.(string)$responseXML->Response->Orders->Order->StatusUpdated, 'date_add' => date('Y-m-d H:i:s')), 'INSERT');
				else
					Db::getInstance()->autoExecute(_DB_PREFIX_.'message', array('id_order' => $order->id, 'message' => 'Statut mis à jour sur '.$order->payment.' : '.(string)$responseXML->Response->Error->Message, 'date_add' => date('Y-m-d H:i:s')), 'INSERT');

			}
		}
	}

	public function hookAdminOrder($params)
	{
		$this->_html = '';

		if (Configuration::get('SHOPPING_FLUX_BUYLINE') != '')
		{
			$xml  = '<?xml version="1.0" encoding="UTF-8"?>';
			$xml .= '<GetBuylineRoute>';
			$xml .= '<Order>';
			$xml .= (int)$params['id_order'];
			$xml .= '</Order>';
			$xml .= '</GetBuylineRoute>';

			$routeXML = $this->_callWebService('GetBuylineRoute', $xml);

			if (!empty($routeXML->Response->Route))
			{
				$this->_html .= '<br/>
					<fieldset>
						<legend>'.$this->l('Buyline').'</legend>
						<p>'.$routeXML->Response->Route.'</p>
					</fieldset>
					<br/>';
			}
		}

		return $this->_html;
	}

	public function hookupdateProductAttribute($params)
	{
		if (Configuration::get('SHOPPING_FLUX_STOCKS') != '')
		{
			$data = Db::getInstance()->getRow('SELECT `id_product`,`quantity` FROM `'._DB_PREFIX_.'product_attribute` WHERE `id_product_attribute` = '.(int)$params['id_product_attribute']);

			$xml  = '<?xml version="1.0" encoding="UTF-8"?>';
			$xml .= '<UpdateProduct>';
			$xml .= '<Product>';
			$xml .= '<SKU>'.(int)$data['id_product'].'_'.(int)$params['id_product_attribute'].'</SKU>';
			$xml .= '<Quantity>'.(int)$data['quantity'].'</Quantity>';
			$xml .= '<Price>'.Product::getPriceStatic((int)$data['id_product'], true, (int)$params['id_product_attribute'], 2, NULL, false, true, 1).'</Price>';
			$xml .= '<OldPrice>'.Product::getPriceStatic((int)$data['id_product'], true, (int)$params['id_product_attribute'], 2, NULL, false, false, 1).'</OldPrice>';
			$xml .= '</Product>';
			$xml .= '</UpdateProduct>';

			$this->_callWebService('UpdateProduct', $xml);
		}
	}

	public function hookupdateProduct($params)
	{
		if (Configuration::get('SHOPPING_FLUX_STOCKS') != '')
		{
			$xml  = '<?xml version="1.0" encoding="UTF-8"?>';
			$xml .= '<UpdateProduct>';
			$xml .= '<Product>';
			$xml .= '<SKU>'.(int)$params['product']->id.'</SKU>';
			$xml .= '<Quantity>'.(int)$params['product']->quantity.'</Quantity>';
			$xml .= '<Price>'.$params['product']->getPrice(true, NULL, 2, NULL, false, true, 1).'</Price>';
			$xml .= '<OldPrice>'.$params['product']->getPrice(true, NULL, 2, NULL, false, false, 1).'</OldPrice>';
			$xml .= '</Product>';
			$xml .= '</UpdateProduct>';

			$this->_callWebService('UpdateProduct', $xml);
		}
	}
	
	public function hookTop()
	{
		global $cookie;

		if ((int)Db::getInstance()->getValue('SELECT `id_customer_ip` FROM `'._DB_PREFIX_.'customer_ip` WHERE `id_customer` = '.(int)$cookie->id_customer) > 0)
		{
			$updateIp = array('ip' => pSQL($_SERVER['REMOTE_ADDR']));
			Db::getInstance()->autoExecute(_DB_PREFIX_.'customer_ip', $updateIp, 'UPDATE', '`id_customer` = '.(int)$cookie->id_customer);
		}
		else
		{
			$insertIp = array('id_customer' => (int)$cookie->id_customer, 'ip' => pSQL($_SERVER['REMOTE_ADDR']));
			Db::getInstance()->autoExecute(_DB_PREFIX_.'customer_ip', $insertIp, 'INSERT');
		}
	}
        
	/* Clean XML strings */
	private function _clean($string)
	{
		return preg_replace('/[^A-Za-z]/','',$string); 
	}
	
	/* Call Shopping Feed Webservices */
	private function _callWebService($call,$xml = false)
	{
		$token = Configuration::get('SHOPPING_FLUX_TOKEN');
		if (empty($token))
			return false;

		$service_url = 'https://clients.shopping-feed.com/webservice/';

		$curl_post_data = array(
			'TOKEN' => Configuration::get('SHOPPING_FLUX_TOKEN'), 
			'CALL' => $call, 
			'MODE'=> 'Production',
			'REQUEST' => $xml
		);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $service_url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		$curl_response = curl_exec($curl);
		
		curl_close($curl);

		return simplexml_load_string($curl_response);

	}
	
	private function _getOrderStates($id_lang)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT osl.name
			FROM `'._DB_PREFIX_.'order_state` os
			LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl
			ON (os.`id_order_state` = osl.`id_order_state`
			AND osl.`id_lang` = '.(int)$id_lang.')
			WHERE `template` = "Shipped"');
	}

	private function _getAddress($addressNode, $id_customer, $type)
	{
		$id_address = (int)Db::getInstance()->getValue('SELECT `id_address` 
			FROM `'._DB_PREFIX_.'address` WHERE `id_customer` = '.(int)$id_customer.' AND `alias` = \''.  pSQL($type) .'\'');

		if ($id_address)
			$address = new Address((int)$id_address);
		else
			$address = new Address();

		$customer = new Customer((int)$id_customer);

		$street1 = '' ;
		$street2 = '' ;
		$line2 = false;
		$streets = Explode (' ', (string)$addressNode->Street) ;

		foreach ($streets as $street)
		{
			if (strlen($street1) + strlen($street) + 1 < 32 && !$line2)
				$street1 .= $street.' ';
			else
			{
				$line2 = true;
				$street2 .= $street.' ';
			}
		}
		
		$address->id_customer = (int)$id_customer;
		$address->id_country = (int)Country::getByIso(trim($addressNode->Country));
		$address->alias = pSQL($type);
		$address->lastname = $customer->lastname;
		$address->firstname = $customer->firstname;
		$address->address1 = pSQL($street1);
		$address->address2 = pSQL($street2);
		$address->postcode = pSQL($addressNode->PostalCode);
		$address->city = pSQL($addressNode->Town);
		$address->phone = substr(pSQL($addressNode->Phone), 0, 16);

		if ($id_address)
			$address->update();
		else
			$address->add();

		return $address->id;
	}
	
	private function _getCustomer($email, $lastname, $firstname)
	{	
		$id_customer = (int)Db::getInstance()->getValue('SELECT `id_customer` 
			FROM `'._DB_PREFIX_.'customer` WHERE `email` = \''.pSQL($email).'\'');

		if ($id_customer)
			return $id_customer;

		$customer = new Customer();
		$customer->lastname = (!empty($lastname)) ? pSQL($lastname) : '-';
		$customer->firstname = (!empty($firstname)) ? pSQL($firstname) : '-';
		$customer->passwd = md5(pSQL(_COOKIE_KEY_.rand()));
		$customer->id_default_group = 1;
		$customer->email = pSQL($email);
		$customer->add();

		return $customer->id;
	}

	private function _updatePrices($id_order, $order, $reference_order)
	{
		$tax_rate = 0;

		foreach ($order->Products->Product as $product)
		{
			$skus = explode ('_', $product->SKU);
                        
			$row = Db::getInstance()->getRow('SELECT t.rate, od.id_order_detail  FROM '._DB_PREFIX_.'tax t
				LEFT JOIN '._DB_PREFIX_.'order_detail_tax odt ON t.id_tax = odt.id_tax
				LEFT JOIN '._DB_PREFIX_.'order_detail od ON odt.id_order_detail = od.id_order_detail
				WHERE od.id_order = '.(int)$id_order.' AND product_id = '.(int)$skus[0].' AND product_attribute_id = '.(int)$skus[1]);
                        
			$tax_rate = $row['rate'];
			$id_order_detail = $row['id_order_detail'];

			$updateOrderDetail = array(
				'product_price' => floatval((float)$product->Price / (1 + ($tax_rate / 100))),
				'reduction_percent' => 0,
				'reduction_amount' => 0,
				'total_price_tax_incl' => floatval($product->Price),
				'total_price_tax_excl' => floatval((float)$product->Price / (1 + ($tax_rate / 100))),
				'unit_price_tax_incl' => floatval($product->Price/$product->Quantity),
				'unit_price_tax_excl' => floatval((float)$product->Price / ((1 + ($tax_rate / 100))* $product->Quantity)),
			);

			Db::getInstance()->autoExecute(_DB_PREFIX_.'order_detail', $updateOrderDetail, 'UPDATE', '`id_order` = '.(int)$id_order.' AND `product_id` = '.(int)$skus[0].' AND `product_attribute_id` = '.(int)$skus[1]);

			$updateOrderDetailTax = array(
				'unit_amount' => floatval((float)$product->Price - ((float)$product->Price/(1 + ($tax_rate / 100)))),
				'total_amount' => floatval(((float)$product->Price - ((float)$product->Price/(1 + ($tax_rate / 100))))* $product->Quantity),
			);

			Db::getInstance()->autoExecute(_DB_PREFIX_.'order_detail_tax', $updateOrderDetailTax, 'UPDATE', '`id_order_detail` = '.(int)$id_order_detail);
		
		}
                
		$updateOrder = array(
			'total_paid' => floatval($order->TotalAmount),
			'total_paid_tax_incl' => floatval($order->TotalAmount),
			'total_paid_tax_excl' => floatval((float)$order->TotalAmount / (1 + ($tax_rate / 100))),
			'total_paid_real' => floatval($order->TotalAmount),
			'total_products' => floatval(Db::getInstance()->getValue('SELECT SUM(`product_price`) FROM `'._DB_PREFIX_.'order_detail` WHERE `id_order` = '.(int)$id_order)),
			'total_products_wt' => floatval($order->TotalProducts),
			'total_shipping' => floatval($order->TotalShipping),
			'total_shipping_tax_incl' => floatval($order->TotalShipping),
			'total_shipping_tax_excl' => floatval((float)$order->TotalShipping / (1 + ($tax_rate / 100))),
		);
		
		Db::getInstance()->autoExecute(_DB_PREFIX_.'orders', $updateOrder, 'UPDATE', '`id_order` = '.(int)$id_order);
                
		$updateOrderInvoice = array(
			'total_paid_tax_incl' => floatval($order->TotalAmount),
			'total_paid_tax_excl' => floatval((float)$order->TotalAmount / (1 + ($tax_rate / 100))),
			'total_products' => floatval(Db::getInstance()->getValue('SELECT SUM(`product_price`) FROM `'._DB_PREFIX_.'order_detail` WHERE `id_order` = '.(int)$id_order)),
			'total_products_wt' => floatval($order->TotalProducts),
			'total_shipping_tax_incl' => floatval($order->TotalShipping),
			'total_shipping_tax_excl' => floatval((float)$order->TotalShipping / (1 + ($tax_rate / 100))),
		);
                
		Db::getInstance()->autoExecute(_DB_PREFIX_.'order_invoice', $updateOrderInvoice, 'UPDATE', '`id_order` = '.(int)$id_order);
                
		$updatePayment = array(
			'amount' => floatval($order->TotalAmount),
		);
		
		Db::getInstance()->autoExecute(_DB_PREFIX_.'order_payment', $updatePayment, 'UPDATE', '`order_reference` = "'.$reference_order.'"');
                
	}

	private function _validateOrder($cart, $marketplace)
	{
		$payment = new SFeedPayment();
		$payment->name = 'SFeedPayment';
		$payment->active = true;

		//we need to flush the cart because of cache problems
		$cart->getPackageList(true);
                
		$payment->validateOrder(intval($cart->id), 2, floatval($cart->getOrderTotal()), $marketplace, NULL, array(), $cart->id_currency, false, $cart->secure_key);
		return $payment;
	}

	/*
	* Fake cart creation
	*/

	private function _getCart($id_customer, $id_address_billing, $id_address_shipping, $productsNode)
	{
		$cart = new Cart();
		$cart->id_customer = $id_customer;
		$cart->id_address_invoice = $id_address_billing;
		$cart->id_address_delivery = $id_address_shipping;
		$cart->id_currency = Currency::getIdByIsoCode('EUR');
		$cart->id_lang = Configuration::get('PS_LANG_DEFAULT');
		$cart->recyclable = 0;
		$cart->secure_key = md5(uniqid(rand(), true));
		$cart->id_carrier = (int)Configuration::get('PS_CARRIER_DEFAULT');
		$cart->add();

		foreach ($productsNode->Product as $product)
		{
			$skus = explode ('_', $product->SKU);
			if (!$cart->updateQty((int)($product->Quantity), (int)($skus[0]),((isset($skus[1])) ? $skus[1] : NULL)))
				return false;
		}
		
		$cart->update();

		return $cart;
	}

	private function _checkProducts($productsNode)
	{
		$available = true;

		foreach ($productsNode->Product as $product)
		{
			if (strpos($product->SKU, '_')!==false)
			{
				$skus = explode ('_', $product->SKU);
				$quantity = StockAvailable::getQuantityAvailableByProduct((int)$skus[0], (int)$skus[1]);

				if($quantity - $product->Quantity < 0)
					$available = false;
			}
			else
			{
				$quantity = StockAvailable::getQuantityAvailableByProduct((int)$product->SKU);
				
				if($quantity - $product->Quantity < 0)
					$available = false;
			}
		}

		return $available;
	}

	private function _validOrders($id_order, $marketplace)
	{
		$xml  = '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<ValidOrders>';
		$xml .= '<Order>';
		$xml .= '<IdOrder>'.$id_order.'</IdOrder>';
		$xml .= '<Marketplace>'.$marketplace.'</Marketplace>';
		$xml .= '</Order>';
		$xml .= '</ValidOrders>';

		$this->_callWebService('ValidOrders', $xml);
	}

	/* Liste Marketplaces SF */
	private function _getMarketplaces()
	{
		return array(
			"Amazon", 
			"Brandalley",
			"CDiscount",
			"eBay",
			"Facebook",
			"Fnac",
			"LaRedoute",
			"Pixmania",
			"PriceMinister",
			"RueDuCommerce"
		);
	}
}

class SFeedPayment extends PaymentModule {}
