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
 * versions in the future.If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2014 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once (dirname ( __FILE__ ).'../../../config/config.inc.php');

class DataHelperVersionFour extends DataHelper
{
	public function getLanguageQuery($lang_name, $value)
	{
		$sql = 'SELECT   LEFT(c.date_add,'.pSQL ( $value ).') as fix_date, COUNT(DISTINCT c.id_customer) as data FROM '._DB_PREFIX_.'customer as c JOIN
				'._DB_PREFIX_.'orders as o  on o.id_customer = c.id_customer JOIN
				'._DB_PREFIX_.'lang as l on  o.id_lang=l.id_lang AND  l.name="'.pSQL ( $lang_name['data'] ).'" AND
				c.date_add >= "'.pSQL ( $this->date_from ).'  00:00:00" AND c.date_add <= "'.pSQL ( $this->date_to ).' 23:59:59"
				GROUP BY LEFT( c.date_add,"'.pSQL ( $value ).'")';
		return $sql;
	}
	public function getOrdersQuery($value)
	{
		$sql = 'SELECT  LEFT(o.date_add,"'.pSQL ( $value ).'") as fix_date, COUNT(distinct o.date_add) as data FROM
				'._DB_PREFIX_.'orders o LEFT JOIN '._DB_PREFIX_.'order_history oh ON oh.id_order = o.id_order
				LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
				WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history WHERE date_add in 
				(SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order)) AND o.date_add BETWEEN
				'.$this->getDateBetween ().'GROUP BY LEFT(o.date_add,"'.pSQL ( $value ).'")';
		return $sql;
	}
	public function getProfitRevenueQuery($value)
	{
		$sql = 'SELECT LEFT(o.date_add,"'.pSQL ( $value ).'") as fix_date,
				ROUND(IFNULL((SUM( (d.product_price/cur.conversion_rate) * d.product_quantity )
				-SUM( d.product_quantity * (p.wholesale_price) )),0),2) AS  "profit",
				ROUND(SUM((d.product_price/cur.conversion_rate) * d.product_quantity ),2) as revenue
				FROM '._DB_PREFIX_.'orders o LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
				LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state 
				JOIN '._DB_PREFIX_.'order_detail AS d ON o.id_order = d.id_order JOIN  '._DB_PREFIX_.'product AS p ON d.product_id = p.id_product
				JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
				WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history 
				WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
				AND o.date_add between '.$this->getDateBetween().' GROUP BY LEFT(o.date_add,"'.pSQL ( $value ).'")';
		return $sql;
	}
	public function getOrdersTopQuery($tag)
	{
		$sql_first = 'SELECT COUNT(distinct o.date_add) as total FROM '._DB_PREFIX_.'orders o
							LEFT JOIN '._DB_PREFIX_.'order_history oh ON oh.id_order = o.id_order LEFT JOIN '._DB_PREFIX_.'order_state os 
							ON os.id_order_state = oh.id_order_state WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history 
							WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
							AND o.date_add BETWEEN '.$this->getDateBetween ().'';
		$sql_second = 'SELECT COUNT(distinct o.date_add) as total FROM '._DB_PREFIX_.'orders o
							LEFT JOIN '._DB_PREFIX_.'order_history oh ON oh.id_order = o.id_order 
							LEFT JOIN  '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
							WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
							WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
							AND o.date_add BETWEEN '.$this->getDateForPercent ( $tag ).' ';
		return array (
				'sql_first'  => $sql_first,
				'sql_second' => $sql_second
		);
	}
	public function getProfitTopQuery($tag)
	{
		$sql_first = 'SELECT ROUND(IFNULL((SUM((od.product_price/cur.conversion_rate) * od.product_quantity)
					- SUM(od.product_quantity * (p.wholesale_price))),0),2) 
					as total FROM
					'._DB_PREFIX_.'orders AS o 
					LEFT JOIN '._DB_PREFIX_.'order_history oh ON oh.id_order = o.id_order
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state 
					LEFT JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
					LEFT JOIN '._DB_PREFIX_.'order_detail AS od ON o.id_order = od.id_order
					LEFT JOIN '._DB_PREFIX_.'product AS p ON od.product_id = p.id_product
					WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history 
					WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
					AND o.date_add BETWEEN '.$this->getDateBetween ().'';
		$sql_second = 'SELECT ROUND(IFNULL((SUM((od.product_price/cur.conversion_rate) * od.product_quantity)
							- SUM(od.product_quantity * (p.wholesale_price))),0),2) 
							as total, cur.sign as currency_symbol FROM
							'._DB_PREFIX_.'orders AS o
							LEFT JOIN '._DB_PREFIX_.'order_history oh ON oh.id_order = o.id_order
							LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
							LEFT JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
							LEFT JOIN '._DB_PREFIX_.'order_detail AS od ON o.id_order = od.id_order
							LEFT JOIN '._DB_PREFIX_.'product AS p ON od.product_id = p.id_product
							WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
							WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
							AND o.date_add BETWEEN '.$this->getDateForPercent ( $tag ).' ';
		return array (
				'sql_first'  => $sql_first,
				'sql_second' => $sql_second
		);
	}
	public function getRevenueTopQuery($tag)
	{
		$sql_first = 'SELECT SUM((od.product_price/cur.conversion_rate) * od.product_quantity) as total
					FROM '._DB_PREFIX_.'orders AS o LEFT JOIN '._DB_PREFIX_.'order_detail AS od ON o.id_order = od.id_order 
					LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
					LEFT JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
					WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
					WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
					AND o.date_add BETWEEN '.$this->getDateBetween ().' ';
		$sql_second = 'SELECT SUM((od.product_price/cur.conversion_rate) * od.product_quantity) as total, cur.sign as currency_symbol
							FROM '._DB_PREFIX_.'orders AS o LEFT JOIN '._DB_PREFIX_.'order_detail AS od ON o.id_order = od.id_order
							LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
							LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
							LEFT JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
							WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
							WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
							AND o.date_add BETWEEN '.$this->getDateForPercent ( $tag ).' ';
		return array (
				'sql_first'  => $sql_first,
				'sql_second' => $sql_second
		);
	}
	public function getProductTopQuery()
	{
		$sql = 'SELECT SQL_CALC_FOUND_ROWS SUM(pro.product_quantity) AS  total,
            	pro.id_order AS  "pro#order_id",
            	pro.product_id AS  "pro#product_id",
            	p.reference AS  "p#reference",
            	pro.product_name AS "pro#product_name",
				pro.product_price AS  "pro#unit_price_tax_excl",
				cur.sign AS "currency_symbol",
				ROUND(IFNULL(SUM( pro.product_price * pro.product_quantity ),0),2) AS  "pro#total_price_tax_excl",
				ROUND(IFNULL((SUM( pro.product_price * pro.product_quantity ) - (pro.product_quantity * p.wholesale_price )),0),2) AS "profit"
            	FROM '._DB_PREFIX_.'order_detail AS pro
            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id'.$this->getFilterP ().'
				JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
				LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
				LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
            	JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
            	WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
				WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
            	AND o.date_add BETWEEN '.$this->getDateBetween ().''.$this->getSortP ();
		return $sql;
	}
	public function getBestCustomersQuery()
	{
		$sql = 'SELECT  count(distinct o.date_add) as totalValidOrders, c.id_customer,
    				c.firstname,c.lastname,c.email, 
					ROUND(SUM((d.product_price/cur.conversion_rate) * d.product_quantity ),2) as totalMoneySpent
					FROM '._DB_PREFIX_.'orders o LEFT JOIN '._DB_PREFIX_.'customer c ON c.id_customer = o.id_customer
					LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
					JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
					LEFT JOIN '._DB_PREFIX_.'order_detail AS d ON o.id_order = d.id_order
					WHERE os.invoice = 1  AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
					WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
				   	AND o.date_add   BETWEEN '.$this->getDateBetween ().'
					GROUP BY c.id_customer ORDER BY totalMoneySpent DESC';
		return $sql;
	}
	public function getBestCategories($tag = '')
	{
		$date_between = $this->getDateBetween ();
		if ($tag != '')
			$date_between = $this->getDateForPercent ( $tag );
		$sql_best_categories = 'SELECT  SQL_CALC_FOUND_ROWS p.id_category_default as id_category,
								cl.name as name,sum(pro.product_quantity) AS totalQuantitySold,
								IFNULL(SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity ),0) AS revenue,	
								ROUND(IFNULL(SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity ),0),2) AS  totalPriceSold,
								ROUND(IFNULL((SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity ) - (pro.product_quantity
								 * (p.wholesale_price) )),0),2) AS profit
				            	FROM '._DB_PREFIX_.'order_detail AS pro
				            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id
								JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
								LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
								LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
								JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
				                JOIN '._DB_PREFIX_.'category_lang as cl ON cl.id_category = p.id_category_default
				                WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
								WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
				                AND cl.id_lang = 1 AND o.date_add BETWEEN '.$date_between.'
								GROUP BY p.id_category_default ORDER BY totalPriceSold DESC LIMIT 5';
		$result = Db::getInstance ( _PS_USE_SQL_SLAVE_ )->executeS ( $sql_best_categories );
		$sql_best_categories_count = 'SELECT  SQL_CALC_FOUND_ROWS p.id_category_default as id_category,
								cl.name as name,sum(pro.product_quantity) AS totalQuantitySold,
								IFNULL(SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity ),0) AS revenue,
								ROUND(IFNULL(SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity ),0),2) AS  totalPriceSold,
								ROUND(IFNULL((SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity ) - (pro.product_quantity
								 * (p.wholesale_price) )),0),2) AS profit
				            	FROM '._DB_PREFIX_.'order_detail AS pro
				            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id
								JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
								LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
								LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
								JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
				                JOIN '._DB_PREFIX_.'category_lang as cl ON cl.id_category = p.id_category_default
				                WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
								WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
				                AND cl.id_lang = 1 AND o.date_add BETWEEN '.$date_between.'
								GROUP BY p.id_category_default ORDER BY totalPriceSold DESC';
		$bestcategoriescount = Db::getInstance ( _PS_USE_SQL_SLAVE_ )->executeS ( $sql_best_categories_count );
		if (count ( $bestcategoriescount ) > 5)
		{
			$category_id = array ();
			foreach ($result as $row)
				$category_id[] = $row['id_category'];
			$cat_id = implode ( ',', $category_id );
			$other_categories_sql = 'SELECT sum(pro.product_quantity) AS totalQuantitySold,
									SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity ) AS revenue,
									ROUND(IFNULL(SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity ),0),2) AS  totalPriceSold,
									ROUND(IFNULL((SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity ) - (pro.product_quantity
									 * (p.wholesale_price) )),0),2) AS profit
					            	FROM '._DB_PREFIX_.'order_detail AS pro
					            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id
									JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
									LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
									LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
									JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
					                JOIN '._DB_PREFIX_.'category_lang as cl ON cl.id_category = p.id_category_default
					                WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
									WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
									AND o.date_add BETWEEN '.$this->getDateBetween ().'
					                AND cl.id_lang = 1 and p.id_category_default NOT IN ('.$cat_id.')';
			$other_categories_result = Db::getInstance ( _PS_USE_SQL_SLAVE_ )->executeS ( $other_categories_sql );
			$result = array_merge ( $result, $other_categories_result );
		}
		$total_count = Db::getInstance ( _PS_USE_SQL_SLAVE_ )->getValue ( 'SELECT FOUND_ROWS()' );
		return array (
				'result' => $result,
				'totalCount' => $total_count
		);
	}
	public function getCustomersWithOrdersTotalQuery()
	{
		$sql = 'SELECT  * FROM '._DB_PREFIX_.'orders o  LEFT JOIN '._DB_PREFIX_.'customer c ON c.id_customer = o.id_customer
					LEFT JOIN '._DB_PREFIX_.'order_history oh ON oh.id_order = o.id_order
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
					WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
					WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
					AND o.date_add between '.$this->getDateBetween ().' '.$this->getFilterP ().'
					GROUP BY date(o.date_add),c.id_customer';
		return $sql;
	}
	public function getCustomersSalesQuery()
	{
		$sql = 'SELECT  count(distinct o.date_add) as total_orders, c.id_customer AS "c#id_customer", o.id_order AS "o#id_order",
					CONCAT( c.firstname,  " ", c.lastname ) AS  "name",
					c.firstname as "c#firstname",c.lastname as "c#lastname",
					c.email as "c#email",
					IF(s.name!="",s.name,"unknown") as "s#name",
					cou.id_country as "country_id",
					ROUND(IFNULL((SUM( (d.product_price/cur.conversion_rate) * d.product_quantity )
					- SUM( d.product_quantity * (p.wholesale_price) )),0),2) AS  "profit",
					ROUND(SUM((d.product_price/cur.conversion_rate) * d.product_quantity ),2) as "revenue",
					date(o.date_add) as "o#invoice_date",
					SUM( o.total_paid_real ) AS  "total paid real", SUM( o.total_paid ) AS  "total_paid"
					FROM '._DB_PREFIX_.'orders o
					LEFT JOIN '._DB_PREFIX_.'customer c ON c.id_customer = o.id_customer
					LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
					JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
					JOIN '._DB_PREFIX_.'address as a ON (c.id_customer=a.id_customer AND a.id_address=o.id_address_invoice) 
					JOIN '._DB_PREFIX_.'country as cou ON a.id_country=cou.id_country
					LEFT JOIN '._DB_PREFIX_.'state as s ON a.id_state = s.id_state
					LEFT JOIN '._DB_PREFIX_.'order_detail AS d ON o.id_order = d.id_order
					JOIN  '._DB_PREFIX_.'product AS p ON d.product_id = p.id_product
					WHERE os.invoice = 1  AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
					WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
					AND o.date_add between '.$this->getDateBetween ().' '.$this->getFilterP ().'
					GROUP BY date(o.date_add),c.id_customer'.$this->getSortP ().$this->getLimitP ();
		return $sql;
	}
	public function getProductSalesQuery()
	{
		$sql = 'SELECT SQL_CALC_FOUND_ROWS SUM(pro.product_quantity) AS  "pro#quantity_sold",
	            	pro.id_order AS  "pro#order_id",
	            	pro.product_id AS  "pro#product_id",
	            	p.reference AS  "p#reference",
	            	pro.product_name AS "pro#product_name",
					pro.product_price AS  "pro#unit_price_tax_excl",
					ROUND(IFNULL(SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity ),0),2) AS  "pro#total_price_tax_excl",
					ROUND(IFNULL((SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity )
					- SUM(pro.product_quantity * (p.wholesale_price) )),0),2) AS "profit"
	            	FROM '._DB_PREFIX_.'order_detail AS pro
	            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id'.$this->getFilterP ().'
					JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
					LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
	            	JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
	            	WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
					WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order)) AND o.date_add BETWEEN '.$this->getDateBetween ().'
					GROUP BY pro.product_id'.$this->getSortP ().' '.$this->getLimitP ();
		return $sql;
	}
	public function getCategorySalesQuery()
	{
		$sql = 'SELECT  SQL_CALC_FOUND_ROWS p.id_category_default as "ca#id_category",
					cl.name as "calang#name",sum(pro.product_quantity) AS totalQuantitySold,  	
					cur.sign AS currency_symbol,
					ROUND(IFNULL(SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity ),0),2) AS  totalPriceSold,
					ROUND(IFNULL((SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity ) - 
					SUM( pro.product_quantity * (p.wholesale_price) )),0),2) AS profit
	            	FROM '._DB_PREFIX_.'order_detail AS pro
	            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id'.$this->getFilterP ().'
					JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
					LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
	            	JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
	                JOIN '._DB_PREFIX_.'category_lang as cl ON cl.id_category = p.id_category_default
	                WHERE os.invoice =1 AND cl.id_lang = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history 
					WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
	                AND o.date_add BETWEEN '.$this->getDateBetween ().'
					GROUP BY p.id_category_default '.$this->getSortP ().''.$this->getLimitP ();
		return $sql;
	}
	public function topLanguageQuery()
	{
		$sql = 'SELECT COUNT(c.id_customer) as data, l.name as name FROM
								'._DB_PREFIX_.'customer as c join 
								'._DB_PREFIX_.'orders as o  on o.id_customer = c.id_customer join
								'._DB_PREFIX_.'lang as l on  o.id_lang=l.id_lang AND
								c.date_add >= "'.pSQL ( $this->date_from ).'  00:00:00" AND c.date_add <= "'.pSQL ( $this->date_to ).' 23:59:59"
								GROUP BY o.id_lang';
		return $sql;
	}
}