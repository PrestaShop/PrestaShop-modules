<?php
include(dirname(__FILE__). '/../../../config/config.inc.php');
include(dirname(__FILE__). '/../../../init.php');
include(dirname(__FILE__). '/../payulatam.php');

if(isset($_REQUEST['sign'])){
	$signature = $_REQUEST['sign'];
} else {
	$signature = $_REQUEST['firma'];
}

if(isset($_REQUEST['merchant_id'])){
	$merchantId = $_REQUEST['merchant_id'];
} else {
	$merchantId = $_REQUEST['usuario_id'];
}
if(isset($_REQUEST['reference_sale'])){
	$referenceCode = $_REQUEST['reference_sale'];
} else {
	$referenceCode = $_REQUEST['ref_venta'];
}
if(isset($_REQUEST['value'])){
	$value = $_REQUEST['value'];
} else {
	$value = $_REQUEST['valor'];
}
if(isset($_REQUEST['currency'])){
	$currency = $_REQUEST['currency'];
} else {
	$currency = $_REQUEST['moneda'];
}
if(isset($_REQUEST['state_pol'])){
	$transactionState = $_REQUEST['state_pol'];
} else {
	$transactionState = $_REQUEST['estado_pol'];
}

$split = explode('.', $value);
$decimals = $split[1];
if ($decimals % 10 == 0) {
	$value = number_format($value, 1, '.', '');
}

$payulatam = new PayuLatam();
$api_key = Configuration::get('PAYU_LATAM_API_KEY');
$signature_local = $api_key . '~' . $merchantId . '~' . $referenceCode . '~' . $value . '~' . $currency . '~' . $transactionState;
$signature_md5 = md5($signature_local);

if(isset($_REQUEST['response_code_pol'])){
	$polResponseCode = $_REQUEST['response_code_pol'];
} else {
	$polResponseCode = $_REQUEST['codigo_respuesta_pol'];
}

$order = new Order((int)$referenceCode);
if (strtoupper($signature) == strtoupper($signature_md5) && $order->current_state != Configuration::get('PS_OS_PAYMENT')) {
	$history = new OrderHistory();
	$history->id_order = (int)$referenceCode;
  $state = 'PAYU_OS_FAILED';
	
	if($transactionState == 6 && $polResponseCode == 5){
		$state = 'PAYU_OS_FAILED';
	} else if($transactionState == 6 && $polResponseCode == 4){
		$state = 'PAYU_OS_REJECTED';
	} else if($transactionState == 12 && $polResponseCode == 9994){
		$state = 'PAYU_OS_PENDING';
	} else if($transactionState == 4 && $polResponseCode == 1){				
		$state = 'PS_OS_PAYMENT';
	}

  if ($state != 'PS_OS_PAYMENT') {
    foreach ($order->getProductsDetail() as $product) {
      StockAvailable::updateQuantity($product['product_id'], $product['product_attribute_id'], +(int)$product['product_quantity'], $order->id_shop);
    }
  }
  $history->changeIdOrderState((int)Configuration::get($state), $order, true);
	$history->add();
}
?>
