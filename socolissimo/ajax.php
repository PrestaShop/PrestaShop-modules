<?php

include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('../../modules/socolissimo/socolissimo.php');

// To have context available and translation
$socolissimo = new Socolissimo();

// Default answer values => key
$result = array(
	'answer' => true,
	'msg' => ''
);

// Check Token
if (Tools::getValue('token') != sha1('socolissimo'._COOKIE_KEY_.Context::getContext()->cart->id))
{
	$result['answer'] = false;
	$result['msg'] = $socolissimo->l('Invalid token');
}

// If no problem with token but no delivery available
if ($result['answer'] && !($result = $socolissimo->getDeliveryInfos(Context::getContext()->cart->id, Context::getContext()->customer->id)))
{
	$result['answer'] = false;
	$result['msg'] = $socolissimo->l('No delivery information selected');
}

header('Content-type: application/json');
echo json_encode($result);
exit(0);

?>
