<?php
/*
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.wexpay.com for more information.
*
*  @author Profileo <contact@profileo.com>
*  @version  Release: $Revision: 2.0 $
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class Wexpay extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();

	public  $merchant_id;

	public function __construct()
	{
		$this->name = 'wexpay';
		$this->tab = 'payments_gateways';
		$this->version = '2.2';
		$this->module_key = '5299896ce976397cf90610f8073eb6de';
		$this->limited_countries = array('fr');

		$config = Configuration::getMultiple(array('WEXPAY_MERCHANT_ID'));
		if (isset($config['WEXPAY_MERCHANT_ID']))
			$this->merchant_id = $config['WEXPAY_MERCHANT_ID'];

		parent::__construct();

		$this->displayName = $this->l('weXpay e-money');
		$this->description = $this->l('Accept payments by weXpay e-money');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
		if (!isset($this->merchant_id))
			$this->warning = $this->l('Account merchant id must be configured in order to use this module correctly');

		/** Backward compatibility */
		require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
	}

	public function install()
	{
		return parent::install() && $this->registerHook('payment');
	}

	public function uninstall()
	{
		return Configuration::deleteByName('WEXPAY_MERCHANT_ID') && parent::uninstall();
	}

	private function _postValidation()
	{
		if (isset($_POST['submitWexpay']) && empty($_POST['merchant_id']))
			$this->_postErrors[] = $this->l('account merchant id is required.');
	}

	private function _postProcess()
	{
		if (isset($_POST['submitWexpay']))
		{
			if (Tools::getValue('merchant_id'))
				Configuration::updateValue('WEXPAY_MERCHANT_ID', Tools::getValue('merchant_id'));
			$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '.$this->l('Settings updated').'</div>';
		}
	}

	private function _displayWexpay()
	{
		$this->_html .= '
		<fieldset>
			<legend><img src="../modules/'.$this->name.'/logo.gif" alt="" /> '.$this->l('weXpay').'</legend>
			<div style="float: right; width: 340px; height: 200px; border: dashed 1px #666; padding: 8px; margin-left: 12px; margin-top:-15px;">
				<h2 style="color:#7DA61F;">'.$this->l('Contactez l\'équipe weXpay').'</h2>
				<div style="clear: both;"></div>
				<p>'.$this->l('Contactez nous / ou faites vous appeler par un de nos chargés de clientèle :').'<br />'.$this->l('Mail : ').'<a href="mailto:marchand@wexpay.com" style="color:#7DA61F;">marchand@wexpay.com</a><br />'.$this->l('Tel : 01 46 08 68 94').'</p>
				<p style="padding-top:40px;"><b>'.$this->l('Faites une demande d`information ou de contrat et nous vous rappelons : ').'</b><br /><a href="https://www.wexpay.com/sites-marchands" target="_blank" style="color:#7DA61F;">https://www.wexpay.com/sites-marchand</a></p>
				<div style="margin-top:50px;margin-left:-10px;"><object width="360" height="60" data="../modules/'.$this->name.'/img/banniere360x60.swf" type="application/x-shockwave-flash"><param name="movie" value="../modules/'.$this->name.'/img/banniere360x60.swf" />
							<param name="quality" value="high" />
							<param name="allowScriptAccess" value="sameDomain" /><embed type="application/x-shockwave-flash" width="360" height="60" src="../modules/'.$this->name.'/img/banniere360x60.swf"></object></div>
				<div style="clear: right;"></div>
			</div>
			<div style="float:left;text-align:justify;margin-top:3px;width:500px;">
			<b>'.$this->l('Ce module vous permet d\'accepter les paiements en espèces avec weXpay.').'</b><br /><br />
			'.$this->l('Payer en espèces sur Internet, aujourd\'hui c\'est possible ! weXpay permet à votre boutique de capter une nouvelle clientèle en lui proposant la solution de paiement la plus simple : les espèces !').'<br /><br />
			'.$this->l('Grâce à son réseau de distribution weXpay permet à l\'internaute de changer ses espèces contre un code qui a les mêmes propriétés qu\'un billet de banque : il est utilisable par tous autant de fois que nécessaire, divisible au centime d\'Euros et cumulable,').'<br /><br />
			'.$this->l('L\'installation est facile, rapide et gratuite, les paiements sont sécurisés et 100% garantis !').'<br />
			'.$this->l('Sans frais fixes ni frais cachés.').'<br /><br />
			'.$this->l('weXpay est la solution alternative à la carte bancaire qui bénéficie d\'un agrément d\'Emetteur de Monnaie Electronique de la Banque de France.').'<br /><br />
			'.$this->l('Avec des tarifs attractifs, weXpay vous permet de proposer un moyen de paiement en espèces quelle que soit votre activité marchande.').'<br /><br />
			'.$this->l('Pour plus d\'infos :').'<br /><a href="https://www.wexpay.com/decouvrir-wexpay/" target="_blank" style="color:#7DA61F;">https://www.wexpay.com/decouvrir-wexpay/</a>
			</div><div style="clear:both;">&nbsp;</div>
		</fieldset>';
	}

	private function _displayForm()
	{
		$this->_html .=
		'<form action="'.htmlentities($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8').'" method="post">
			<fieldset>
				<legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->l('Configuration').'</legend>
				<div style="float:left;"><img src="../modules/'.$this->name.'/img/config_1.png" /></div>
				<div style="float:left; margin-left:10px;"><h2 style="color:#7DA61F;">'.$this->l('Créer un compte weXpay').'</h2>
				'.$this->l('Rien de plus simple, il vous suffit de vous rendre sur notre espace site marchand, et de remplir le formulaire de demande de contrat').'<br /><a href="https://www.wexpay.com/demande-contrat" target="_blank" style="color:#7DA61F;">https://www.wexpay.com/demande-contrat</a>
				</div>
				<div style="clear:both;">&nbsp;</div><br />
				<div style="float:left;"><img src="../modules/'.$this->name.'/img/config_2.png" /></div>
				<div style="float:left; margin-left:10px;width:800px;"><h2 style="color:#7DA61F;">'.$this->l('Activer facilement et gratuitement weXpay').'</h2>'
				.$this->l('Vous avez signé un contrat avec weXpay, et vous avez reçu vos codes d\'activation dans un mail de bienvenue weXpay - Procédure = entrez vos indentifiant weXpay dans Prestashop pour activer le module et réalisez 3 transactions de test avec les codes de tests fournis dans le mail de bienvenue.').'<br/><br/><label style="text-align:left;width:210px;">'.$this->l('weXpay merchant ID').'</label>
				<div><input type="text" size="33" name="merchant_id" value="'.htmlentities(Tools::getValue('merchant_id', $this->merchant_id), ENT_QUOTES, 'UTF-8').'" /> <input type="submit" name="submitWexpay" value="'.$this->l('Update settings').'" class="button" /></div><br />'
				.$this->l('Une fois que les transactions sont validées avec succès, connectez-vous à votre espace partenaire weXpay ').'<a href="https://partenaires.wexpay.com" target="_blank" style="color:#7DA61F;">https://partenaires.wexpay.com</a>'
				.$this->l(' : uploadez votre logo, et appuyer sur le bouton ').'<b>'.$this->l('"passage en production."').'</b><br /><br />'
				.$this->l('Vous devez cocher dans votre espace partenaire « passage en production » pour activer weXpay, et 
pour permettre à vos clients de payer avec des weXpay sur votre site.').'<br /><br /><a href="http://media.wexpay.com/pdf/Config-Presta1.5.pdf" target="_blank" style="color:#7DA61F;font-weight:bold; text-decoration:underline;">'
				.$this->l('Pour plus d\'infos, cliquez-ici').'</a>
				</div>
				<div style="clear:both;">&nbsp;</div>
			</fieldset>
		</form>';
	}

	public function getContent()
	{
		$this->_html .= ' <div style="float:left;margin-left:-30px; margin-top:-55px;">
		<img src="../modules/'.$this->name.'/wexpay.png" alt="" />
		</div><h2 style="float:left;color:#7DA61F;">'.$this->displayName.' '.$this->l(': acceptez le paiement en espèces et augmentez votre chiffre d\'affaires !').'</h2><div style="clear: both;"></div>';
		if (!empty($_POST))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= $this->displayError($err);
		}
		else
			$this->_html .= '<br />';
		
		$this->_displayWexpay();
		$this->_html .= '<br />';
		$this->_displayForm();

		return $this->_html;
	}

	public function hookPayment($params)
	{
		$currency = new Currency((int)$params['cart']->id_currency);
		$amount = number_format(Tools::convertPrice($params['cart']->getOrderTotal(true, 3), $currency), 2, '.', '');

		if (strpos($amount, '.'))
			$amount = $amount * 100;
		$amount = str_replace('.', '', $amount);
		$customer = new Customer((int)$this->context->cookie->id_customer);

		$this->context->smarty->assign(array(
		'merchant_id' => Tools::safeOutput($this->merchant_id),
		'amount' => $amount,
		'urlNotification' => 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/wexpay/validation.php',
		'urlError' => 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'order.php',
		'urlReturn' => 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'order-confirmation.php?id_cart='.$this->context->cookie->id_cart.'&id_module='.$this->id.'&key='.$customer->secure_key,
		'ref_order' => (int)$this->context->cookie->id_cart));

		return $this->display(__FILE__, 'payment.tpl');
	}
}