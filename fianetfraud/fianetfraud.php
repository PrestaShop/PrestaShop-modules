<?php

/*
 * 2007-2010 PrestaShop
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
 *  @author Prestashop SA <contact@prestashop.com>
 *  @copyright  2007-2010 Prestashop SA
 *  @version  Release: $Revision: 1.4 $
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registred Trademark & Property of PrestaShop SA
 */

require_once _PS_ROOT_DIR_.'/modules/fianetfraud/fianet/lib/includes/includes.inc.php';

class Fianetfraud extends Module
{

  const CERTISSIM_TABLE_NAME = 'fianet_certissim';
  const INSTALL_SQL_FILE = 'install.sql';

  static private $token;
  private $_html;
  private $_product_type = array(
    '1' => 'Alimentation & gastronomie',
    '2' => 'Auto & moto',
    '3' => 'Culture & divertissements',
    '4' => 'Maison & jardin',
    '5' => 'Electroménager',
    '6' => 'Enchères et achats groupés',
    '7' => 'Fleurs & cadeaux',
    '8' => 'Informatique & logiciels',
    '9' => 'Santé & beauté',
    '10' => 'Services aux particuliers',
    '11' => 'Services aux professionnels',
    '12' => 'Sport',
    '13' => 'Vêtements & accessoires',
    '14' => 'Voyage & tourisme',
    '15' => 'Hifi, photo & videos',
    '16' => 'Téléphonie & communication',
    '17' => 'Bijoux & Métaux précieux',
    '18' => 'Articles et Accessoires pour bébé',
    '19' => 'Sonorisation & Lumière',
  );
  private $_carrier_type = array(
    '1' => 'Retrait de la marchandise chez le marchand',
    '2' => 'Utilisation d\'un réseau de points-retrait tiers (type kiala, alveol, etc.)',
    '3' => 'Retrait dans un aéroport, une gare ou une agence de voyage',
    '4' => 'Transporteur (La Poste, Colissimo, UPS, DHL... ou tout transporteur privé)',
    '5' => 'Emission d\'un billet électronique, téléchargements',
  );
  private $_payement_type = array(
    1 => 'carte',
    2 => 'cheque',
    3 => 'contre-remboursement',
    4 => 'virement',
    5 => 'cb en n fois',
    6 => 'paypal',
    7 => '1euro.com',
    8 => 'buyster',
    9 => 'bybox',
  );

  public function __construct()
  {
    $this->name = 'fianetfraud';
    $this->author = 'ESPIAU Nicolas @ Fia-Net';
    $this->tab = 'payment_security';
    $this->version = '2.2.5';
    $this->limited_countries = array('fr');

    parent::__construct();

    $this->displayName = 'FIA-NET - Certissim';
    $this->description = "Prot&eacute;gez vous contre la fraude &agrave; la carte bancaire sans perturber l'acte d'achat";
    self::$token = sha1('fianetfraud'._COOKIE_KEY_.'token');
  }

  public function install()
  {
    CertissimTools::insertLog(__METHOD__." : ".__LINE__, "Installation du module");
    if (!parent::install())
      return false;

    if (!file_exists(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
      return false;
    elseif (!$sql = file_get_contents(dirname(__FILE__).'/'.self::INSTALL_SQL_FILE))
      return false;
    $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
    $sql = preg_split("/;\s*[\r\n]+/", $sql);
    foreach ($sql as $query)
      if ($query and sizeof($query) and !Db::getInstance()->Execute(trim($query)))
        return false;
    $langs = Language::getLanguages();

    $sac = new CertissimSac();
    $sac->setStatus('test');
    $sac->saveParamInFile();

    //cleans old order states
    $orderStateFraud = new OrderState((int) Configuration::get('SAC_ID_FRAUD'), (int) Configuration::get('PS_LANG_DEFAULT'));
    $orderStateValid = new OrderState((int) Configuration::get('SAC_ID_VALID'), (int) Configuration::get('PS_LANG_DEFAULT'));
    $orderStateWaiting = new OrderState((int) Configuration::get('SAC_ID_WAITING'), (int) Configuration::get('PS_LANG_DEFAULT'));
    $orderStateTest = new OrderState((int) Configuration::get('SAC_ID_UNKNOWN'), (int) Configuration::get('PS_LANG_DEFAULT'));

    $orderStateFraud->delete();
    $orderStateValid->delete();
    $orderStateWaiting->delete();
    $orderStateTest->delete();


    //database connection test
    $bddwritable = Db::getInstance()->Execute("
			INSERT INTO `"._DB_PREFIX_.self::CERTISSIM_TABLE_NAME."` (`id_order`, `ip_address`, `date`)
			VALUES ('0', '".pSQL(self::getRemoteAddr())."','".pSQL(date('Y-m-d H:i:s'))."')");

    //log
    CertissimTools::insertLog(__METHOD__." : ".__LINE__, (bool) $bddwritable ? "Database is writable" : "Database is not writable");

    //cleans the database test entry if success
    if ($bddwritable)
    {
      Db::getInstance()->Execute("
			DELETE FROM `"._DB_PREFIX_.self::CERTISSIM_TABLE_NAME."`
			WHERE  `id_order`='0'");
    }

    //tests the connection to the Fia-Net's server
    $emptystack = new CertissimXMLElement('<stack></stack>');
    $sslres = $sac->sendStacking($emptystack);
    //log
    CertissimTools::insertLog(__METHOD__." : ".__LINE__, (bool) $sslres ? "Connexion au serveur Fia-Net réussie" : "Connexion au serveur Fia-Net impossible");


    if (!$this->registerHook('updateCarrier'))
      return false;
    if (!Configuration::updateValue('SAC_SITEID', '') or
      !Configuration::updateValue('SAC_LOGIN', '') or
      !Configuration::updateValue('SAC_PASSWORD', '') or
      !Configuration::updateValue('SAC_MINIMAL_ORDER', 0))
      return false;

    return ($this->registerHook('paymentConfirm') and
      $this->registerHook('paymentReturn') and
      $this->registerHook('newOrder') and
      $this->registerHook('adminOrder') and
      $this->registerHook('updateOrderStatus')
      );
  }

  public function uninstall()
  {
    return parent::uninstall();
  }

  private function _postProcess()
  {
    global $cookie;

    $error = false;

    Configuration::updateValue('SAC_PRODUCTION', ((Tools::getValue('fianetfraud_production') == 1 ) ? 1 : 0));
    $sac = new CertissimSac();
    $sac->switchMode(((Tools::getValue('fianetfraud_production') == 1 ) ? "prod" : "test"));

    Configuration::updateValue('SAC_LOGIN', Tools::getValue('fianetfraud_login'));
    $sac->setLogin(Tools::getValue('fianetfraud_login'));

    Configuration::updateValue('SAC_PASSWORD', Tools::getValue('fianetfraud_password'));
    $sac->setPassword(Tools::getValue('fianetfraud_password'));
    $sac->setPasswordurlencoded(urlencode(Tools::getValue('fianetfraud_password')));

    Configuration::updateValue('SAC_SITEID', Tools::getValue('fianetfraud_siteid'));
    $sac->setSiteid(Tools::getValue('fianetfraud_siteid'));

    Configuration::updateValue('SAC_DEFAULT_PRODUCT_TYPE', Tools::getValue('fianetfraud_product_type'));

    Configuration::updateValue('SAC_DEFAULT_CARRIER_TYPE', Tools::getValue('fianetfraud_default_carrier'));

    Configuration::updateValue('SAC_MINIMAL_ORDER', Tools::getValue('fianetfraud_minimal_order'));

    $sac->saveParamInFile();

    if (Tools::getValue('payementBox'))
    {
      Configuration::updateValue('SAC_PAYMENT_MODULE', implode(',', Tools::getValue('payementBox')));
      foreach (Tools::getValue('payementBox') as $payment)
        Configuration::updateValue('SAC_PAYMENT_TYPE_'.$payment, Tools::getValue($payment));
    }

    $categories = Category::getSimpleCategories($cookie->id_lang);
    foreach ($categories as $category)
      Configuration::updateValue('SAC_CATEGORY_TYPE_'.(int) $category['id_category'], Tools::getValue('cat_'.(int) $category['id_category']));

    $id_lang = Configuration::get('PS_LANG_DEFAULT');

    $carriers = Carrier::getCarriers($id_lang, false, false, false, NULL, false);
    foreach ($carriers as $carrier) {
      if (Tools::getValue('carrier_'.(int) $carrier['id_carrier']))
        Configuration::updateValue('SAC_CARRIER_TYPE_'.(int) $carrier['id_carrier'], Tools::getValue('carrier_'.(int) $carrier['id_carrier']));
      else
      {
        $error = true;
        $this->_html .= '<div class="alert error">'.$this->l('Invalid carrier code').'</div>';
      }
    }

    if (!$error)
    {
      $dataSync = ((($site_id = Configuration::get('SAC_SITEID')) and Configuration::get('SAC_PRODUCTION')) ? '<img src="http://www.prestashop.com/modules/fianetfraud.png?site_id='.urlencode($site_id).'" style="float:right" />' : ''
        );
      $this->_html .= '<div class="conf confirm">'.$this->l('Settings are updated').$dataSync.'</div>';
    }
  }

  public function getContent()
  {
    if (Tools::isSubmit('submitSettings'))
      $this->_postProcess();

    $id_lang = Configuration::get('PS_LANG_DEFAULT');
    $categories = Category::getSimpleCategories($id_lang);

    $carriers = Carrier::getCarriers($id_lang, false, false, false, NULL, false);

    $this->_html .= '<div class="warning">'.$this->l('Phone number is required').'</div>
		<fieldset><legend>FIA-NET - Système d\'Analyse des Commandes</legend>
			<img src="../modules/'.$this->name.'/logo.jpg" style="float:right;margin:5px 10px 5px 0" />
			FIA-NET, le leader fran&ccedil;ais de la lutte contre la fraude à la carte bancaire sur internet !<br /><br />
            Avec son réseau mutualisé de plus de 1 700 sites marchands, et sa base de données de 14 millions de cyber-acheteurs, le Système d\'Analyse des Commandes vous offre une protection complète et unique contre le risque d\'impayé.<br /><br />
            Le logiciel expert (SAC) score vos transactions en quasi temps réel à partir de plus de 200 critères pour valider plus de 92 % de vos transactions.<br />
            Le contr&ocirc;le humain, prenant en charge les transactions les plus risqués, associé à l\'assurance FIA-NET vous permet de valider et garantir jusqu\'à 100 % de vos transactions.<br /><br />
            Ne restez pas isolé face à l\'explosion des réseaux de fraudeurs !
			<p>'.$this->l('To sign in, check out: ').' <u><a href="https://www.fia-net.com/marchands/devispartenaire.php?p=185" target="_blank">'.$this->l('Fia-net Website').'</a></u></p>
		</fieldset><br />
		<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
			<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Login').'</label>
				<div class="margin-form">
					<input type="text" name="fianetfraud_login" value="'.Tools::htmlentitiesUTF8(Configuration::get('SAC_LOGIN')).'"/>
				</div>
				<label>'.$this->l('Password').'</label>
				<div class="margin-form">
					<input type="text" name="fianetfraud_password" value="'.Tools::htmlentitiesUTF8(Configuration::get('SAC_PASSWORD')).'"/>
				</div>
				<label>'.$this->l('Site ID').'</label>
				<div class="margin-form">
					<input type="text" name="fianetfraud_siteid" value="'.Tools::htmlentitiesUTF8(Configuration::get('SAC_SITEID')).'"/>
				</div>
				<label>'.$this->l('Production mode').'</label>
				<div class="margin-form">
					<input type="checkbox" name="fianetfraud_production" id="activated_on" value="1" '.((Configuration::get('SAC_PRODUCTION') == 1) ? 'checked="checked" ' : '').'/>
				</div>
				<label>'.$this->l('Default Product Type').'</label>
				<div class="margin-form">
					<select name="fianetfraud_product_type">
						<option value="0">'.$this->l('-- Choose --').'</option>';
    foreach ($this->_product_type as $k => $product_type)
      $this->_html .= '<option value="'.Tools::safeOutput($k).'"'.(Configuration::get('SAC_DEFAULT_PRODUCT_TYPE') == $k ? ' selected="selected"' : '').'>'.Tools::safeOutput($product_type).'</option>';
    $this->_html .= '</select>
				</div>
			</fieldset><br />
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Category detail').'</legend>
			<label>'.$this->l('Category detail').'</label>
			<div class="margin-form">
			<table cellspacing="0" cellpadding="0" class="table">
						<thead><tr><th>'.$this->l('Category').'</th><th>'.$this->l('Category Type').'</th></tr></thead><tbody>';
    foreach ($categories as $category) {
      $this->_html .= '<tr><td>'.Tools::safeOutput($category['name']).'</td><td>
			<select name="cat_'.(int) $category['id_category'].'" id="cat_'.(int) $category['id_category'].'">
				<option value="0">'.$this->l('Choose a category...').'</option>';
      foreach ($this->_product_type as $id => $cat)
        $this->_html .= '<option value="'.$id.'" '.((Configuration::get('SAC_CATEGORY_TYPE_'.(int) $category['id_category']) == $id) ? ' selected="true"' : '').'>'.Tools::safeOutput($cat).'</option>';
      $this->_html .= '</select></td></tr>';
    }
    $this->_html .= '</tbody></table></div>
			</fieldset>
			<div class="clear">&nbsp;</div>
			<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Carrier Configuration').'</legend>
				<label>'.$this->l('Carrier Detail').'</label>
				<div class="margin-form">
					<table cellspacing="0" cellpadding="0" class="table">
						<thead><tr><th>'.$this->l('Carrier').'</th><th>'.$this->l('Carrier Type').'</th></tr></thead><tbody>';
    foreach ($carriers as $carrier) {
      $this->_html .= '<tr><td>'.Tools::safeOutput($carrier['name']).'</td><td><select name="carrier_'.(int) $carrier['id_carrier'].'" id="cat_'.(int) $carrier['id_carrier'].'">
			<option value="0">'.$this->l('Choose a carrier type...').'</option>';
      foreach ($this->_carrier_type as $id => $type)
        $this->_html .= '<option value="'.$id.'"'.((Configuration::get('SAC_CARRIER_TYPE_'.(int) $carrier['id_carrier']) == $id) ? ' selected="true"' : '').'>'.Tools::safeOutput($type).'</option>';
      $this->_html .= '</select></td>';
    }
    $this->_html .= '</tbody></table></margin>
			</div>
			<div class="clear">&nbsp;</div>
			<label>'.$this->l('Default Carrier Type').'</label>
			<div class="margin-form">
				<select name="fianetfraud_default_carrier">';
    foreach ($this->_carrier_type as $k => $type)
      $this->_html .= '<option value="'.Tools::safeOutput($k).'"'.($k == Configuration::get('SAC_DEFAULT_CARRIER_TYPE') ? ' selected' : '').'>'.Tools::safeOutput($type).'</option>';
    $this->_html .= '</select>
			</div>
			</fieldset><div class="clear">&nbsp;</div>';

    /* Get all modules then select only payment ones */
    $modules = Module::getModulesOnDisk();
    $modules_is_fianet = explode(',', Configuration::get('SAC_PAYMENT_MODULE'));
    $this->paymentModules = array();
    foreach ($modules as $module)
      if (method_exists($module, 'hookPayment'))
      {
        if ($module->id)
        {
          $module->country = array();
          $countries = DB::getInstance()->ExecuteS('SELECT id_country FROM '._DB_PREFIX_.'module_country WHERE id_module = '.(int) ($module->id));
          foreach ($countries as $country)
            $module->country[] = $country['id_country'];

          $module->currency = array();
          $currencies = DB::getInstance()->ExecuteS('SELECT id_currency FROM '._DB_PREFIX_.'module_currency WHERE id_module = '.(int) ($module->id));
          foreach ($currencies as $currency)
            $module->currency[] = $currency['id_currency'];

          $module->group = array();
          $groups = DB::getInstance()->ExecuteS('SELECT id_group FROM '._DB_PREFIX_.'module_group WHERE id_module = '.(int) ($module->id));
          foreach ($groups as $group)
            $module->group[] = $group['id_group'];
        }
        else
        {
          $module->country = NULL;
          $module->currency = NULL;
          $module->group = NULL;
        }
        $this->paymentModules[] = $module;
      }

    $this->_html .= '<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Payement Configuration').'</legend>
				<label>'.$this->l('Payement Detail').'</label>
				<div class="margin-form">
					<table cellspacing="0" cellpadding="0" class="table" ><thead><tr>
						<th><input type="checkbox" name="checkme" class="noborder" onclick="checkDelBoxes(this.form, \'payementBox[]\', this.checked)" /></th>
						<th>'.$this->l('Payement Module').'</th><th>'.$this->l('Payement Type').'</th></tr></thead><tbody>';

    foreach ($this->paymentModules as $module) {
      $this->_html .= '<tr><td><input type="checkbox" class="noborder" value="'.substr($module->name, 0, 15).'" name="payementBox[]" '.(in_array(substr($module->name, 0, 15), $modules_is_fianet) ? 'checked="checked"' : '').'></td>';
      $this->_html .= '<td><img src="'.__PS_BASE_URI__.'modules/'.$module->name.'/logo.gif" alt="'.$module->name.'" title="'.$module->displayName.'" />'.stripslashes($module->displayName).'</td><td><select name="'.substr($module->name, 0, 15).'">';
      $this->_html .= '<option value="0">'.$this->l('-- Choose --').'</option>';
      foreach ($this->_payement_type as $type)
        $this->_html .= '<option '.((Configuration::get('SAC_PAYMENT_TYPE_'.substr($module->name, 0, 15)) == $type) ? 'selected="true"' : '').'>'.Tools::safeOutput($type).'</option>';
      $this->_html .= '</select></tr>';
    }

    $this->_html .= '</tbody></table></margin></fieldset><br class="clear" /><br />
			<center><input type="submit" name="submitSettings" value="'.$this->l('Save').'" class="button" /></center><br />
			<center><a href="../modules/fianetfraud/fianet/logs/getlog.php?token='.sha1(_COOKIE_KEY_.'fianet').'">Télécharger les logs Fia-Net</a></center>
		</form>
		<div class="clear">&nbsp;</div>';
    return $this->_html;
  }

  /**
   * returns the IP address of the customer who paid the order $id_order
   *
   * @param int $id_order id of the order
   * @return string customer's IP address
   */
  static private function getIpByOrder($id_order = false)
  {
    if ($id_order == false)
      return false;
    return Db::getInstance()->getValue('
			SELECT `ip_address`
			FROM '._DB_PREFIX_.self::CERTISSIM_TABLE_NAME.'
			WHERE id_order = '.(int) $id_order);
  }

  /**
   * action done when updating the order status
   *
   * @param array $params
   * @return bool
   */
  public function hookUpdateOrderStatus($params)
  {
    $order_status = false;
    $conf = Configuration::getMultiple(array('SAC_PRODUCTION', 'PS_SAC_ID_FRAUD', 'SAC_SITEID', 'SAC_LOGIN', 'SAC_PASSWORD'));
    if ($params['newOrderStatus']->id == Configuration::get('SAC_ID_FRAUD'))
      $order_status = 2;
    elseif ($params['newOrderStatus']->id == _PS_OS_DELIVERED_)
      $order_status = 1;
    elseif ($params['newOrderStatus']->id == _PS_OS_CANCELED_)
      $order_status = 2;
    elseif ($params['newOrderStatus']->id == _PS_OS_REFUND_)
      $order_status = 6;

    if ($order_status != false)
      return file_get_contents('https://secure.fia-net.com/'.($conf['SAC_PRODUCTION'] ? 'fscreener' : 'pprod').'/engine/delivery.cgi?SiteID='.$conf['SAC_SITEID'].'&Pwd='.urlencode($conf['SAC_PASSWORD']).'&RefID='.(int) ($params['id_order']).'&Status='.$order_status);
    else
      return true;
  }

  /**
   * returns true if the payment module $module is set to use Certissim AND if the order has never been sent to Certissim, false otherwise
   *
   * @param int $id_module payment module id
   * @return bool
   */
  public function needCheck($module)
  {
    //récupération des id des modules activés pour l'envoi des transactions
    $modules = explode(',', Configuration::get('SAC_PAYMENT_MODULE'));

    //set $inarray var to true only if $module is found in the array containing payment modules activated for Certissim
    $inarray = in_array($module, $modules);

    return $inarray;
  }

  /**
   * returns true if the order is under screening, false otherwise
   *
   * @param int $id_order
   * @return bool
   */
  public function waitingEval($id_order)
  {
    $res = Db::getInstance()->ExecuteS('
			SELECT `avancement`,`eval`
			FROM '._DB_PREFIX_.self::CERTISSIM_TABLE_NAME.'
			WHERE id_order='.(int) $id_order);

    $waiting_eval = Db::getInstance()->NumRows() > 0 && $res['eval'] == '' && $res['avancement'] == 'encours';

    return $waiting_eval;
  }

  /**
   * returns true if the order is ready to be sent to Certissim, false otherwise
   *
   * @param int $id_order
   * @param bool
   */
  public function readyToSend($id_order)
  {
    $res = Db::getInstance()->ExecuteS('
			SELECT `avancement`
			FROM `'._DB_PREFIX_.self::CERTISSIM_TABLE_NAME.'`
			WHERE `id_order` = '.(int) $id_order);

    $res = array_pop($res);

    $ready = Db::getInstance()->NumRows() > 0 && ($res['avancement'] == '' || is_null($res['avancement']) || strtoupper($res['avancement']) == 'NULL');

    return $ready;
  }

  /**
   * returns true if the order has already been sent to Certissim, false otherwise
   *
   * @param int $order_id
   * @return bool
   */
  public function hasBeenSentToCertissim($order_id)
  {
    $res = Db::getInstance()->ExecuteS('
			SELECT `avancement`
			FROM `'._DB_PREFIX_.self::CERTISSIM_TABLE_NAME.'`
			WHERE `id_order` = '.(int) $order_id);

    $sent = true;
    $i = 0;
    while ($i < count($res) && $sent) {
      $row = $res[$i];
      $sent = Db::getInstance()->NumRows() > 0 && !($row['avancement'] == '' || is_null($row['avancement']) || strtoupper($row['avancement']) == 'NULL');
      $i++;
    }

    return $sent;
  }

  /**
   * build and send the XML stream to Certissim
   *
   * @param array $params
   */
  private function buildAndSendOrder($id_order)
  {
    CertissimTools::insertLog(__METHOD__.' : '.__LINE__, 'construction du flux pour order '.$id_order);
    $order = new Order($id_order);
    //service instanciation
    $sac = new CertissimSac();
    //gets the delivery address
    $address_delivery = new Address((int) ($order->id_address_delivery));
    //gets the billing address
    $address_invoice = new Address((int) ($order->id_address_invoice));
    //gets the customer
    $customer = new Customer((int) ($order->id_customer));
    //XML stream initialization
    $orderFianet = new CertissimControl();

    //gets the default language
    $id_lang = Configuration::get('PS_LANG_DEFAULT');

    //instanciation de l'élément <utilisateur type="livraison" ...>
    $utilisateur_facturation = new CertissimUtilisateur(
        'facturation',
        (($customer->id_gender == 1) ? $this->l('monsieur') : (($customer->id_gender == 2 ) ? $this->l('madame') : $this->l('monsieur'))),
        ($address_invoice->lastname),
        ($address_invoice->firstname),
        ($address_invoice->company),
        ($address_invoice->phone),
        ($address_invoice->phone_mobile),
        null,
        $customer->email
    );

    //gets the stats of the customer
    $customer_stats = $customer->getStats();

    //gets the orders list of the customer
    $all_orders = Order::getCustomerOrders((int) ($customer->id));

    //initialization of the tag <siteconso>
    $siteconso = new CertissimSiteconso(
        $customer_stats['total_orders'],
        $customer_stats['nb_orders'],
        $all_orders[count($all_orders) - 1]['date_add'],
        (count($all_orders) > 1 ? $all_orders[1]['date_add'] : null)
    );

    //gets the billing country
    $country = new Country((int) ($address_invoice->id_country));

    //initialization of the tag <adresse type='facturation'>
    $adresse_facturation = new CertissimAdresse(
        'facturation',
        ($address_invoice->address1),
        ($address_invoice->address2),
        ($address_invoice->postcode),
        ($address_invoice->city),
        ($country->name[$id_lang])
    );

    //gets the carrier
    $carrier = new Carrier((int) ($order->id_carrier));

    //if it's a home delivery carrier
    if (Configuration::get('SAC_CARRIER_TYPE_'.(int) ($carrier->id)) == 4)
    {
      //initialization of the tag <utilisateur type='livraison'>
      $utilisateur_livraison = new CertissimUtilisateur(
          'livraison',
          (($customer->id_gender == 1) ? $this->l('Monsieur') : (($customer->id_gender == 2 ) ? $this->l('Madame') : $this->l(''))),
          ($address_delivery->lastname),
          ($address_delivery->firstname),
          ($address_delivery->company),
          ($address_delivery->phone),
          ($address_delivery->phone_mobile),
          null,
          $customer->email);

      //gets the delivery country
      $country = new Country((int) ($address_delivery->id_country));

      //initialization of the tag <adresse type='livraison'>
      $adresse_livraison = new CertissimAdresse(
          'livraison',
          ($address_delivery->address1),
          ($address_delivery->address2),
          ($address_delivery->postcode),
          ($address_delivery->city),
          ($country->name[$id_lang]),
          null
      );
    }

    //gets the currency
    $currency = new Currency((int) ($order->id_currency));
    //initialization of the tag <infocommande>
    $infocommande = new CertissimInfocommande(
        $sac->getSiteId(),
        $order->id,
        (string) $order->total_paid,
        self::getIpByOrder((int) ($order->id)),
        date('Y-m-d H:i:s')
    );
    //gets information about the order products
    $products = $order->getProducts();
    //gets the default product type
    $default_product_type = Configuration::get('SAC_DEFAULT_PRODUCT_TYPE');

    //initialization of the tag <list>
    $liste_produits = new CertissimProductList();
    //initialization of the var that says if all the products of the order are downloadables
    $alldownloadables = true;
    //foreach product
    foreach ($products as $product) {
      $alldownloadables = $alldownloadables && strlen($product['download_hash']) > 0;
      //gets the product type
      $product_category = Product::getIndexedCategories((int) ($product['product_id']));
      //initialization of the tag <produit>
      $produit = new CertissimXMLElement("<produit></produit>");

      //if this product belongs to a specified category
      if (Configuration::get('SAC_CATEGORY_TYPE_'.$product_category))
      //sets the specified category as the type attribute
        $produit->addAttribute('type', Configuration::get('SAC_CATEGORY_TYPE_'.$product_category));
      else //if this product doesn't belong to a specified category
      //the type attribute takes the default value
        $produit->addAttribute('type', $default_product_type);
      //adds the attributes ref, nb and prixunit
      $produit->addAttribute('ref', ((((isset($product['product_reference']) and !empty($product['product_reference'])) ? $product['product_reference'] : ((isset($product['product_ean13']) and !empty($product['product_ean13'])) ? $product['product_ean13'] : strtoupper($product['product_name']))))));
      $produit->addAttribute('nb', $product['product_quantity']);
      $produit->addAttribute('prixunit', $product['total_price']);
      $produit->setValue(($product['product_name']));

      //adds the product into the list
      $liste_produits->addProduit($produit);
    }

    //gets the carrier type
    $carrier_type = ($alldownloadables ? '5' : Configuration::get('SAC_CARRIER_TYPE_'.(int) ($carrier->id)));
    //initialization of the tag <transport>
    $transport = new CertissimTransport(
        $carrier_type,
        $alldownloadables ? 'Téléchargement' : Tools::htmlentitiesUTF8($carrier->name),
        $alldownloadables ? '1' : self::getCarrierFastById((int) ($carrier->id)),
        null
    );

    //if it's a drop off point delivery
    if ($carrier_type == 2)
    {
      //initialization of the tag <adresse>
      $adresse_point_relai = new CertissimAdresse(
          null,
          ($address_delivery->address1),
          ($address_delivery->address2),
          ($address_delivery->postcode),
          ($address_delivery->city),
          ($country->name[$id_lang])
      );
      //initialization of the tag <pointrelais>
      $pointrelais = new CertissimPointrelais(null, $address_delivery->company, $adresse_point_relai);
      //adds the tag <pointrelais> as a child of the tag <transport>
      $transport->childPointrelais($pointrelais);
    }

    //initialization of the tag <paiement>
    $paiement = new CertissimPaiement(
        Configuration::get('SAC_PAYMENT_TYPE_'.(substr($order->module, 0, 15)))
    );
    //stack initialization
    $stack = new CertissimXMLElement("<stack></stack>");

    //builds the stream by merging all tags together
    $utilisateur_facturation->childSiteconso($siteconso);
    $orderFianet->childUtilisateur($utilisateur_facturation);
    $orderFianet->childAdresse($adresse_facturation);
    if (isset($utilisateur_livraison))
      $orderFianet->childUtilisateur($utilisateur_livraison);
    if (isset($adresse_livraison))
      $orderFianet->childAdresse($adresse_livraison);
    $infocommande->childTransport($transport);
    $infocommande->childList($liste_produits);
    $orderFianet->childInfocommande($infocommande);
    $orderFianet->childPaiement($paiement);

    $stack->childControl($orderFianet);
    CertissimTools::insertLog(__METHOD__.' : '.__LINE__, '<![DATA['.$orderFianet->getXML().']]>');
    //sends the stack to Certissim et gets the response
    $res = $sac->sendStacking($stack);

    //logs an error message and returns if an error occured
    if ($res === false)
    {
      CertissimTools::insertLog(__METHOD__.' : '.__LINE__, "L'envoi a échoué pour la commande ".(int) $order->id);
      return;
    }

    CertissimTools::insertLog(__METHOD__.' : '.__LINE__, "Retour de Fia-Net pour la commande ".(int) $order->id." : $res");

    foreach ($res->getChildrenByName('result') as $result) {
      $avancement = $result->getAttribute('avancement');

      //update in database
      Db::getInstance()->Execute("
			UPDATE `"._DB_PREFIX_.self::CERTISSIM_TABLE_NAME."`
			SET `avancement`= '".pSQL($avancement)."'
			WHERE `id_order` = ".(int) $order->id." LIMIT 1");
    }
  }

  /**
   * action executed when the payment is confirmed : if the order is ready to be sent, then it's sent to Certissim, otherwise nothing happen
   *
   * @param array $params
   * @return bool
   */
  public function hookPaymentConfirm($params)
  {
    if (!$this->readyToSend((int) ($params['id_order'])))
      return false;
    else
      $this->buildAndSendOrder($params['id_order']);

    return true;
  }

  /**
   * display the Certissim analysis on the admin order page if the order has been sent to Certissim, nothing otherwise
   *
   * @param array $params
   * @return string
   */
  public function hookAdminOrder($params)
  {
    $conf = Configuration::get('SAC_PRODUCTION');
    $order = new Order((int) ($params['id_order']));
    if (!$this->hasBeenSentToCertissim($order->id))
      return null;

    $score = self::getScore((int) ($order->id));

    if (Tools::isSubmit('submitFianet'))
      $this->_postProcess();

    $html = '<br /><fieldset style="width:400px;"><legend>'.$this->l('Fianet Validation').'</legend>';
    $html .= '<a href="https://secure.fia-net.com/'.($conf ? 'fscreener' : 'pprod').'/BO/visucheck_detail.php?sid='.Configuration::get('SAC_SITEID').'&log='.urlencode(Configuration::get('SAC_LOGIN')).'&pwd='.urlencode(Configuration::get('SAC_PASSWORD')).'&rid='.(int) $params['id_order'].'" target="_blank">'.$this->l('See Detail').'</a><br />';
    $html .= $this->l('Eval').' : '.Tools::htmlentitiesUTF8($score['eval']);
    $html .= ( isset($score['detail']) ? '<br />'.$this->l('Détail').' : '.Tools::htmlentitiesUTF8($score['detail']) : '');
    $html .= '</fieldset>';

    return $html;
  }

  /**
   * when a new order is created: adds its id, the IP of the customer, and the id of the payment module in the module table in the databse
   *
   * @param array $params
   * @return bool
   */
  public function hookNewOrder($params)
  {
    //if the IP address is not valid, or if the payment module is not configured to use Certissim then end of process
    if (in_array(self::getRemoteAddr(), array('0.0.0.0', '', false)) || !$this->needCheck($params['order']->module))
      return true;

    //looks for the order in the Certissim table
    $res = Db::getInstance()->Execute('
			SELECT `id_order`
			FROM '._DB_PREFIX_.self::CERTISSIM_TABLE_NAME.'
			WHERE id_order='.(int) ($params['order']->id));

    //if order found
    if (Db::getInstance()->NumRows() > 0)
    {
      //updates the entry
      $update = Db::getInstance()->Execute("
				UPDATE `"._DB_PREFIX_.self::CERTISSIM_TABLE_NAME."`
				SET `ip_address` = '".pSQL(self::getRemoteAddr())."', `date` = '".pSQL(date('Y-m-d H:i:s'))."'
				WHERE `id_order` = ".(int) $params['order']->id." LIMIT 1");

      //logs error if sql failed
      if (!(bool) $update)
        CertissimTools::insertLog(__METHOD__." : ".__LINE__, "Order ".(int) $params['order']->id." was not updated.");
    }
    else //if order has not been found
    {
      //adds the order in the Certissim table
      $insert = Db::getInstance()->Execute("
				INSERT INTO `"._DB_PREFIX_.self::CERTISSIM_TABLE_NAME."` (`id_order`, `ip_address`, `date`)
				VALUES (".(int) $params['order']->id.", '".pSQL(self::getRemoteAddr())."','".pSQL(date('Y-m-d H:i:s'))."')");

      //logs error if sql failed
      if (!(bool) $insert)
        CertissimTools::insertLog(__METHOD__." : ".__LINE__, "Order ".(int) $params['order']->id." was not inserted.");
    }

    return true;
  }

  /**
   * Replaces the IP address in the Certissim table in case the previous one has been inserted by the return from a bank server
   *
   * @param array $params
   * @return bool
   */
  public function hookPaymentReturn($params)
  {
    //checks if the order already exists in the Certissim table
    $res = Db::getInstance()->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.self::CERTISSIM_TABLE_NAME.'`
			WHERE `id_order` = '.(int) $params['id_order']);

    //if order exists, then replaces the IP address
    if (Db::getInstance()->NumRows() > 0)
    {
      $update = Db::getInstance()->Execute("
				UPDATE `"._DB_PREFIX_.self::CERTISSIM_TABLE_NAME."`
				SET `ip_address` = '".pSQL(self::getRemoteAddr())."'
				WHERE `id_order` = ".(int) $params['order']->id." LIMIT 1");

      //logs error if sql failed
      if (!(bool) $update)
        CertissimTools::insertLog(__METHOD__." : ".__LINE__, "Commande ".(int) $params['order']->id." non mise � jour : '".Db::getMsgError()."'");

      return (bool) $update;
    }
    else
    {
      return false;
    }
  }

  /**
   * checks orders that are waiting their score, and request the score
   */
  static public function checkWaitingOrders()
  {
    //gets all the orders without score
    $orders = Db::getInstance()->ExecuteS('SELECT `id_order`, `avancement` FROM '._DB_PREFIX_.self::CERTISSIM_TABLE_NAME.' WHERE `avancement` = \'encours\'');
    //foreach unscored order
    foreach ($orders as $order) {
      //call Certissim to get the score
      $eval = self::getScore($order['id_order']);

      switch ($eval['eval']) {
        //is score available
        case -1:
        case 0:
        case 100:
          break;

        //if error on Certissim side
        case 'error':
          //inserts the error in the Certissim table
          Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.self::CERTISSIM_TABLE_NAME.'`
			SET `eval` = "'.pSQL($eval).'", `avancement` = "'.pSQL($eval['eval']).'", `detail` = "'.pSQL($eval['detail']).'"
			WHERE `id_order` = '.(int) ($order['id_order']).' LIMIT 1');
          break;

        //if order not found on Certissim server
        case 'absente':
          //sends the order again
          $this->buildAndSendOrder((int) ($order['id_order']));
          //inserts the error in the Certissim table
          Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.self::CERTISSIM_TABLE_NAME.'`
			SET `eval` = "'.pSQL($eval).'", `avancement` = "'.pSQL($eval['eval']).'"
			WHERE `id_order` = '.(int) ($order['id_order']).' LIMIT 1');
          break;

        default :
          break;
      }
    }
  }

  /**
   * returns an array containing evaluation with detail if order is scored, error notification otherwise
   *
   * @param int $id_order
   * @return array
   */
  private static function getScore($id_order)
  {
    //gets the eval and detail fields in the Certissim table
    $res = Db::getInstance()->Execute('
		SELECT `id_order`,`eval`,`detail`
		FROM '._DB_PREFIX_.self::CERTISSIM_TABLE_NAME.'
		WHERE id_order='.(int) $id_order);

    $count = Db::getInstance()->NumRows();

    //f the evaluation is set
    if ($count > 0 && $res['eval'] != '' && $res['eval'] != 'NULL' && !is_null($res['eval']))
    //returns the evaluation and detail
      return $res;

    //inistialization of Certisism
    $sac = new CertissimSac();

    //gets the validation result
    $xml_result = $sac->getValidation($id_order);

    //gets the 'retour' attribute value
    $retour = $xml_result->getAttribute('retour');

    //if order has been found
    if ($retour == "trouvee")
    {
      //gets the transaction element
      $transaction = array_pop($xml_result->getChildrenByName('transaction'));
      //gets the attribute 'avancement' to know the state of the analysis
      $avancement = $transaction->getAttribute('avancement');
      //if order has been scored
      if ($avancement == "traitee")
      {
        //gets the score
        $xml_eval = array_pop($transaction->getChildrenByName('eval'));
        //builds the array to return
        $return = array('eval' => $xml_eval->getValue(), 'detail' => (string) $xml_eval->getAttribute('info'));

        //if order already exists in Certissim table
        if ($count > 0)
        {
          //updates the entry
          Db::getInstance()->Execute("
			UPDATE `"._DB_PREFIX_.self::CERTISSIM_TABLE_NAME."`
			SET `eval` = '".pSQL($return['eval'])."', `detail` = '".pSQL($return['detail'])."', `avancement` = '".pSQL($avancement)."'
			WHERE `id_order` = '".(int) $id_order."' LIMIT 1");
        }
        else //if order not found
        {
          //inserts the order into the Certissim table
          Db::getInstance()->Execute("
			INSERT INTO `"._DB_PREFIX_.self::CERTISSIM_TABLE_NAME."` (`id_order`, `eval`, `detail`, `avancement`)
			VALUES (".(int) $id_order.", '".pSQL($return['eval'])."', '".pSQL($return['detail'])."', '".pSQL($avancement)."')");
        }
      }
      else
      {
        $return = array('eval' => $avancement);
      }
      return $return;
    }
    if ($retour == "absente")
      return array('eval' => 'absente');

    return array('eval' => 'error');
  }

  /**
   * update orders that have been reevaluated
   *
   * @return bool
   */
  public static function reEvaluateOrder()
  {
    //Certissim initialization
    $sac = new CertissimSac();

    //gets all reevaluations
    $result = $sac->getAlert('all');

    //foreach reevaluated order
    foreach ($result->getChildrenByName('transaction') as $transaction) {
      //checks that the order already exists in Certissim table
      $res = Db::getInstance()->ExecuteS('
		SELECT `id_order`
		FROM `'._DB_PREFIX_.self::CERTISSIM_TABLE_NAME.'`
		WHERE `id_order` = '.(int) ($transaction->getAttribute('refid')));

      $found = Db::getInstance()->NumRows() > 0;

      //if order has not been found, end of process
      if (!$found)
        return false;

      //gets the score
      $eval = array_pop($transaction->getChildrenByName('eval'));

      //update the entry in Certissim table
      Db::getInstance()->Execute("
			UPDATE `"._DB_PREFIX_.self::CERTISSIM_TABLE_NAME."`
			SET `eval` = '".pSQL($eval->getValue())."', `detail` = '".pSQL($eval->getAttribute('info'))."'
			WHERE `id_order` = ".(int) ($transaction->getAttribute('refid'))." LIMIT 1");
    }

    return true;
  }

  /**
   * returns the delivery type : 2 for standard delivery, 1 for express delivery
   * Notice : there is no way to know what type of delivery it is wile we're developping the module, it returns '2' for each case
   * 
   * @return int
   */
  private static function getCarrierFastById()
  {
    return 2;
  }

  /**
   * returns the list of all Certissim product categories
   * 
   * @return array
   */
  public function getSACCategories()
  {
    $categories = Db::getInstance()->ExecuteS('SELECT id_category, id_sac FROM '._DB_PREFIX_.'sac_categories');
    $sac_cat = array();
    if ($categories)
      foreach ($categories as $category)
        $sac_cat[$category['id_category']] = $category['id_sac'];
    return $sac_cat;
  }

  /**
   * Get the server variable REMOTE_ADDR
   *
   * @param string $remote_addr ip of client
   */
  static function getRemoteAddr()
  {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND $_SERVER['HTTP_X_FORWARDED_FOR'])
    {
      if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ','))
      {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return $ips[0];
      }
      else
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
  }

}