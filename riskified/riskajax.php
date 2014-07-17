<?php
/**
 * 	Riskified payments security module for Prestashop. Riskified reviews, approves & guarantees transactions you would otherwise decline.
 *
 *  @author    riskified.com <support@riskified.com>
 *  @copyright 2013-Now riskified.com
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Riskified 
 */

header('Access-Control-Allow-Methods: GET,POST,OPTIONS,DELETE,PUT');
header('Access-Control-Allow-Origin: *');

include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once('riskified.php');

function fireCurl($data, $url)
{
	$domain = Configuration::get('PS_SHOP_DOMAIN');
	$auth_token = Configuration::get('PS_AUTH_TOKEN');
	Tools::getValue('id_order');
	$data_string = Tools::jsonEncode($data);
	$hash_code = hash_hmac('sha256', $data_string, $auth_token);
	Logger::addLog('Riskified URL is '.$url, 1);
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$headers = array(
		'Content-Type: application/json',
		'Content-Length: '.Tools::strlen($data_string),
		'X_RISKIFIED_SHOP_DOMAIN:'.$domain,
		'X_RISKIFIED_HMAC_SHA256:'.$hash_code);
	array_push($headers, 'X_RISKIFIED_SUBMIT_NOW:ok');
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_getinfo($ch);
	$result = curl_exec($ch);
	$decoded_response = Tools::jsonDecode($result);
	$order_id = null;
	$status = null;
	if (!is_null($decoded_response) && isset($decoded_response->order))
	{
		$order_id = $decoded_response->order->id;
		$status = $decoded_response->order->status;
		if ($status != 'captured' && $order_id)
		{
			switch ($status)
			{
				case 'approved':
				echo 'Reviewed and approved by Riskified';
				break;

				case 'declined':
				echo 'Reviewed and declined by Riskified';
				break;

				case 'submitted':
				echo 'Order under review by Riskified';
				break;
			}
		}
	}
	else
	{
		if (!is_null($decoded_response) && isset($decoded_response->error))
		{
			$error_message = $decoded_response->error->message;
			Logger::addLog('Error occured when submitting an order to Riskified.');
			echo 'Error: '.$error_message;
		}
		else
		{
			$error_message = print_r($decoded_response, true);
			echo 'Error occured: '.$error_message;
			Logger::addLog('Error occured when submitting an order to Riskified.');
		}
	}
	die;
}

$order_id = Tools::getValue('id_order');
$today = date('Ymd');
$validation_token = Tools::getAdminToken('riskifiedAjax'.$today);
if ($validation_token == Tools::getValue('token'))
{
	$riskified = new Riskified();
	if ($riskified->curlExists())
	{
		$data = $riskified->getData($order_id);
		$url = $riskified->getRiskifiedUrl();
		$result = fireCurl($data, $url);
		$pattern = '/"status":"([a-zA-Z]*?)"/i';
		preg_match($pattern, $result, $output_array);
		$msg = '/"message":"([a-zA-Z\s]*?)"/i';
		preg_match($msg, $result, $output_ary);
	}
	else
		echo 'Failed to submit order to Riskified - The curl library is not installed. Please install.';
}
else
	echo 'Security exception occured while trying to send the order to Riskified (invalid token).';
?>
