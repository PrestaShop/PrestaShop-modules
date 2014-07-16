<?php
/**
 * 2007-2014 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2014 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

include(dirname(__FILE__).'/datahelper.php');

function authenticate()
{
	if (!isset($_SERVER['PHP_AUTH_USER']))
	{
		header('WWW-Authenticate: Basic realm="Prestastats"');
		header('HTTP/1.0 401 Unauthorized');
		exit;
	}

	// check credentials
	$username = Configuration::get ( 'PS_PRESTASTATS_USER_LOGIN' );
	$password = Configuration::get ( 'PS_PRESTASTATS_USER_PASSWORD' );
	$api = Tools::strtolower ( Configuration::get ( 'PS_PRESTASTATS_API_KEY' ) );
	$api_set = Tools::strtolower ( Tools::getValue ( 'api' ) );
	if ($username == $_SERVER['PHP_AUTH_USER'] && $password == $_SERVER['PHP_AUTH_PW'] && $api == $api_set)
		return true;

	header('WWW-Authenticate: Basic realm="Prestastats"');
	header('HTTP/1.0 401 Unauthorized');
	// wrong login, password or api
	return false;
}

if (!authenticate())
{
	$customers = array();
	$customers['status'] = 500;
	$customers['message'] = 'Authentication Failed';
	echo Tools::jsonEncode($customers, JSON_NUMERIC_CHECK);
	exit;
}

$token = Tools::getAdminTokenLite('AdminModules');


$date_from = Tools::getValue('date_start');
$date_to = Tools::getValue('date_end');

$rangeselect = Tools::getValue('rangeselect');

if (isset($rangeselect))
	$method = Tools::getValue('rangeselect');
else
	$method = 'day';

$get_query = Tools::getValue('query');
if (isset($get_query))
	$data_type = Tools::getValue('query');
else
	$data_type = 'day';

try
{
	$timezone = Configuration::get('PS_TIMEZONE');
	$data = new DataHelper($date_from, $date_to);
	$data->setDateFrom($date_from);
	$data->setDateTo($date_to);

	if (isset($rangeselect))
		$data->setMethod($method);

	$data->setTimezone($timezone);
	$filter = null;
	$property = null;
	$direction = null;

	switch ($data_type)
	{
		case 'TOTAL_ORDERS':
			$customers_orders = $data->getOrders();
			$customers_orders['status'] = 200;
			echo Tools::jsonEncode($customers_orders);
			break;

		case 'PROFIT_REVENUE':
			$profit_revenue = $data->getProfitRevenue();
			$profit_revenue['status'] = 200;
			echo Tools::jsonEncode($profit_revenue);
			break;

		case 'TOTAL_VISIT':
			$total_visit = array();
			$visits = $data->getTotalVisits();
			$total_visit['data'] = array('total' => $visits['total'], 'percent' => $visits['percent'], 'sign' => $visits['sign']);
			$total_visit['status'] = 200;
			echo Tools::jsonEncode($total_visit);
			break;

		case 'TOTAL_VISITORS':
			$total_visitors = array();
			$visitors = $data->getTotalGuests();
			$total_visitors['data'] = array('total' => $visitors['total'], 'percent' => $visitors['percent'], 'sign' => $visitors['sign']);
			$total_visitors['status'] = 200;
			echo Tools::jsonEncode($total_visitors);
			break;

		case 'TOTAL_REGISTRATIONS':
			$total_registration = array();
			$total_register = $data->getTotalRegistrations();
			$total_registration['data'] = array('total' => $total_register['total'], 'percent' => $total_register['percent'],
				'sign' => $total_register['sign']);
			$total_registration['status'] = 200;
			echo Tools::jsonEncode($total_registration);
			break;

		case 'TOTAL_CUSTOMERS':
			$total_customers = $data->getCustomers();
			$total_customers['status'] = 200;
			echo Tools::jsonEncode($total_customers);
			break;

		case 'TOTAL_CUSTOMERS_BY_GENDER':
			$total_customers_by_gender = $data->getCustomersByGender();
			$total_customers_by_gender['status'] = 200;
			echo Tools::jsonEncode($total_customers_by_gender);
			break;

		case 'VISITOR_AND_REGISTRATION':
			$visitor_registration = $data->getVisitorsAndRegistration();
			$visitor_registration['status'] = 200;
			echo Tools::jsonEncode($visitor_registration);
			break;

		case 'CUSTOMER_BY_LANGUAGE':
			$customers_by_language = $data->getCustomerByLanguage();
			$customers_by_language['status'] = 200;
			echo Tools::jsonEncode($customers_by_language);
			break;

		case 'CUSTOMER_BY_REGION':
			$customers_by_region = $data->getCustomerByRegion();
			$customers_by_region['status'] = 200;
			echo Tools::jsonEncode($customers_by_region);
			break;

		case 'CUSTOMER_BY_CURRENCY':
			$customers_by_currency = $data->getCustomerByCurrency();
			$customers_by_currency['status'] = 200;
			echo Tools::jsonEncode($customers_by_currency);
			break;

		case 'CUSTOMER_BY_TOPSTATS':
			$customers_topstats = $data->topStatisticsCustomer();
			$customers_topstats['status'] = 200;
			echo Tools::jsonEncode($customers_topstats);
			break;

		case 'TOTAL_TOP_ORDER':
			$total_customers_top_order = array();
			$customers_top_order = $data->topStatisticsOrder();
			$total_customers_top_order['data'] = array('total' => $customers_top_order['total'], 'percent' => $customers_top_order['percent'],
				'sign' => $customers_top_order['sign']);
			$total_customers_top_order['status'] = 200;
			echo Tools::jsonEncode($total_customers_top_order);
			break;

		case 'TOTAL_TOP_VISIT':
			$total_customers_top_visit = array();
			$customers_top_visit = $data->getVisitTop();
			$total_customers_top_visit['data'] = array('total' => $customers_top_visit['total'], 'percent' => $customers_top_visit['percent'],
				'sign' => $customers_top_visit['sign']);
			$total_customers_top_visit['status'] = 200;
			echo Tools::jsonEncode($total_customers_top_visit);
			break;

		case 'TOTAL_TOP_PROFIT':
			$total_customers_top_profit = array();
			$customers_top_profit = $data->getProfitTop();
			$total_customers_top_profit['data'] = array('total' => $customers_top_profit['total'], 'percent' => $customers_top_profit['percent'],
				'sign' => $customers_top_profit['sign']);
			$total_customers_top_profit['status'] = 200;
			echo Tools::jsonEncode($total_customers_top_profit);
			break;

		case 'TOTAL_TOP_REVENUE':
			$total_revenue = array();
			$revenue = $data->getRevenueTop();
			$total_revenue['data'] = array('total' => $revenue['total'], 'percent' => $revenue['percent'], 'sign' => $revenue['sign']);
			$total_revenue['status'] = 200;
			echo Tools::jsonEncode($total_revenue);
			break;
		case 'TOTAL_TOP_PRODUCT':
			$total_product = array();
			$product = $data->getProductTop();
			$total_product['data'] = array('total' => $product['total']);
			$total_product['status'] = 200;
			echo Tools::jsonEncode($total_product);
			break;

		case 'BEST_CATEGORIES':
			$best_categories = $data->getBestCategoriesPie();
			$best_categories['status'] = 200;
			echo Tools::jsonEncode($best_categories);
			break;

		case 'CUSTOMERS_REGISTERED':
			$data->setStart(Tools::getValue('start'));
			$data->setLimit(Tools::getValue('limit'));
			$get_sort = Tools::getValue('sort');
			$get_property = Tools::getValue('property');
			if (isset($get_property) && isset($get_sort))
			{
				$direction = Tools::getValue('sort');
				$property = Tools::getValue('property');
			}

			$customers_registered = array();
			$data->setSort($direction);
			$data->setProperty($property);
			$get_filters = Tools::getValue('filters');
			if (isset($get_filters) && $get_filters != null)
				$data->parseFilters(Tools::getValue('filters'));

			$customers_registered['customers'] = $data->getCustomersRegistered();
			$customers_registered['total'] = count($data->getCustomersRegisteredTotal());
			$customers['status'] = 200;
			echo Tools::jsonEncode($customers_registered);
			break;

		case 'CUSTOMERS_SALES':

			$data->setStart(Tools::getValue('start'));
			$data->setLimit(Tools::getValue('limit'));
			$get_sort = Tools::getValue('sort');
			$get_property = Tools::getValue('property');
			if (isset($get_property) && isset($get_sort))
			{
				$direction = Tools::getValue('sort');
				$property = Tools::getValue('property');
			}

			$data->setSort($direction);
			$data->setProperty($property);
			$get_filters = Tools::getValue('filters');
			if (isset($get_filters) && $get_filters != null)
				$data->parseFilters(Tools::getValue('filters'));

			$customers_registered['customers'] = $data->getCustomersSales();
			$customers_registered['total'] = count($data->getCustomersWithOrdersTotal());
			$customers['status'] = 200;
			echo Tools::jsonEncode($customers_registered);
			break;

		case 'ABANDONED_CUSTOMER_CARTS':

			$data->setStart(Tools::getValue('start'));
			$data->setLimit(Tools::getValue('limit'));
			$get_sort = Tools::getValue('sort');
			$get_property = Tools::getValue('property');
			if (isset($get_property) && isset($get_sort))
			{
				$direction = Tools::getValue('sort');
				$property = Tools::getValue('property');
			}
			$data->setSort($direction);
			$data->setProperty($property);
			$get_filters = Tools::getValue('filters');
			if (isset($get_filters) && $get_filters != null)
				$data->parseFilters(Tools::getValue('filters'));
			$abandoned_carts_data = $data->getAbandonedCustomerCarts();
			$abandoned_carts = array();
			$abandoned_carts['abandoned_carts'] = $abandoned_carts_data['result'];
			$abandoned_carts['total'] = $abandoned_carts_data['total'];
			$abandoned_carts['totalcartvalue'] = $abandoned_carts_data['totalcartvalue'];
			echo Tools::jsonEncode($abandoned_carts);
			break;

		case 'PRODUCTS_SALES':

			$data->setStart(Tools::getValue('start'));
			$data->setLimit(Tools::getValue('limit'));
			$get_sort = Tools::getValue('sort');
			$get_property = Tools::getValue('property');
			if (isset($get_property) && isset($get_sort))
			{
				$direction = Tools::getValue('sort');
				$property = Tools::getValue('property');
			}

			$data->setSort($direction);
			$data->setProperty($property);
			$get_filters = Tools::getValue('filters');
			if (isset($get_filters) && $get_filters != null)
				$data->parseFilters(Tools::getValue('filters'));

			$products_sales_data = array();
			$products_sales = array();
			$products_sales_data = $data->getProductsSales();
			$products_sales['products_sales'] = $products_sales_data['result'];
			$products_sales['total'] = $products_sales_data['total'];
			echo Tools::jsonEncode($products_sales);
			break;

		case 'CATEGORY_SALES':

			$data->setStart(Tools::getValue('start'));
			$data->setLimit(Tools::getValue('limit'));
			$get_sort = Tools::getValue('sort');
			$get_property = Tools::getValue('property');
			if (isset($get_property) && isset($get_sort))
			{
				$direction = Tools::getValue('sort');
				$property = Tools::getValue('property');
			}

			$data->setSort($direction);
			$data->setProperty($property);
			$get_filters = Tools::getValue('filters');
			if (isset($get_filters) && $get_filters != null)
				$data->parseFilters(Tools::getValue('filters'));

			$category_sales = array();
			$category_sales_data = $data->getCategorySales();
			$category_sales['category_sales'] = $category_sales_data['result'];
			$category_sales['total'] = $category_sales_data['total'];
			echo Tools::jsonEncode($category_sales);
			break;

		case 'ORIGIN_TRAFFIC' :
			$origin_traffic = $data->getOriginTraffic();
			$origin_traffic['status'] = 200;
			echo Tools::jsonEncode($origin_traffic);
			break;

		default :
			$customers['message'] = 'Method not found';
			$customers['status'] = 500;
			echo Tools::jsonEncode($customers);


	}
}
catch (Exception $e)
{
	$customers['status'] = 500;
	$result = Tools::jsonEncode($customers);
	exit;
}
