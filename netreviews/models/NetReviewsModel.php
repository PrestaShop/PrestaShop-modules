<?php
/**
 * NetReviewsModel.php file used to execute specific queries
 *
 * @author    NetReviews (www.avis-verifies.com) - Contact: contact@avis-verifies.com
 * @category  classes
 * @copyright NetReviews
 * @license   NetReviews 
 * @date 09/04/2014
 */

class NetReviewsModel extends ObjectModel{

	protected $table = 'av_products_reviews';
	protected $identifier = 'id_product_av';

	public $reviews_by_page;
	public $id_order;
	public $id_order_state = null;
	public $id_shop = null;

	public function __construct()
	{
		$this->reviews_by_page = 10;
		//Be carefule, the frontcontroller pagination used in the main file in ProductTabContent
		//impose a number of 10 for pagination (according to the product numbers on page)
		//Changing this number will break the paginationn
	}


	public function getProductReviews($id_product, $count_reviews = false, $p = 1)
	{
		$p = (int)$p;
		$n = $this->reviews_by_page;

	if ($p <= 1) $p = 1;
	if ($n != null && $n <= 0) $n = 10;

	if ($count_reviews)
		return Db::getInstance()->getRow('SELECT COUNT(ref_product) as nbreviews FROM '._DB_PREFIX_.'av_products_reviews WHERE ref_product = '
											.(int)$id_product);
	else
		return Db::getInstance()->ExecuteS('	SELECT * FROM '._DB_PREFIX_.'av_products_reviews 
												WHERE ref_product = '.(int)$id_product.' ORDER BY horodate DESC
												'.((int)$n ? 'LIMIT '.(((int)$p - 1) * $n).', '.(int)$n : ''));
	}

	public function getStatsProduct($id_product)
	{
		return Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'av_products_average WHERE ref_product = '.(int)$id_product);

	}

	public function export($id_shop = null, $header_colums)
	{
		$o_netreviews = new NetReviews;

		$duree = Tools::getValue('duree');

		if (! empty($id_shop))
		{
			$file_name = Configuration::get('AVISVERIFIES_CSVFILENAME', null, null, $id_shop);
			$delay = (Configuration::get('AVISVERIFIES_DELAY', null, null, $id_shop)) ? Configuration::get('AVISVERIFIES_DELAY', null, null, $id_shop) : 0;

		}
		else
		{
			$file_name = Configuration::get('AVISVERIFIES_CSVFILENAME');
			$delay = (Configuration::get('AVISVERIFIES_DELAY')) ? Configuration::get('AVISVERIFIES_DELAY') : 0;
		}

		$avis_produit = Tools::getValue('productreviews');

		if (!empty($file_name))
		{
			$file_path = _PS_MODULE_DIR_.'netreviews/Export_NetReviews_'.str_replace('/', '', stripslashes($file_name));
			if (file_exists($file_path))
			{
				if (is_writable($file_path))
					unlink($file_path);
				else
					throw new Exception($o_netreviews->l('Writing on our server is not allowed. Please assign write permissions to the folder netreviews'));
			}
			else
			{
				foreach (glob(_PS_MODULE_DIR_.'netreviews/Export_NetReviews_*') as $filename_to_delete) {
					if (is_writable($filename_to_delete))
				   		unlink($filename_to_delete);
				}
			}
		}

		$file_name = date('d-m-Y').'-'.Tools::substr(md5(rand(0, 10000)), 1, 10).'.csv';
		$file_path = _PS_MODULE_DIR_.'netreviews/Export_NetReviews_'.$file_name;

		$duree_sql = '';
		switch ($duree)
		{
			case '1w':
				$duree_sql = 'INTERVAL 1 WEEK';

				break;
			case '2w':
				$duree_sql = 'INTERVAL 2 WEEK';
				break;
			case '1m':
				$duree_sql = 'INTERVAL 1 MONTH';
				break;
			case '2m':
				$duree_sql = 'INTERVAL 2 MONTH';
				break;
			case '3m':
				$duree_sql = 'INTERVAL 3 MONTH';
				break;
			case '4m':
				$duree_sql = 'INTERVAL 4 MONTH';
				break;
			case '5m':
				$duree_sql = 'INTERVAL 5 MONTH';
				break;
			case '6m':
				$duree_sql = 'INTERVAL 6 MONTH';
				break;
			case '7m':
				$duree_sql = 'INTERVAL 7 MONTH';
				break;
			case '8m':
				$duree_sql = 'INTERVAL 8 MONTH';
				break;
			case '9m':
				$duree_sql = 'INTERVAL 9 MONTH';
				break;
			case '10m':
				$duree_sql = 'INTERVAL 10 MONTH';
				break;
			case '11m':
				$duree_sql = 'INTERVAL 11 MONTH';
				break;
			case '12m':
				$duree_sql = 'INTERVAL 12 MONTH';
				break;
			default:
				$duree_sql = 'INTERVAL 1 WEEK';
				break;

		}

		$all_orders = array();

		// Get orders with choosen date interval
		$where_id_shop = (! empty($id_shop)) ?  'AND o.id_shop = '.(int)$id_shop  : '';
		$select_id_shop = (! empty($id_shop)) ?  ', o.id_shop' : '';

		$qry_sql = '		SELECT o.id_order, o.id_customer, o.date_add, c.firstname, c.lastname, c.email '.$select_id_shop.'
						FROM '._DB_PREFIX_.'orders o
						LEFT JOIN '._DB_PREFIX_.'customer c ON o.id_customer = c.id_customer
						WHERE (TO_DAYS(DATE_ADD(o.date_add,'.$duree_sql.')) - TO_DAYS(NOW())) >= 0
						'.$where_id_shop;

		$item_list = Db::getInstance()->ExecuteS($qry_sql);

		foreach ($item_list as $item)
		{

			$all_orders[$item['id_order']] = array(
				'ID_ORDER'     => $item['id_order'],
				'DATE_ORDER'   => date('d/m/Y', strtotime($item['date_add'])),
				'ID_CUSTOMER'  => array(
										'ID_CUSTOMER'  => $item['id_customer'],
										'FIRST_NAME'   => $item['firstname'],
										'LAST_NAME'    => $item['lastname'],
										'EMAIL'        => $item['email']
										),
				'EMAIL_CLIENT' => '',
				'NOM_CLIENT'   => '',
				'ORDER_STATE'  => '',
				'PRODUCTS'     => array()
			);

			$qry_sql = 'SELECT id_order, product_id, product_name FROM '._DB_PREFIX_.'order_detail WHERE id_order = '.(int)$item['id_order'];
			$product_list = Db::getInstance()->ExecuteS($qry_sql);
			foreach ($product_list as $product)
			{
				$all_orders[$product['id_order']]['PRODUCTS'][] = array(
					'ID_PRODUIT' => $product['product_id'],
					'NOM_PRODUIT' => $product['product_name']
				);

			}
		}

		if (count($all_orders) > 0)
		{
			if ($csv = @fopen($file_path, 'w'))
			{
				fwrite($csv, $header_colums);

				foreach ($all_orders as $order)
				{
					$count_products = count($order['PRODUCTS']);

					if ($avis_produit == 1 && $count_products > 0)
					{
						for ($i = 0; $i < $count_products; $i++)
						{
							$line   = '';//reset the line
							$line[] = $order['ID_ORDER'];
							$line[] = $order['ID_CUSTOMER']['EMAIL'];
							$line[] = utf8_decode($order['ID_CUSTOMER']['LAST_NAME']);
							$line[] = utf8_decode($order['ID_CUSTOMER']['FIRST_NAME']);
							$line[] = $order['DATE_ORDER'];
							$line[] = $delay;
							$line[] = $order['PRODUCTS'][$i]['ID_PRODUIT'];
							$line[] = ''; // Categorie du produit
							$line[] = utf8_decode($order['PRODUCTS'][$i]['NOM_PRODUIT']);
							$line[] = ''; //Url fiche produit
							$line[] = $order['ORDER_STATE']; //Etat de la commande
							if (! empty($id_shop)) $line[] = $id_shop;
							fwrite($csv, self::generateCsvLine($line));
						}
					}
					else
					{
						$line   = '';//reset the line
						$line[] = $order['ID_ORDER'];
						$line[] = $order['ID_CUSTOMER']['EMAIL'];
						$line[] = utf8_decode($order['ID_CUSTOMER']['LAST_NAME']);
						$line[] = utf8_decode($order['ID_CUSTOMER']['FIRST_NAME']);
						$line[] = $order['DATE_ORDER'];
						$line[] = $delay;
						$line[] = '';
						$line[] = ''; // Product category
						$line[] = '';
						$line[] = '';// URL
						$line[] = $order['ORDER_STATE']; //Order state
						if (! empty($id_shop)) $line[] = $id_shop;
						fwrite($csv, self::generateCsvLine($line));
					}
				}

				fclose($csv);

				
				if (file_exists($file_path))
				{
					Configuration::updateValue('AVISVERIFIES_CSVFILENAME', $file_name);
					return array($file_name, count($all_orders), $file_path);
				}
				else
				{
					throw new Exception($o_netreviews->l('Unable to read/write export file'));
				}				
			}
			else
			{
				throw new Exception($o_netreviews->l('Unable to read/write export file'));
			}
		}
		else
			throw new Exception($o_netreviews->l('No order to export'));
	}

	public function saveOrderToRequest()
	{
		$qry_order = 'SELECT id_order FROM '._DB_PREFIX_.'av_orders WHERE id_order = '.$this->id_order;

		$this->id_order_state = (!empty($this->id_order_state)) ? $this->id_order_state : 'NULL';
		$this->id_shop = (!empty($this->id_shop)) ? $this->id_shop : 'NULL';

		if (!Db::getInstance()->getRow($qry_order, false)) //Save order only if not exist in table
			Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'av_orders 
													(id_order, id_shop, id_order_state, id_lang_order)
													VALUES ('.$this->id_order.',
														'.$this->id_shop.',
														'.$this->id_order_state.',
														'.$this->id_lang_order.'
													)');
	}

	private static function generateCsvLine($list)
	{
		foreach ($list as &$l)
			$l = ''.addslashes($l).'';
		return implode(';', $list)."\r\n";
	}

	public static function acEncodeBase64($s_data)
	{
		$s_base64 = base64_encode($s_data);
		return strtr($s_base64, '+/', '-_');
	}


	public static function acDecodeBase64($s_data)
	{
		$s_base64 = strtr($s_data, '-_', '+/');
		return base64_decode($s_base64);
	}

}
