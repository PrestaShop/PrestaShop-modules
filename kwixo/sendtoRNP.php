<?php
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/kwixo.php');
ini_set('default_charset', 'UTF-8');

global $cart, $cookie;
if (!isset($cookie->rnp_payment) AND $cookie->rnp_payment === false)
	Tools::redirect('order.php');
unset($cookie->rnp_payment);

if (Configuration::get('RNP_PRODUCTION'))
	$url =  'https://secure.kwixo.com/transaction.html';
else
	$url =' http://recette.kwixo.com/payflow/transaction.html';
	
$kwixo = new Kwixo();
$customer = new Customer(intval($cart->id_customer));
$param = array('custom' => $cart->id, 'id_module' => $kwixo->id, 'amount' => $cart->getOrderTotal(true), 'secure_key' => $customer->secure_key);

$rnp_categories = $kwixo->getRNPCategories();
$products = $cart->getProducts();
$default_product_type = Configuration::get('RNP_DEFAULTCATEGORYID');
$invoice_address = new Address(intval($cart->id_address_invoice));
$delivery_address = new Address(intval($cart->id_address_delivery));
$carrier = new Carrier(intval($cart->id_carrier));
$currency = new Currency(intval($cart->id_currency));
$invoice_country = new Country(intval($invoice_address->id_country));
$delivery_country = new Country(intval($delivery_address->id_country));
$crypt = $kwixo->getHashKwixo($cart->id, $cart->getOrderTotal(true), $customer->email, $customer->lastname);
$nb = 0;

foreach ($products as $product)
	$nb += $product['cart_quantity'];

$xml = '
';

$xml .= '
	<?xml version="1.0" encoding="UTF-8" ?>
	<control>
		 <utilisateur type="facturation" qualite="2"> 
			 <nom titre="'.(($customer->id_gender == 1) ? 'monsieur' : 'madame').'">'.$invoice_address->lastname.'</nom> 
			<prenom>'.$invoice_address->firstname.'</prenom> 
			<telhome>'.$invoice_address->phone.'</telhome>
			<telmobile>'.$invoice_address->phone_mobile.'</telmobile>
			 <email>'.$customer->email.'</email>
		</utilisateur>';
$xml .= '
		<adresse type="facturation" format="1">
			<rue1>'.$invoice_address->address1.'</rue1>
			<rue2>'.$invoice_address->address2.'</rue2>
			<cpostal>'.$invoice_address->postcode.'</cpostal>
			<ville>'.$invoice_address->city.'</ville>
			<pays>'.$invoice_country->name[intval($cookie->id_lang)].'</pays>
		</adresse>
		<adresse type="livraison" format="1">
			<rue1>'.$delivery_address->address1.'</rue1>
			<rue2>'.$delivery_address->address2.'</rue2>
			<cpostal>'.$delivery_address->postcode.'</cpostal>
			<ville>'.$delivery_address->city.'</ville>
			<pays>'.$delivery_country->name[intval($cookie->id_lang)].'</pays>
		</adresse>
		<infocommande>
			<siteid>'.Configuration::get('RNP_MERCHID').'</siteid>
			<refid>'.$cart->id.'</refid>
			<montant devise="'.$currency->iso_code.'">'.$cart->getOrderTotal(true).'</montant>
			<transport>
				<type>'.Configuration::get('RNP_CARRIER_TYPE_'.intval($carrier->id)).'</type>
				<nom>'.$carrier->name.'</nom>
				<rapidite>2</rapidite>
			</transport>
			<list nbproduit="'.$nb.'">';
foreach ($products AS $product)
{
	if (_PS_VERSION_ >= 1.4)
		$product_categories = Product::getProductCategories(intval($product['id_product']));
	else
        $product_categories = Product::getIndexedCategories(intval($product['id_product']));
	$have_rnp_cat = false;
	foreach ($product_categories AS $category)
		if (array_key_exists($category['id_category'], $rnp_categories))
		{
			$have_rnp_cat = $category['id_category'];
			break;
		}
	$xml .= '
				<produit type="'.($have_rnp_cat !== false ? $have_rnp_cat : $default_product_type).'" ref="'.(((isset($product['reference']) AND !empty($product['reference'])) ? $product['reference'] : ((isset($product['ean13']) AND !empty($product['ean13'])) ? $product['ean13'] : str_replace("'", "", $product['name'])))).'"
				 nb="'.$product['cart_quantity'].'" prixunit="'.$product['price'].'">'.str_replace("'", "", $product['name']).'</produit>';	
}
$xml .='
			</list>
		</infocommande>
		<wallet version="1.0">
			<datelivr>'.$kwixo->get_delivery_date(Configuration::get('RNP_NBDELIVERYDAYS')).'</datelivr>
			<datecom>'.date("Y-m-d H:i:s").'</datecom>
			<crypt version="2.0">'.$crypt.'</crypt>
		</wallet>';
if (Tools::getValue('payment') == 2)
	$xml .= '
		<options-paiement type="credit" ></options-paiement>';
elseif (Tools::getValue('payment') == 3)
	$xml .= '
		<options-paiement type="comptant" comptant-rnp="0"></options-paiement>';
else
		$xml .= '
		<options-paiement type="comptant" comptant-rnp="1" comptant-rnp-offert="1"></options-paiement>';
$xml .= '
	</control>';
	
$xmlParam = '<ParamCBack>';
foreach ($param as $key => $value)
	$xmlParam .= '<obj>
					<name>'.$key.'</name>
					<value>'.$value.'</value>
				</obj>';
$xmlParam .= '</ParamCBack>';

$flux = $kwixo->clean_xml($xml);
$flux = str_replace('"', "'", $flux);
$flux = mb_convert_encoding($flux, 'UTF-8', mb_detect_encoding($flux));

$flux2 = $kwixo->clean_xml($xmlParam);
$flux2 = str_replace('"', "'", $flux2);
echo '
<center><h1>Vous allez &#234;tre redirig&#233; sur la plateforme kwixo ...</h1></center>
<form action="'.$url.'" method="POST" name="RnPform">
<input type="hidden" name="MerchID" value="'.Configuration::get('RNP_MERCHID').'" >
<input type="hidden" name="XMLInfo" value="'.$flux.'" >
<input type="hidden" name="URLCall" value="http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/kwixo/payment_return.php">
<input type="hidden" name="URLSys" value="http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/kwixo/push.php">
<input type="hidden" name="XMLParam" value="'.$flux2.'">
</form>
<script>document.RnPform.submit();</script>';


