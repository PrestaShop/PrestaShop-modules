<?php
/**
* 2007-2014 PrestaShop
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
* @author    Feedaty <info@feedaty.com>
* @copyright 2012-2014 Feedaty
* @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
* @version   Release: 1.1.135 $
*/

include('../../config/config.inc.php');

/* We made internal security checks */

$max_time = 60 * 10;

$idEmployee = (int)Tools::getValue('idEmployee');
$timeGenerated = (int)Tools::getValue('timeGenerated');

$cryptToken = md5($idEmployee._COOKIE_KEY_.$timeGenerated);

if (Tools::getValue('cryptToken') == $cryptToken && ((time() - $timeGenerated) < $max_time))
{
	/* Retrive all orders up to 3 months */
	$sql = 'SELECT *
			FROM '._DB_PREFIX_.'orders
			WHERE date_add > "'.date('c', strtotime('-3 months')).'"';

	$order_details  = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

	$final_products = array();

	if (is_array($order_details) && count($order_details) > 0)
		foreach ($order_details as $ar_order)
		{
			$order = new Order($ar_order['id_order']);

			if ($order->getCurrentState() == Configuration::get('feedaty_status_request'))
			{
				/* For each order we get id, date, customer email, prestashop version and some product infos */
				$products = $order->getProducts();
				$tmp_order = array();
				$tmp_order['OrderId'] = $ar_order['id_order'];
				$tmp_order['OrderDate'] = $ar_order['date_add'];
				$customer = new Customer((int)$order->id_customer);
				$tmp_order['CustomerEmail'] = $customer->email;
				$tmp_order['CustomerId'] = $customer->email;
				$tmp_order['Platform'] = 'PrestaShop '._PS_VERSION_.' CSV';

				if (is_array($products) && (count($products) > 0))
				{
					foreach ($products as $product)
					{
						/* For each product we collect image url, url, name, id */
						$tmp = $tmp_order;

						$id_image = Product::getCover($product['product_id']);
						if (count($id_image) > 0)
						{
							$image = new Image($id_image['id_image']);
							$tmp['ImageUrl'] = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().'.jpg';
						}
						$tmp['Id'] = $product['product_id'];
						$tmp['Name'] = $product['product_name'];
						$tmp['Brand'] = '';
						$link = new Link();
						$tmp['Url'] = $link->getProductLink((int)$product['product_id']);

						$final_products[] = $tmp;
					}
				}
			}
		}

	/* CSV Header */
	$csv = '"Order ID","UserID","E-mail","Date","Product ID","Extra","Product Url","Product Image","Platform"'."\r\n";
	foreach ($final_products as $p)
	{
		/* Every row it's a product on order */
		$csv .= '"'.$p['OrderId'].'","'.$p['CustomerId'].'","'.$p['CustomerEmail'].'","'.$p['OrderDate'].'","'.$p['Id'].'","'.
			str_replace('"', '""', $p['Name']).'","'.str_replace('"', '""', $p['Url']).'","'.
			str_replace('"', '""', $p['ImageUrl']).'","'.str_replace('"', '""', $p['Platform']).'"'."\r\n";
	}

	/* Send header to force download of a file called export_date.csv */
	header('Cache-Control: public');
	header('Content-Description: File Transfer');
	header('Content-Disposition: attachment; filename=export_'.date('d_m_Y_H_i').'.csv');
	header('Content-Transfer-Encoding: binary');
	/* Print csv */
	echo $csv;
}
