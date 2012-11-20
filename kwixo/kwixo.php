<?php

class kwixo extends PaymentModule
{
	private	$_html = '';
	private $_postErrors = array();
	private $_postWarnings = array();

	// category defined by Receive&Pay
	private $categories = array(
		1 => 'Alimentation & gastronomie',
		2 => 'Auto & moto',
		3 => 'Culture & divertissements',
		4 => 'Maison & jardin',
		5 => 'Electroménager',
		6 => 'Enchères et achats groupés',
		7 => 'Fleurs & cadeaux',
		8 => 'Informatique & logiciels',
		9 => 'Santé & beauté',
		10 => 'Services aux particuliers',
		11 => 'Services aux professionnels',
		12 => 'Sport',
		13 => 'Vêtements & accessoires',
		14 => 'Voyage & tourisme',
		15 => 'Hifi, photo & vidéos',
		16 => 'Téléphonie & communication',
		17 => 'Bijoux et métaux précieux',
		18 => 'Articles et accessoires pour bébé',
		19 => 'Sonorisation & lumière'
	);

	// return values defined by Receive&Pay
	private $tags = array(
		// Zone Paiement
		0 => 'Commande avortée',
		1 => 'OK Commande acceptée validée',
		2 => 'KO Commande refusée (fraude)',
		3 => 'SU Commande sous surveillance FIA-NET',
		// Zone Surveillance (si Tag vaut 3)
		10 => 'Surveillance OK, la commande est "libérée"',
		11 => 'Surveillance KO, la commande est annulée',
		// Zone Livraison
		100 => 'OK Clôture de la transaction (livraison)',
		101 => 'KO Annulation de la transaction'
	);

	private $_carrier_type = array(
		1 => 'Retrait de la marchandise chez le marchand',
		2 =>'Utilisation d\'un réseau de points-retrait tiers (type kiala, alveol, etc.)',
		3 => 'Retrait dans un aéroport, une gare ou une agence de voyage',
		4 => 'Transporteur (La Poste, Colissimo, UPS, DHL... ou tout transporteur privé)',
		5 => 'Emission d’un billet électronique, téléchargements'
	);

	public function __construct()
	{
		$this->name = 'kwixo';
		$this->version = '3.1.3';
		$this->module_key = 'c862ef8e35f9a224119c567e2710bb5a';

		if (preg_match("/1\.4/", _PS_VERSION_))
			$this->tab = 'payments_gateways';
		else
        	$this->tab = 'Payment';
		parent::__construct();

		$this->displayName = $this->l('Kwixo');
		$this->description = $this->l('Accepts payments by "Kwixo"');

		if (intval(Configuration::get('RNP_MERCHID')) == 0
				OR Configuration::get('RNP_CRYPTKEY')==NULL
				OR Configuration::get('RNP_DEFAULTCATEGORYID')==NULL)
			$this->warning = $this->l('MerchID, CryptKey and a Category must be configured in order to use this module correctly');
	}

	public function install()
	{
		Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'rnp_categories` (
		`id_category` int(10) unsigned NOT NULL auto_increment,
		`id_rnp` int(10) NOT NULL,
		PRIMARY KEY (`id_category`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8');
		Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'sac_carriers` (
		`id_carrier` int(11) NOT NULL,
		`id_sac_carrier` int(11) NOT NULL,
		PRIMARY KEY `id_carrier` (`id_carrier`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8');

		if (!Configuration::get('RNP_NBDELIVERYDAYS'))
			Configuration::updateValue('RNP_NBDELIVERYDAYS', 7);
		if (!Configuration::get('RNP_TYPE_DISPLAY'))
			Configuration::updateValue('RNP_TYPE_DISPLAY', 1);

		if (!parent::install() OR !$this->registerHook('payment') OR !$this->registerHook('paymentReturn') OR !$this->registerHook('adminOrder') OR !$this->registerHook('rightColumn'))
			return false;

		$orderState = new OrderState();
		$langs = Language::getLanguages();
		foreach ($langs AS $lang)
			$orderState->name[$lang['id_lang']] = 'Waiting FIA-NET validation';
		$orderState->name[2] = 'Attente FIA-NET validation';
		$orderState->invoice = false;
		$orderState->send_email = false;
		$orderState->logable = false;
		$orderState->color = '#3333FF';
		$orderState->save();
		Configuration::updateValue('RNP_ID_ORDERSTATE', intval($orderState->id));
		
		return true;
	}

	public function uninstall()
	{
		Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'rnp_categories');
		Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'sac_carriers');

		return parent::uninstall();
	}

	private static function getCarrierType($id_carrier)
	{
		$carriers = array(
			1 => 4,
			2 => 2,
			3 => 4,
			4 => 4,
			5 => 2,
			9 => 2
		);
		$carrier_type = $carrier[Db::getInstance()->GetValue('SELECT id_sac_carrier FROM '._DB_PREFIX_.'sac_carriers WHERE id_carrier ='.(int)$id_carrier)];
		return ($carrier_type ? $carrier_type : 4);
	}

	public function duplicateCart()
	{
		global $cart;
		if (method_exists('Cart', 'duplicate'))
		{
			$arr = $cart->duplicate();
			return $arr['cart']->id;
		}
		else
			return self::_duplicateCart(intval($cart->id));
	}

	private function _duplicateCart($id_cart)
	{
		$cart = new Cart(intval($id_cart));
		if (!$cart->id OR $cart->id == 0)
			return false;
		$db = Db::getInstance();
		$cart->id = 0;
		$cart->save();
		if (!$cart->id OR $cart->id == 0 OR $cart->id == $id_cart)
			return false;

		/* Products */
		$products = $db->ExecuteS('
		SELECT id_product, id_product_attribute, quantity, date_add
		FROM '._DB_PREFIX_.'cart_product
		WHERE id_cart='.intval($id_cart));
		$sql = 'INSERT INTO '._DB_PREFIX_.'cart_product(id_cart, id_product, id_product_attribute, quantity, date_add) VALUES ';
		if ($products)
		{
			foreach ($products AS $product)
				$sql .= '('.intval($cart->id).','.intval($product['id_product']).', '.intval($product['id_product_attribute']).', '.intval($product['quantity']).', \''.pSQL($product['date_add']).'\'),';
			$db->Execute(rtrim($sql, ','));
		}

		/* Customization */
		$customs = $db->ExecuteS('
		SELECT c.id_customization, c.id_product_attribute, c.id_product, c.quantity, cd.type, cd.index, cd.value
		FROM '._DB_PREFIX_.'customization c
		JOIN '._DB_PREFIX_.'customized_data cd ON (cd.id_customization = c.id_customization)
		WHERE c.id_cart='.intval($id_cart));

		$custom_ids = array();
		$sql_custom_data = 'INSERT INTO '._DB_PREFIX_.'customized_data (id_customization, type, index, value) VALUES ';
		if ($customs)
		{
			foreach ($customs AS $custom)
			{
				$db->Execute('INSERT INTO '._DB_PREFIX_.'customization (id_customization, id_cart, id_product_attribute, id_product, quantity)
								VALUES(\'\', '.intval($cart->id).', '.intval($custom['id_product_attribute']).', '.intval($custom['id_product']).', '.intval($custom['quantity']).')');
				$custom_ids[$custom['id_customization']] = $db->Insert_ID();
			}

			foreach ($customs AS $custom)
				$sql_custom_data .= '('.intval($custom_ids[$custom['id_customization']]).', '.intval($custom['type']).', '.intval($custom['index']).', \''.pSQL($custom['value']).'\'),';

			$db->Execute($sql_custom_data);
		}
		
		/* Discounts */
		$discounts = $db->ExecuteS('SELECT id_discount FROM '._DB_PREFIX_.'cart_discount WHERE id_cart='.intval($id_cart));
		if ($discounts)
		{
			$sql = 'INSERT INTO '._DB_PREFIX_.'cart_discount(id_cart, id_discount) VALUES ';
			foreach ($discounts AS $discount)
				$sql .= '('.intval($cart->id).', '.intval($discount['id_discount']).'),';
			$db->Execute(rtrim($sql, ','));
		}
		return $cart->id;
	}
	
	public function getContent()
	{
		global $cookie;
		$this->_html = '<h2><img src="../modules/kwixo/logo_kwixo.png" alt="Kwixo"/> <img src="../modules/kwixo/base_line.png" alt="Le paiement so quick"/></h2>';

		if (isset($_POST['submitReceiveAndPay']))
		{
			if (empty($_POST['merchid']))
				$this->_postErrors[] = $this->l('Kwixo "merch id" is required.');
			elseif (!Validate::isInt($_POST['merchid']) && !Validate::isInt($_POST['merchidtest']))
				$this->_postErrors[] = $this->l('Kwixo merch id must be a positive number.');

			if (empty($_POST['cryptkey']))
				$this->_postErrors[] = $this->l('Kwixo "crypt key" is required.');

			if ($_POST['category_id']==0)
				$this->_postErrors[] = $this->l('Kwixo "category" is required.');

			if (empty($_POST['nb_delivery_days']) OR !is_numeric($_POST['nb_delivery_days']))
				$this->_postErrors[] = $this->l('Kwixo "number of delivery days" is required and must be a number.');

			if (!sizeof($this->_postErrors))
			{
				Configuration::updateValue('RNP_MERCHID', intval($_POST['merchid']));
				Configuration::updateValue('RNP_TYPE_DISPLAY', implode(',', $_POST['typeihm']));
				Configuration::updateValue('RNP_DEFAULTCATEGORYID', intval($_POST['category_id']));
				Configuration::updateValue('RNP_CRYPTKEY', $_POST['cryptkey']);
				Configuration::updateValue('RNP_NBDELIVERYDAYS', $_POST['nb_delivery_days']);
				$availability = isset($_POST['rnp_availability']) ? 1 : 0;
				Configuration::updateValue('RNP_AVAILABILITY', $availability);
				$production = (int)($_POST['rnp_production']);
				Configuration::updateValue('RNP_PRODUCTION', $production);
				Db::getInstance()->Execute('TRUNCATE TABLE '._DB_PREFIX_.'rnp_categories');
				//Db::getInstance()->Execute('TRUNCATE TABLE '._DB_PREFIX_.'sac_carriers');
				
				$carriers = Carrier::getCarriers($cookie->id_lang, false, false, false, NULL, false);
				foreach ($carriers as $carrier) {
					if (isset($_POST['carrier_'.$carrier['id_carrier']]))
						Configuration::updateValue('RNP_CARRIER_TYPE_'.$carrier['id_carrier'], Tools::getValue('carrier_'.$carrier['id_carrier']));
					else
						$this->_html .= '<div class="alert error">'.$this->l('Invalid carrier code').'</div>';
				}
				
				
				$categories = Category::getSimpleCategories($cookie->id_lang);
				foreach ($categories as $categorie) {
					if (isset($_POST['cat_'.$categorie['id_category']]))
						Configuration::updateValue('RNP_CAT_TYPE_'.$categorie['id_category'], Tools::getValue('cat_'.$categorie['id_category']));
					else
						$this->_html .= '<div class="alert error">'.$this->l('Invalid categorie code').'</div>';
				}
				$production = (int)(array_key_exists('rnp_production', $_POST) ? $_POST['rnp_production'] : (array_key_exists('RNP_PRODUCTION', $conf) ? $conf['RNP_PRODUCTION'] : ''));
				if ($production == 0)
					{
						$this->_postWarning[] = $this->l('Test environment is selected : Kwixo is not in relation with your bank. Customers are able to pay but you won\'t receive the money.');
						$this->displayWarnings();
					}
				else
					$this->displayConf();
			}
			else
				$this->displayErrors();
		}
		$this->displayFormSettings();
		$this->displayInformations();

		return $this->_html;
	}

	public function displayInformations()
	{
		$url = 'https://business.kwixo.com/merchantbo/login.htm'; 

		$this->_html.= '<br /><br />
		<fieldset><legend>'.$this->l('Manage your payments in your Kwixo administration interface').'</legend>
			<p>'.$this->l('Your administration interface').' :&nbsp;<a href="'.$url.'" target="_blank" style="color:blue;text-decoration:underline">'.$url.'</a></p><br/>
			<p>'.$this->l('The Kwixo administration interface enables you to manage your payments : follow up, cancellation, refoundation').'</p>
			<!--<p><b>'.$this->l('Return URL').'</b>:&nbsp;http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/'.$this->name.'/push.php</p>-->
		</fieldset>
		<div class="clear">&nbsp;</div>
		<!--<fieldset>
			<legend>PrestaShop Addons</legend>
			'.$this->l('This module has been developped by PrestaShop SA and can only be sold through').' <a href="http://addons.prestashop.com">addons.prestashop.com</a>.<br />
			'.$this->l('Please report all bugs to').' <a href="mailto:addons@prestashop.com">addons@prestashop.com</a> '.$this->l('or using our').' <a href="http://addons.prestashop.com/contact-form.php">'.$this->l('contact form').'</a>.
		</fieldset>-->';
	}

	public function displayConf()
	{
		$this->_html .= '
		<div class="conf confirm">
			<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
			'.$this->l('Settings updated').'
		</div>';
	}

	public function displayErrors()
	{
		$nbErrors = sizeof($this->_postErrors);
		$this->_html .= '
		<div class="error">
			<h3>'.($nbErrors > 1 ? $this->l('There are') : $this->l('There is')).' '.$nbErrors.' '.($nbErrors > 1 ? $this->l('errors') : $this->l('error')).'</h3>
			<ul>';
		foreach ($this->_postErrors AS $error)
			$this->_html .= '<li>'.$error.'</li>';
		$this->_html .= '
			</ul>
		</div>';
	}
	
	public function displayWarnings()
	{
		$nbWarnings = sizeof($this->_postWarning);
		$this->_html .= '
		<div class="warn">
			<h3>'.($nbWarnings > 1 ? $this->l('There are') : $this->l('There is')).' '.$nbWarnings.' '.($nbWarnings > 1 ? $this->l('warnings') : $this->l('warning')).'</h3>
			<ul>';
		foreach ($this->_postWarning AS $warning)
			$this->_html .= '<li>'.$warning.'</li>';
		$this->_html .= '
			</ul>
		</div>';
	}

	public function getSACCarriers()
	{
		$carriers = Db::getInstance()->ExecuteS('SELECT id_carrier, id_sac_carrier FROM '._DB_PREFIX_.'sac_carriers');
		$sac_carrier = array();
		foreach ($carriers AS $carrier)
			$sac_carrier[$carrier['id_carrier']] = $carrier['id_sac_carrier'];
		return $sac_carrier;
	}

	public function getRNPCategories()
	{
		$categories = Db::getInstance()->ExecuteS('SELECT id_category, id_rnp FROM '._DB_PREFIX_.'rnp_categories');
		$rnp_cat = array();
		if ($categories)
			foreach ($categories AS $category)
				$rnp_cat[$category['id_category']] = $category['id_rnp'];
		return $rnp_cat;
	}
	public function displayFormSettings()
	{
		$conf = Configuration::getMultiple(array('RNP_MERCHID', 'RNP_TYPE_DISPLAY', 'RNP_CRYPTKEY', 'RNP_DEFAULTCATEGORYID', 'RNP_NBDELIVERYDAYS', 'RNP_AVAILABILITY', 'RNP_PRODUCTION'));
		$production = array_key_exists('rnp_production', $_POST) ? $_POST['rnp_production'] : (array_key_exists('RNP_PRODUCTION', $conf) ? $conf['RNP_PRODUCTION'] : '');
		$merchid = array_key_exists('merchid', $_POST) ? $_POST['merchid'] : (array_key_exists('RNP_MERCHID', $conf) ? $conf['RNP_MERCHID'] : '');
		$merchidtest = array_key_exists('merchidtest', $_POST) ? $_POST['merchidtest'] : (array_key_exists('RNP_MERCHIDTEST', $conf) ? $conf['RNP_MERCHIDTEST'] : '');
		$typeihm = array_key_exists('typeihm', $_POST) ? $_POST['typeihm'] : (array_key_exists('RNP_TYPE_DISPLAY', $conf) ? explode("," , $conf['RNP_TYPE_DISPLAY']) : '');
		$cryptkey = array_key_exists('cryptkey', $_POST) ? $_POST['cryptkey'] : (array_key_exists('RNP_CRYPTKEY', $conf) ? $conf['RNP_CRYPTKEY'] : '');
		$cryptkeytest = array_key_exists('cryptkeytest', $_POST) ? $_POST['cryptkeytest'] : (array_key_exists('RNP_CRYPTKEYTEST', $conf) ? $conf['RNP_CRYPTKEYTEST'] : '');
		$category_id = array_key_exists('category_id', $_POST) ? $_POST['category_id'] : (array_key_exists('RNP_DEFAULTCATEGORYID', $conf) ? $conf['RNP_DEFAULTCATEGORYID'] : '');
		$availability = array_key_exists('rnp_availability', $_POST) ? $_POST['rnp_availability'] : (array_key_exists('RNP_AVAILABILITY', $conf) ? $conf['RNP_AVAILABILITY'] : '');
		$nb_delivery_days = array_key_exists('nb_delivery_days', $_POST) ? $_POST['nb_delivery_days'] : (array_key_exists('RNP_NBDELIVERYDAYS', $conf) ? $conf['RNP_NBDELIVERYDAYS'] : '');
		$id_lang = Configuration::get('PS_LANG_DEFAULT');
		$categories = Category::getSimpleCategories($id_lang);
		$carriers = Carrier::getCarriers($id_lang, false, false, false, NULL, false);
		$rnp_categories = $this->getRNPCategories();
		$this->_html .= '
		<fieldset><legend>'.$this->l('Settings "Kwixo"').'</legend>
		<p>'.$this->l('The following parameters were provided to you by FIA-NET / KWIXO').'. '.$this->l('If you are not yet registered, click ').' <a style="color:blue;text-decoration:underline" href="http://recette.kwixo.com/homebo/index.htm">'.$this->l('here').'</a>
		<!--<a href="http://recette.kwixo.com/homebo/index.htm"><img src="../modules/kwixo/logoKwixo_Cr_V.png" alt="logo" style="width:100px"/></a>-->
		<br/><br/></p>
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<label for="rnp_production">'.$this->l('Environment').'</label>
			<div class="margin-form">
				<!--<input type="checkbox" value="1" id="rnp_production" name="rnp_production" '.($production == 1 ? 'checked="checked"' : '').' />-->
				<input type="radio" value="1" name="rnp_production" '.($production == 1 ? 'checked="checked"' : '').' /> <label class="t">'.$this->l('Production').'</label> '.$this->l('(in this mode, you will receive real payments)').'<br/>
				<input type="radio" value="0" name="rnp_production" '.($production == 0 ? 'checked="checked"' : '').' /> <label class="t">'.$this->l('Test').'</label> '.$this->l('(this mode only allows you to test if this Kwixo module is working well but you won\'t receive payments)').'
			</div>
			<label for="merchid">'.$this->l('Merch Id').'</label>
			<div class="margin-form"><input type="text" name="merchid" id="merchid" value="'.Tools::safeOutput($merchid).'" size="10" /></div>
			<label for="cryptkey">'.$this->l('Crypt key').'</label>
			<div class="margin-form"><input type="text" name="cryptkey" id="cryptkey" value="'.Tools::safeOutput($cryptkey).'" size="40" /></div>
			<br />
			<label>'.$this->l('Type of payment accepted').'</label>
			<div class="margin-form">
				<input type="checkbox" name="typeihm[]" id="typeihm1" value="1" '.(in_array('1', $typeihm)  ? 'checked="checked"' : '').' />
				<label for="typeihm1" class="t">'.$this->l('Comptant').'</label>
				<br/>
				<input type="checkbox" name="typeihm[]" id="typeihm2" value="2" '.(in_array('2', $typeihm) ? 'checked="checked"' : '').' />
				<label for="typeihm2" class="t">'.$this->l('Crédit').'</label>
				<br/>
				<input type="checkbox" name="typeihm[]" id="typeihm2" value="3" '.(in_array('3', $typeihm) ? 'checked="checked"' : '').' />
				<label for="typeihm2" class="t">'.$this->l('Standard').'</label>
				<br/>
			</div>
			<label for="nb_delivery_days">'.$this->l('Delay for delivery').'</label>
			<div class="margin-form"><input type="text" name="nb_delivery_days" id="nb_delivery_days" value="'.Tools::safeOutput($nb_delivery_days).'" size="5" /> '.$this->l('days').'</div>
			</fieldset>
		<!--<br/>
				<fieldset><legend>'.$this->l('Test account').'</legend>
					<label for="merchidtest">'.$this->l('Merch Id').'</label>
					<div class="margin-form"><input type="text" name="merchidtest" id="merchidtest" value="'.Tools::safeOutput($merchidtest).'" size="10" /></div>
					<label for="cryptkeytest">'.$this->l('Crypt key').'</label>
					<div class="margin-form"><input type="text" name="cryptkeytest" id="cryptkeytest" value="'.Tools::safeOutput($cryptkeytest).'" size="40" /></div>
				</fieldset>-->
			<br/>
		<fieldset>
			<legend>'.$this->l('Information on products sold on your shop').'</legend>
			<p>'.$this->l('For a better assistance, Kwixo needs to know what are the main product types you sell').' :</p><br/>
			<label for="category_id">'.$this->l('Default product type').'</label>
			<div class="margin-form">
				<select name="category_id" id="category_id">
					<option value="0">'.$this->l('Choose a type...').'</option>';
			$html_category = '';
			foreach ($this->categories AS $id => $category)
			{
				$this->_html .= '<option value="'.$id.'"';
				if ($category_id==$id) $this->_html.= ' selected="selected"';
					$this->_html .= '>'.$category.'</option>';
			}
			$this->_html .= '
				</select> <span style="color:red">*</span>
				</div>';
			foreach ($categories AS $category)
			{
				$this->_html .= '
				<label for="cat_'.$category['id_category'].'">'.$category['name'].'</label>
				<div class="margin-form">
					<select name="cat_'.$category['id_category'].'" id="cat_'.$category['id_category'].'">
						<option value="0">'.$this->l('Choose a type...').'</option>';
				foreach ($this->categories AS $id => $cat)
				{
				$this->_html .= '<option value="'.$id.'"'.((Configuration::get('RNP_CAT_TYPE_'.$category['id_category']) == $id) ? ' selected="selected"' : '').'>'.$cat.'</option>';
				}
				$this->_html .= '</select></div><div class="clear"></div>';
			}
			$this->_html .= '
			</fieldset>
			<br class="clear" />
			<fieldset><legend>'.$this->l('Carrier Configuration').'</legend>
			<p>'.$this->l('Please select a carrier type for each carrier use on your shop').' :</p><br/>
				<label>'.$this->l('Carrier Detail').'</label>
				<div class="margin-form">
					<table cellspacing="0" cellpadding="0" class="table">
						<thead><tr><th>'.$this->l('Carrier').'</th><th>'.$this->l('Carrier Type').'</th></tr></thead><tbody>';
		foreach ($carriers AS $carrier)
		{
			$this->_html .= '<tr><td>'.$carrier['name'].'</td><td><select name="carrier_'.$carrier['id_carrier'].'" id="carrier_'.$carrier['id_carrier'].'">
			<option value="0">'.$this->l('Choose a carrier type...').'</option>';
			foreach ($this->_carrier_type AS $k => $type)
				$this->_html .= '<option value="'.$k.'"'.((Configuration::get('RNP_CARRIER_TYPE_'.$carrier['id_carrier']) == $k) ? ' selected="selected"' : '').'>'.$type.'</option>';
			$this->_html .= '</select></td>';
		}
			$this->_html .= '</tbody></table></margin>
			</fieldset><br class="clear" />
			<div class="center">
				<input type="submit" name="submitReceiveAndPay" value="'.$this->l('Update settings').'" class="button" />
			</div>
		</form>';
	}
	function getHashKwixo($refid, $montant_total, $email, $nom)
	{
		if (!class_exists('HashMD5', false))
			require_once(dirname(__FILE__).'/fianet_key_64bits.php'); 
		$MD5 = new HashMD5();
		$modulo = date("s") % 4;   
		switch($modulo) {
			case 0:
				$select = $montant_total;
			break;
			case 1:
				$select = $email;
			break;   
				case 2:
					$select = $refid;
			break;   
			case 3:
				$select = $nom;
			break;
			default:
			break;
		}
		$crypt = $MD5->hash(Configuration::get('RNP_CRYPTKEY').$refid.$montant_total.$email.$select);
		return $crypt;
	}
	
	function clean_xml($xml)
	{
		
		$xml = str_replace("\\'", "'", $xml);
	
		$xml = str_replace("\\\"", "\"", $xml);
		
		$xml = str_replace("\\\\", "\\", $xml);
		
		$xml = str_replace("\t", "", $xml);
		
		$xml = str_replace("\n", "", $xml);
		
		$xml = str_replace("\r", "", $xml);
		
		$xml = trim($xml);
		return ($xml);
	
	}
	function get_delivery_date($delivery_times)
	{
		$date =  mktime(0,0,0,date("m" ), date("d" ) + $delivery_times ,date("Y" ));
		return (date("Y-m-d", $date));
	}

	public function hookPayment($params)
	{
		global $smarty, $cookie;
		if (Configuration::get('RNP_CHECK_AVAILABILITY') AND !$this->checkAvailibility())
			return false;

		$cookie->rnp_payment = true;
		$total_cart = $params['cart']->getOrderTotal(true);
		$type_display = Configuration::get('RNP_TYPE_DISPLAY');
		$type = explode(",", $type_display);
		$smarty->assign('comptant', (in_array("1", $type)));
		if ($total_cart >= 150 AND $total_cart <= 4000)
			$smarty->assign('credit', (in_array("2", $type)));
		$smarty->assign('direct', (in_array("3", $type)));
		return $this->display(__FILE__, 'kwixo.tpl');
	}
	
	public function hookRightColumn($params)
	{
		global $smarty;
		$smarty->assign('path', 'modules/'.$this->name);
		return $this->display(__FILE__, 'logo.tpl');
	}
	
	public function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}
	
	function hookPaymentReturn()
	{
		global $smarty;

		if ($params['objOrder']->module != $this->name)
			return;
		
		if (Tools::getValue('error'))
			$smarty->assign('status', 'failed');
		else
			$smarty->assign('status', 'ok');
		return $this->display(__FILE__, 'payment_return.tpl');
  }

}

?>
