<?php
/**
 * avisverifiesApi.php file used to execute query from AvisVerifies plateform
 *
 * @author    NetReviews (www.avis-verifies.com) - Contact: contact@avis-verifies.com
 * @category  api
 * @copyright NetReviews
 * @license   NetReviews 
 * @date 09/04/2014
 */

require('../../config/config.inc.php');
require('../../init.php');
include('netreviews.php');


$post_data = $_POST;

/*Check data received - Exit if no data received*/

if (!isset($post_data) || empty($post_data))
{
	$reponse = array();
	$reponse['debug'] = 'No POST DATA received';
	$reponse['return'] = 2;

	echo NetReviewsModel::acEncodeBase64(json_encode($reponse));
	exit;
}

/*Check module state | EXIT if error returned*/

$is_active_var = isActiveModule($post_data);

if ($is_active_var['return'] != 1)
{
	echo NetReviewsModel::acEncodeBase64(json_encode($is_active_var));
	exit;
}

/*Check module customer identification | EXIT if error returned*/

$check_security_var = checkSecurityData($post_data);

if ($check_security_var['return'] != 1)
{
	echo NetReviewsModel::acEncodeBase64(json_encode($check_security_var));
	exit;
}

/*############ START ############*/

/*Switch between each query allowed and sent by NetReviews*/
$to_reply = '';
switch ($post_data['query'])
{
	case 'isActiveModule':
		$to_reply = isActiveModule($post_data);
		break;
	case 'setModuleConfiguration' :
		$to_reply = setModuleConfiguration($post_data);
		break;
	case 'getModuleAndSiteConfiguration' :
		$to_reply = getModuleAndSiteConfiguration($post_data);
		break;
	case 'getOrders' :
		$to_reply = getOrders($post_data);
		break;
	case 'setProductsReviews' :
		$to_reply = setProductsReviews($post_data);
		break;
	case 'truncateTables' :
		$to_reply = truncateTables($post_data);
		break;
	default:
		break;
}



/*Displaying functions returns to NetReviews */

echo NetReviewsModel::acEncodeBase64(json_encode($to_reply));

/**
 * Check ID Api Customer
 * Every sent query depends on the return result of this function
 * @param $post_data
 * @return $reponse : error code + error
 */

function checkSecurityData(&$post_data)
{
	$reponse = array();
	/*get($key, $id_lang = null, $id_shop_group = null, $id_shop = null)*/

	$uns_msg = json_decode(NetReviewsModel::acDecodeBase64($post_data['message']),true);

	if (empty($uns_msg))
	{
		$reponse['debug'] = 'empty message';
		$reponse['return'] = 2;
		$reponse['query'] = 'checkSecurityData';
		/* Set query name because this query is called locally */
		return $reponse;
	}

	if (version_compare(_PS_VERSION_, '1.5', '>=') && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1)
	{
		if (!isset($uns_msg['id_shop']) || empty($uns_msg['id_shop']))
		{
			$reponse['debug'] = $uns_msg;
			$reponse['return'] = 2;
			$reponse['query'] = 'checkSecurityData';
			/* Set query name because this query is called locally */
			return $reponse;
		}
	}

	if (isset($uns_msg['id_shop']))
	{
		$local_id_website = Configuration::get('AVISVERIFIES_IDWEBSITE', null, null, $uns_msg['id_shop']);
		$local_secure_key = Configuration::get('AVISVERIFIES_CLESECRETE', null, null, $uns_msg['id_shop']);
	}
	else
	{
		$local_id_website = Configuration::get('AVISVERIFIES_IDWEBSITE');
		$local_secure_key = Configuration::get('AVISVERIFIES_CLESECRETE');
	}

	/*Check if ID clustomer are set locally*/
	if (!$local_id_website || !$local_secure_key)
	{
		$reponse['debug'] = 'Identifiants clients non renseignés sur le module';
		$reponse['message'] = 'Identifiants clients non renseignés sur le module';
		$reponse['return'] = 3;
		$reponse['query'] = 'checkSecurityData';
		/* Set query name because this query is called locally */
		return $reponse;
	}
	//Check if sent Idwebsite if the same as local
	elseif ($uns_msg['idWebsite'] != $local_id_website)
	{
		$reponse['message'] = 'Clé Website incorrecte';
		$reponse['debug'] = 'Clé Website incorrecte';
		$reponse['debug'] .= "\n Clé Local Client : ".$local_id_website;
		$reponse['debug'] .= "\n Clé AvisVerifies : ".$uns_msg['idWebsite'];
		$reponse['return'] = 4;
		$reponse['query'] = 'checkSecurityData';
		return $reponse;
	}
	//Check if sent sign if the same as local
	elseif (SHA1($post_data['query'].$local_id_website.$local_secure_key) != $uns_msg['sign'])
	{
		$reponse['message'] = 'La signature est incorrecte';
		$reponse['debug'] = 'La signature est incorrecte';
		$reponse['debug'] .= "\n Signature Client : ".SHA1($post_data['query'].$local_id_website.$local_secure_key);
		$reponse['debug'] .= "\n Signature AvisVerifies : ".$uns_msg['sign'];
		$reponse['return'] = 5;
		$reponse['query'] = 'checkSecurityData';
		return $reponse;
	}
	$reponse['message'] = 'Identifiants Client Ok';
	$reponse['debug'] = 'Identifiants Client Ok';
	$reponse['return'] = 1;
	$reponse['sign'] = SHA1($post_data['query'].$local_id_website.$local_secure_key);
	$reponse['query'] = 'checkSecurityData';
	return $reponse;

}


/* ############ END ############*/

/**############ FUNCTION ############ **/


/**
 * Website configuration update
 *
 * @param $post_data
 * Config Prestashop mis à jour : 
 * AVISVERIFIES_PROCESSINIT : (varchar) onorder or onorderstatuschange | Event which initiate the review request to customer 
 * AVISVERIFIES_ORDERSTATESCHOOSEN : (array) Array of choosen status to get orders
 * AVISVERIFIES_GETPRODREVIEWS : (varchar) yes or no | Get products reviews
 * AVISVERIFIES_DISPLAYPRODREVIEWS : (varchar) yes or no | Display products reviews
 * AVISVERIFIES_SCRIPTFIXE_ALLOWED : (varchar) yes or non | Display fix widget
 * AVISVERIFIES_SCRIPTFLOAT_ALLOWED: (varchar) yes or non | Display float widget
 * AVISVERIFIES_SCRIPTFIXE : (varchar) script Js | JS for fix widget
 * AVISVERIFIES_SCRIPTFIXE_POSITION : (varchar) left or right | Fix widget position
 * AVISVERIFIES_SCRIPTFLOAT : (varchar) script Js | JS for float widget
 * AVISVERIFIES_FORBIDDEN_EMAIL : (array) Domain name on emails for which we can't request reviews to customer
 * @return $reponse : error code + error
 */

function setModuleConfiguration(&$post_data)
{
	$reponse = array();

	$uns_msg = json_decode(NetReviewsModel::acDecodeBase64($post_data['message']),true);

	if (!empty($uns_msg))
	{
		if (isset($uns_msg['id_shop']))
		{
			/*Multisite case, id_shop was sent by query*/

			/*Multisite structure: updateValue($key, $values, $html = false, $id_shop_group = null, $id_shop = null) */

			Configuration::updateValue('AVISVERIFIES_PROCESSINIT', $uns_msg['init_reviews_process'], false, null, $uns_msg['id_shop']);

			/* Implode if more than one element so is_array */
			$orderstatechoosen = (is_array($uns_msg['id_order_status_choosen'])) ?
									implode(';', $uns_msg['id_order_status_choosen']) : $uns_msg['id_order_status_choosen'];
			Configuration::updateValue('AVISVERIFIES_ORDERSTATESCHOOSEN', $orderstatechoosen, false, null, $uns_msg['id_shop']);
			Configuration::updateValue('AVISVERIFIES_DELAY', $uns_msg['delay'], false, null, $uns_msg['id_shop']);
			Configuration::updateValue('AVISVERIFIES_GETPRODREVIEWS', $uns_msg['get_product_reviews'], false, null, $uns_msg['id_shop']);
			Configuration::updateValue('AVISVERIFIES_DISPLAYPRODREVIEWS', $uns_msg['display_product_reviews'], false, null, $uns_msg['id_shop']);
			Configuration::updateValue('AVISVERIFIES_SCRIPTFIXE_ALLOWED', $uns_msg['display_fixe_widget'], false, null, $uns_msg['id_shop']);
			Configuration::updateValue('AVISVERIFIES_SCRIPTFIXE_POSITION', $uns_msg['position_fixe_widget'], false, null, $uns_msg['id_shop']);
			Configuration::updateValue('AVISVERIFIES_SCRIPTFLOAT_ALLOWED', $uns_msg['display_float_widget'], false, null, $uns_msg['id_shop']);
			Configuration::updateValue('AVISVERIFIES_URLCERTIFICAT', $uns_msg['url_certificat'], false, null, $uns_msg['id_shop']);
			/* Implode if more than one element so is_array */
			$forbiddenemail = (is_array($uns_msg['forbidden_mail_extension'])) ?
									implode(';', $uns_msg['forbidden_mail_extension']) : $uns_msg['forbidden_mail_extension'];
			Configuration::updateValue('AVISVERIFIES_FORBIDDEN_EMAIL', $forbiddenemail, false, null, $uns_msg['id_shop']);
			Configuration::updateValue('AVISVERIFIES_SCRIPTFIXE',
										str_replace(array("\r\n", "\n"), '',
										$uns_msg['script_fixe_widget']), true, null,
										$uns_msg['id_shop']);
			Configuration::updateValue('AVISVERIFIES_SCRIPTFLOAT', str_replace(array("\r\n", "\n"), '',
										$uns_msg['script_float_widget']), true, null, $uns_msg['id_shop']);
			Configuration::updateValue('AVISVERIFIES_CODE_LANG', $uns_msg['code_lang'], false, null, $uns_msg['id_shop']);
			$reponse['sign'] = SHA1($post_data['query']
								.Configuration::get('AVISVERIFIES_IDWEBSITE', null, null, $uns_msg['id_shop'])
								.Configuration::get('AVISVERIFIES_CLESECRETE', null, null, $uns_msg['id_shop']));

			$reponse['message'] = getModuleAndSiteInfos($uns_msg['id_shop']);
		}
		else
		{
			Configuration::updateValue('AVISVERIFIES_PROCESSINIT', $uns_msg['init_reviews_process']);

			/* Implode if more than one element so is_array */
			$orderstatechoosen = (is_array($uns_msg['id_order_status_choosen'])) ?
									implode(';', $uns_msg['id_order_status_choosen']) : $uns_msg['id_order_status_choosen'];
			Configuration::updateValue('AVISVERIFIES_ORDERSTATESCHOOSEN', $orderstatechoosen);
			Configuration::updateValue('AVISVERIFIES_DELAY', $uns_msg['delay']);
			Configuration::updateValue('AVISVERIFIES_GETPRODREVIEWS', htmlentities($uns_msg['get_product_reviews']));
			Configuration::updateValue('AVISVERIFIES_DISPLAYPRODREVIEWS', htmlentities($uns_msg['display_product_reviews']));
			Configuration::updateValue('AVISVERIFIES_SCRIPTFIXE_ALLOWED', htmlentities($uns_msg['display_fixe_widget']));
			Configuration::updateValue('AVISVERIFIES_SCRIPTFIXE_POSITION', htmlentities($uns_msg['position_fixe_widget']));
			Configuration::updateValue('AVISVERIFIES_SCRIPTFLOAT_ALLOWED', htmlentities($uns_msg['display_float_widget']));
			Configuration::updateValue('AVISVERIFIES_URLCERTIFICAT', htmlentities($uns_msg['url_certificat']));
			/* Implode if more than one element so is_array */
			$forbiddenemail = (is_array($uns_msg['forbidden_mail_extension'])) ?
								implode(';', $uns_msg['forbidden_mail_extension']) : $uns_msg['forbidden_mail_extension'];
			Configuration::updateValue('AVISVERIFIES_FORBIDDEN_EMAIL', $forbiddenemail);
			Configuration::updateValue('AVISVERIFIES_SCRIPTFIXE', str_replace(array("\r\n", "\n"), '', $uns_msg['script_fixe_widget']), true);
			Configuration::updateValue('AVISVERIFIES_SCRIPTFLOAT', str_replace(array("\r\n", "\n"), '', $uns_msg['script_float_widget']), true);
			Configuration::updateValue('AVISVERIFIES_CODE_LANG', $uns_msg['code_lang']);

			$reponse['sign'] = SHA1($post_data['query'].Configuration::get('AVISVERIFIES_IDWEBSITE').Configuration::get('AVISVERIFIES_CLESECRETE'));
			$reponse['message'] = getModuleAndSiteInfos();

		}

		$reponse['debug'] = 'La configuration du site a été mise à jour';
		$reponse['return'] = 1;
		$reponse['query'] = $post_data['query'];

	}
	else
	{
		$reponse['debug'] = "Aucune données reçues par le site dans $_POST[message]";
		$reponse['message'] = "Aucune données reçues par le site dans $_POST[message]";
		$reponse['return'] = 2;
		$reponse['sign'] = (!empty($uns_msg['id_shop'])) ?
							SHA1($post_data['query']
							.Configuration::get('AVISVERIFIES_IDWEBSITE', null, null, $uns_msg['id_shop'])
							.Configuration::get('AVISVERIFIES_CLESECRETE', null, null, $uns_msg['id_shop'])) :
							SHA1($post_data['query']
							.Configuration::get('AVISVERIFIES_IDWEBSITE')
							.Configuration::get('AVISVERIFIES_CLESECRETE'));
		$reponse['query'] = $post_data['query'];
	}

	return $reponse;

}

/**
 * truncate content on tables av_products_reviews et av_products_average
 *
 * @param $post_data : sent parameters
 * @return $reponse : array to debug info
 */


function truncateTables(&$post_data)
{
	$reponse = array();
	$uns_msg = json_decode(NetReviewsModel::acDecodeBase64($post_data['message']),true);
	$query = array();
	$query[] = 'TRUNCATE TABLE '._DB_PREFIX_.'av_products_reviews;';
	$query[] = 'TRUNCATE TABLE '._DB_PREFIX_.'av_products_average;';

	$reponse['return'] = 1;
	$reponse['debug'] = 'Tables truncated';
	$reponse['message'] = 'Tables truncated';

	foreach ($query as $sql)
	{
		if (!Db::getInstance()->Execute($sql))
		{
			$reponse['return'] = 2;
			$reponse['debug'] = 'Tables not truncated';
			$reponse['message'] = 'Tables not truncated';
		}
	}

	$reponse['sign'] = (!empty($uns_msg['id_shop'])) ?
							SHA1($post_data['query']
								.Configuration::get('AVISVERIFIES_IDWEBSITE', null, null, $uns_msg['id_shop'])
								.Configuration::get('AVISVERIFIES_CLESECRETE', null, null, $uns_msg['id_shop'])) :
							SHA1($post_data['query']
								.Configuration::get('AVISVERIFIES_IDWEBSITE')
								.Configuration::get('AVISVERIFIES_CLESECRETE'));
	$reponse['query'] = $uns_msg['query'];
	return $reponse;

}


/**
 * Check if module is installed and enabled
 *
 * @param $post_data : sent parameters
 * @return state
 */

function isActiveModule(&$post_data)
{
	$reponse = array();
	$active = false;
	$uns_msg = json_decode(NetReviewsModel::acDecodeBase64($post_data['message']),true);

	if (!empty($uns_msg['id_shop']))
	{
		$id_module = Db::getInstance()->getValue('SELECT id_module FROM '._DB_PREFIX_.'module WHERE name = \'netreviews\'');
		if (Db::getInstance()->getValue('SELECT id_module FROM '._DB_PREFIX_.'module_shop WHERE id_module = '
										.(int)$id_module.' AND id_shop = '.(int)$uns_msg['id_shop']))
			$active = true;
	}
	else
	{
		if (Db::getInstance()->getValue('SELECT active FROM '._DB_PREFIX_.'module WHERE name LIKE \'netreviews\''))
			$active = true;
	}

	if (!$active)
	{
		$reponse['debug'] = 'Module disabled';
		$reponse['return'] = 2; //Module disabled
		$reponse['query'] = 'isActiveModule';
		return $reponse;

	}

	$reponse['debug'] = 'Module installed and enabled';
	$reponse['sign'] = (!empty($uns_msg['id_shop'])) ?
							SHA1($post_data['query']
								.Configuration::get('AVISVERIFIES_IDWEBSITE', null, null, $uns_msg['id_shop'])
								.Configuration::get('AVISVERIFIES_CLESECRETE', null, null, $uns_msg['id_shop'])) :
							SHA1($post_data['query']
								.Configuration::get('AVISVERIFIES_IDWEBSITE')
								.Configuration::get('AVISVERIFIES_CLESECRETE'));
	$reponse['return'] = 1; /*Module OK*/
	$reponse['query'] = $post_data['query'];

	return $reponse;
}

/**
 * Get module and site configuration
 *
 * @param $post_data : sent parameters
 * @return $reponse : array to debug info
 */

function getModuleAndSiteConfiguration(&$post_data)
{
	$reponse = array();
	$uns_msg = json_decode(NetReviewsModel::acDecodeBase64($post_data['message']),true);
	if (!empty($uns_msg['id_shop']))
	{
		$reponse['message'] = getModuleAndSiteInfos($uns_msg['id_shop']);
		$reponse['sign'] = SHA1($uns_msg['query'].Configuration::get('AVISVERIFIES_IDWEBSITE', null, null, $uns_msg['id_shop'] )
							.Configuration::get('AVISVERIFIES_CLESECRETE', null, null, $uns_msg['id_shop']));
	}
	else
	{
		$reponse['message'] = getModuleAndSiteInfos();
		$reponse['sign'] = SHA1($uns_msg['query'].Configuration::get('AVISVERIFIES_IDWEBSITE')
							.Configuration::get('AVISVERIFIES_CLESECRETE'));
	}

	$reponse['query'] = $uns_msg['query'];

	if (empty($reponse['message']))
		$reponse['return'] = 2;
	else
		$reponse['return'] = 1;

	return $reponse;

}


/**
 * Get orders
 *
 * @param $query : $post_data
 * @return orders (array)
 */

function getOrders(&$post_data)
{
	$reponse = array();
	$post_message = json_decode(NetReviewsModel::acDecodeBase64($post_data['message']),true);
	if (!empty($post_message['id_shop']))
	{

		$allowed_products = Configuration::get('AVISVERIFIES_GETPRODREVIEWS', null, null, $post_message['id_shop']);
		$process_choosen = Configuration::get('AVISVERIFIES_PROCESSINIT', null, null, $post_message['id_shop']);
		$forbidden_mail_extensions = explode(';', Configuration::get('AVISVERIFIES_FORBIDDEN_EMAIL', null, null, $post_message['id_shop']));
	}
	else
	{
		$allowed_products = Configuration::get('AVISVERIFIES_GETPRODREVIEWS');
		$process_choosen = Configuration::get('AVISVERIFIES_PROCESSINIT');
		$forbidden_mail_extensions = explode(';', Configuration::get('AVISVERIFIES_FORBIDDEN_EMAIL'));
	}

	$query_iso_lang = '';
	$query_id_shop = '';
	if (isset($post_message['iso_lang']))
	{
		$o_lang = new Language;
		$id_lang = $o_lang->getIdByIso(Tools::strtolower($post_message['iso_lang']));
		$query_iso_lang = ' AND o.id_lang = '.(int)$id_lang;
	}

	if ($process_choosen == 'onorder' || $process_choosen == 'onorderstatuschange')
	{
		if (!empty($post_message['id_shop']))
			$query_id_shop = ' AND oav.id_shop = '.(int)$post_message['id_shop'];

		$query = ' 	SELECT oav.horodate_now as date_last_status_change, oav.id_order, oav.id_order_state, o.date_add as date_order,
					oav.horodate_get as av_horodate_get, o.id_customer, oav.flag_get as av_flag
					FROM '._DB_PREFIX_.'av_orders oav
					LEFT JOIN '._DB_PREFIX_.'orders o
					ON oav.id_order = o.id_order
					WHERE (oav.flag_get IS NULL OR oav.flag_get = 0)'
					.$query_id_shop.$query_iso_lang;

		$orders_list = Db::getInstance()->ExecuteS($query);

		$reponse['debug'][] = $query;
		$reponse['debug']['mode'] = '['.$process_choosen.'] '.Db::getInstance()->numRows().' commandes récupérées';

	}
	else
	{
		$reponse['debug'][] = "no event onorder or onorderstatuschange selected";
		$reponse['return'] = 3;
		return $reponse;
	}

	$orders_list_toreturn = array();

	foreach ($orders_list as $order)
	{
		/* Test if customer email domain is forbidden (marketplaces case) */
		$o_customer = new Customer($order['id_customer']);
		$customer_email_extension = explode('@', $o_customer->email);

		if (!in_array($customer_email_extension[1], $forbidden_mail_extensions))
		{
			$array_order = array(
				'id_order' => $order['id_order'],
				'id_customer' => $order['id_customer'],
				'date_order' => strtotime($order['date_order']), /* date timestamp in orders table*/
				'date_order_formatted' => $order['date_order'], /* date in orders table formatted*/
				'date_last_status_change' => $order['date_last_status_change'], /* last status change date */
				'date_av_getted_order' => $order['av_horodate_get'], /* date Netreviews getted order */
				'is_flag' => $order['av_flag'], /* if order already flag */
				'state_order' => $order['id_order_state'],
				'firstname_customer' => $o_customer->firstname,
				'lastname_customer' => $o_customer->lastname,
				'email_customer' => $o_customer->email,
				'products' => array()
				);

			/*  Add products to array */
			if (!empty($allowed_products) && $allowed_products == 'yes')
			{
				$o_order = new Order($order['id_order']);
				$products_in_order = $o_order->getProducts();

				$array_products = array();

				foreach ($products_in_order as $element)
				{
					$product = array(
						'id_product' => $element['product_id'],
						'name_product' => $element['product_name']
					);

					$array_products[] = $product;
					unset($product);
				}

				$array_order['products'] = $array_products;
				unset($array_products);
			}

			$orders_list_toreturn[$order['id_order']] = $array_order;
		}
		else
			$reponse['message']['Emails_Interdits'][] = 'Commande n°'.$order['id_order'].' Email:'.$o_customer->email;

		/* Set orders as getted but do not if it's a test request */
		if (!isset($post_message['no_flag']) || $post_message['no_flag'] == 0)
			Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'av_orders SET horodate_get = "'
										.time().'", flag_get = 1 WHERE id_order = '.(int)$order['id_order']);
	}

	/* Purge Table */
	$nb_orders_purge = Db::getInstance()->getValue('SELECT count(id_order) FROM '
													._DB_PREFIX_.'av_orders 
													WHERE horodate_now < DATE_SUB(NOW(), INTERVAL 6 MONTH)');
	$reponse['debug']['purge'] = '[purge] '.$nb_orders_purge.' commandes purgées';

	Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'av_orders WHERE horodate_now < DATE_SUB(NOW(), INTERVAL 6 MONTH)');

	$reponse['return'] = 1;
	$reponse['sign'] = (!empty($post_message['id_shop'])) ?
						SHA1($post_message['query']
							.Configuration::get('AVISVERIFIES_IDWEBSITE', null, null, $post_message['id_shop'])
							.Configuration::get('AVISVERIFIES_CLESECRETE', null, null, $post_message['id_shop'])) :
						SHA1($post_data['query']
						.Configuration::get('AVISVERIFIES_IDWEBSITE')
						.Configuration::get('AVISVERIFIES_CLESECRETE'));
	$reponse['query'] = $post_message['query'];
	$reponse['message']['nb_orders'] = count($orders_list_toreturn);
	$reponse['message']['delay'] = (!empty($post_message['id_shop'])) ?
										Configuration::get('AVISVERIFIES_DELAY', null, null, $post_message['id_shop']) :
										Configuration::get('AVISVERIFIES_DELAY');
	$reponse['message']['list_orders'] = $orders_list_toreturn;
	$reponse['debug']['force'] = $post_message['force'];
	$reponse['debug']['no_flag'] = $post_message['no_flag'];
	return $reponse;

}


/**
 * Product reviews update
 *
 * @param $post_data : sent parameters
 * @return 
 */

function setProductsReviews(&$post_data)
{
	$reponse = array();
	$microtime_deb = microtime();
	$message = json_decode(NetReviewsModel::acDecodeBase64($post_data['message']),true);
	$reviews = (!empty($message['data'])) ? $message['data'] : null;
	$arra_line_reviews = (!empty($reviews)) ? explode("\n", $reviews) : array(); /* Line array (separator \n) */
	$count_line_reviews = count($arra_line_reviews);
	$count_update_new = 0;
	$count_delete = 0;
	$count_error = 0;

	foreach ($arra_line_reviews as $line_review)
	{
		$arra_column = explode("\t", $line_review); /* Get column in each line to save in an array (separator \t = tab) */
		$count_column = count($arra_column);

		/* Check if NEW or UPDATE ou DELETE exist */
		if (!empty($arra_column[0]))
		{
			if ($arra_column[0] == 'NEW' || $arra_column[0] == 'UPDATE')
			{
				if (isset($arra_column[11]) && $arra_column[11] > 0) /*Check if there is a discussion on this reviews (in 11)*/
				{
					if (($arra_column[11] * 3 + 12) == $count_column) /*3 data by message in discussion*/
					{
						for ($i = 0; $i < $arra_column[11]; $i++)
						{
							$arra_column['discussion'][] = array(
													'horodate' => $arra_column[11 + ($i * 3) + 1],
													'origine' => $arra_column[11 + ($i * 3) + 2],
													'commentaire' => $arra_column[11 + ($i * 3) + 3],
												);
						}

						Db::getInstance()->Execute('REPLACE INTO '._DB_PREFIX_.'av_products_reviews 
													(id_product_av, ref_product, rate, review, horodate, customer_name, discussion)
													VALUES (\''.pSQL($arra_column[2]).'\',
														\''.intval($arra_column[4]).'\',
														\''.floatval($arra_column[7]).'\',
														\''.pSQL($arra_column[6]).'\',
														\''.pSQL($arra_column[5]).'\',
														\''.pSQL(Tools::ucfirst($arra_column[8][0]).'. '.Tools::ucfirst($arra_column[9])).'\',
														\''.NetReviewsModel::acEncodeBase64(json_encode($arra_column['discussion'])).'\'
													)');

						$count_update_new++;

					}
					else
					{
						$reponse['debug'][$arra_column[2]] = 'Incorrect number of parameters in the line (Number of messages : '
																.$arra_column[11].')  : '
																.$count_column;
						$count_error++;
					}

				}
				elseif ((!isset($arra_column[11]) || empty($arra_column[11]) || $arra_column[11] == 0)) /*  No discussion */
				{
					if (($arra_column[11] * 3 + 12) == count($arra_column))
					{
						Db::getInstance()->Execute('REPLACE INTO '._DB_PREFIX_.'av_products_reviews 
													(id_product_av, ref_product, rate, review, horodate, customer_name, discussion)
													VALUES (\''.pSQL($arra_column[2]).'\',
														\''.intval($arra_column[4]).'\',
														\''.floatval($arra_column[7]).'\',
														\''.pSQL($arra_column[6]).'\',
														\''.pSQL($arra_column[5]).'\',
														\''.urlencode(Tools::ucfirst($arra_column[8][0]).'. '.Tools::ucfirst($arra_column[9])).'\',
														null
													)');

						$count_update_new++;
					}
					else
					{
						$reponse['debug'][$arra_column[2]] = 'Incorrect number of parameters in the line (Number of messages : '
															.$arra_column[11].')  : '.$count_column;
						$count_error++;
					}
				}

			}
			elseif ($arra_column[0] == 'DELETE')
			{
				Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'av_products_reviews 
											WHERE id_product_av = \''.pSQL($arra_column[2]).'\' AND ref_product = \''.(int)$arra_column[4].'\'');
				$count_delete++;
			}
			elseif ($arra_column[0] == 'AVG') /*AVG id_product_av ref_product rate nb_reviews */
			{
				Db::getInstance()->Execute('REPLACE INTO '._DB_PREFIX_.'av_products_average 
											(id_product_av, ref_product, rate, nb_reviews, horodate_update)
											VALUES (\''.$arra_column[1].'\',
													\''.$arra_column[2].'\',
													\''.$arra_column[3].'\',
													\''.$arra_column[4].'\',
													\''.time().'\'
												)
											');
				$count_update_new++;
			}
			else
			{
				$reponse['debug'][$arra_column[2]] = 'No action (NEW, UPDATE, DELETE) sent : ['.$arra_column[0].']';
				$count_error++;
			}
		}
	}

	$microtime_fin = microtime();
	$reponse['return'] = 1;
	$reponse['sign'] = (!empty($message['id_shop'])) ?
							SHA1($post_data['query']
								.Configuration::get('AVISVERIFIES_IDWEBSITE', null, null, $message['id_shop'])
								.Configuration::get('AVISVERIFIES_CLESECRETE', null, null, $message['id_shop'])) :
							SHA1($post_data['query']
								.Configuration::get('AVISVERIFIES_IDWEBSITE')
								.Configuration::get('AVISVERIFIES_CLESECRETE'));
	$reponse['query'] = $post_data['query'];
	$reponse['message']['lignes_recues'] = $arra_line_reviews;
	$reponse['message']['nb_update_new'] = $count_update_new;
	$reponse['message']['nb_delete'] = $count_delete;
	$reponse['message']['nb_errors'] = $count_error;
	$reponse['message']['microtime'] = $microtime_fin - $microtime_deb;

	if ($count_line_reviews != ($count_update_new + $count_delete + $count_error))
		$reponse['debug'][] = 'An error occured. Numbers of line received is not the same as line saved in DB';

	return $reponse;

}

/**
 * Get module and site infos
 * Private function, do not use it. This function is called in setModuleConfiguration and getModuleConfiguration
 * @param $post_data
 * @return array with info data
 */

function getModuleAndSiteInfos($id_shop = null)
{
	$module_version = new NetReviews;
	$module_version = $module_version->version;
	$order_statut_list = OrderState::getOrderStates((int)Configuration::get('PS_LANG_DEFAULT'));
	$perms = fileperms(_PS_MODULE_DIR_.'netreviews');

	if (($perms & 0xC000) == 0xC000)
		// Socket
		$info = 's';
	elseif (($perms & 0xA000) == 0xA000)
		// Symbolic link
		$info = 'l';
	elseif (($perms & 0x8000) == 0x8000)
		// Regular
		$info = '-';
	elseif (($perms & 0x6000) == 0x6000)
		// Block special
		$info = 'b';
	elseif (($perms & 0x4000) == 0x4000)
		// Repository
		$info = 'd';
	elseif (($perms & 0x2000) == 0x2000)
		// Special characters
		$info = 'c';
	elseif (($perms & 0x1000) == 0x1000)
		// pipe FIFO
		$info = 'p';
	else
		// Unknow
		$info = 'u';

	// Others
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ?
				(($perms & 0x0800) ? 's' : 'x' ) :
				(($perms & 0x0800) ? 'S' : '-'));

	// Group
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ?
				(($perms & 0x0400) ? 's' : 'x' ) :
				(($perms & 0x0400) ? 'S' : '-'));

	// All
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ?
				(($perms & 0x0200) ? 't' : 'x' ) :
				(($perms & 0x0200) ? 'T' : '-'));

	if (!empty($id_shop))
	{
		$explode_secret_key = explode('-', Configuration::get('AVISVERIFIES_CLESECRETE', null, null, $id_shop));
		$return = array(
			'Version_PS' => _PS_VERSION_,
			'Version_Module' => $module_version,
			'idWebsite' => Configuration::get('AVISVERIFIES_IDWEBSITE', null, null, $id_shop),
			'Nb_Multiboutique' => '',
			'Websites' => '',
			'Id_Website_encours' => '',
			'Cle_Secrete' => $explode_secret_key[0].'-xxxx-xxxx-'.$explode_secret_key[3],
			'Delay' => Configuration::get('AVISVERIFIES_DELAY', null, null, $id_shop),
			'Initialisation_du_Processus' => Configuration::get('AVISVERIFIES_PROCESSINIT', null, null, $id_shop),
			'Statut_choisi' => Configuration::get('AVISVERIFIES_ORDERSTATESCHOOSEN', null, null, $id_shop),
			'Recuperation_Avis_Produits' => Configuration::get('AVISVERIFIES_GETPRODREVIEWS', null, null, $id_shop),
			'Affiche_Avis_Produits' => Configuration::get('AVISVERIFIES_DISPLAYPRODREVIEWS', null, null, $id_shop),
			'Affichage_Widget_Flottant' => Configuration::get('AVISVERIFIES_SCRIPTFLOAT_ALLOWED', null, null, $id_shop),
			'Script_Widget_Flottant' => Configuration::get('AVISVERIFIES_SCRIPTFLOAT', null, null, $id_shop),
			'Affichage_Widget_Fixe' => Configuration::get('AVISVERIFIES_SCRIPTFIXE_ALLOWED', null, null, $id_shop),
			'Position_Widget_Fixe' => Configuration::get('AVISVERIFIES_SCRIPTFIXE_POSITION', null, null, $id_shop),
			'Script_Widget_Fixe' => Configuration::get('AVISVERIFIES_SCRIPTFIXE', null, null, $id_shop),
			'Emails_Interdits' => Configuration::get('AVISVERIFIES_FORBIDDEN_EMAIL', null, null, $id_shop),
			'Liste_des_statuts' => $order_statut_list,
			'Droit_du_dossier_AV' => $info,
			'Date_Recuperation_Config' => date('Y-m-d H:i:s')

		);

	}
	else
	{
		$explode_secret_key = explode('-', Configuration::get('AVISVERIFIES_CLESECRETE'));
		$return = array(
			'Version_PS' => _PS_VERSION_,
			'Version_Module' => $module_version,
			'idWebsite' => Configuration::get('AVISVERIFIES_IDWEBSITE'),
			'Nb_Multiboutique' => '',
			'Websites' => '',
			'Id_Website_encours' => '',
			'Cle_Secrete' => $explode_secret_key[0].'-xxxx-xxxx-'.$explode_secret_key[3],
			'Delay' => Configuration::get('AVISVERIFIES_DELAY'),
			'Initialisation_du_Processus' => Configuration::get('AVISVERIFIES_PROCESSINIT'),
			'Statut_choisi' => Configuration::get('AVISVERIFIES_ORDERSTATESCHOOSEN'),
			'Recuperation_Avis_Produits' => Configuration::get('AVISVERIFIES_GETPRODREVIEWS'),
			'Affiche_Avis_Produits' => Configuration::get('AVISVERIFIES_DISPLAYPRODREVIEWS'),
			'Affichage_Widget_Flottant' => Configuration::get('AVISVERIFIES_SCRIPTFLOAT_ALLOWED'),
			'Script_Widget_Flottant' => Configuration::get('AVISVERIFIES_SCRIPTFLOAT'),
			'Affichage_Widget_Fixe' => Configuration::get('AVISVERIFIES_SCRIPTFIXE_ALLOWED'),
			'Position_Widget_Fixe' => Configuration::get('AVISVERIFIES_SCRIPTFIXE_POSITION'),
			'Script_Widget_Fixe' => Configuration::get('AVISVERIFIES_SCRIPTFIXE'),
			'Emails_Interdits' => Configuration::get('AVISVERIFIES_FORBIDDEN_EMAIL'),
			'Liste_des_statuts' => $order_statut_list,
			'Droit_du_dossier_AV' => $info,
			'Date_Recuperation_Config' => date('Y-m-d H:i:s')
		);

	}

	if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') == 1)
	{
		$return['Nb_Multiboutique'] = Shop::getTotalShops();
		$return['Websites'] = Shop::getShops();
	}
	return $return;

}
?>