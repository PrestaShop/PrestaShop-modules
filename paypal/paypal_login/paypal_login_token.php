<?php 
header('Content-Type: text/html; charset=utf-8');
include_once(dirname(__FILE__).'/../../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../../init.php');

include_once(_PS_MODULE_DIR_.'paypal/paypal.php');
include_once(_PS_MODULE_DIR_.'paypal/paypal_login/paypal_login.php');
include_once(_PS_MODULE_DIR_.'paypal/paypal_login/PayPalLoginUser.php');

$login = new PayPalLogin();

$obj = $login->getAuthorizationCode();
if ($obj)
{
	$context = Context::getContext();
	$customer = new Customer((int)$obj->id_customer);
	$context->cookie->id_customer = (int)($customer->id);
	$context->cookie->customer_lastname = $customer->lastname;
	$context->cookie->customer_firstname = $customer->firstname;
	$context->cookie->logged = 1;
	$customer->logged = 1;
	$context->cookie->is_guest = $customer->isGuest();
	$context->cookie->passwd = $customer->passwd;
	$context->cookie->email = $customer->email;
	$context->customer = $customer;
	$context->cookie->write();
}

?>

<script type="text/javascript">
	window.opener.location.reload(false);
	window.close();
</script>