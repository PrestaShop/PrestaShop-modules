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

require_once(dirname(__FILE__).'../../../config/config.inc.php');

class DataHelper extends ModuleGrid
{

	private $date_from;
	private $date_to;
	private $date_from_for_percent;
	private $date_to_for_percent;
	private $order = 'DESC';
	private $timezone = 'UTC';
	private $sql;
	private $method;
	private $start = 10;
	private $limit = 20;
	private $sort = 'DESC';
	private $property = 'null';
	private $filters = '';

	public function __construct($date_from, $date_to)
	{
		$this->date_from = date('y/m/d', $date_from);
		$this->date_to = date('y/m/d', $date_to);

	}

	public function __get($var)
	{
		return $var;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function setMethod($method)
	{
		$this->method = $method;
	}

	public function getSort()
	{
		return $this->sort;
	}

	public function setSort($sort)
	{
		$this->sort = $sort;
	}

	public function getProperty()
	{
		return $this->property;
	}

	public function setProperty($property)
	{
		$this->property = $property;
	}

	public function getStart()
	{
		return $this->start;
	}

	public function setStart($start)
	{
		$this->start = $start;
	}

	public function getLimit()
	{
		return $this->limit;
	}

	public function setLimit($limit)
	{
		$this->limit = $limit;
	}

	public function getFilter()
	{
		return $this->filters;
	}

	public function setFilter($filter)
	{
		$this->filter = $filter;
	}

	public function getComparison()
	{
		return $this->comparison;
	}

	public function setComparison($comparison)
	{
		$this->comparison = $comparison;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setType($type)
	{
		$this->type = $type;
	}

	public function verifyDate($date)
	{
		//return 1; // format date is required
		return (date('m-d-Y', strtotime($date)) !== false)? 1 : 0;
	}

	public function getDate($tag = '')
	{
		if ($tag != '')
			return true;

		return 'date_add >="'.pSQL($this->date_from).'  00:00:00" AND date_add <="'.pSQL($this->date_to).' 23:59:59" ';
	}

	public function getDateBetween()
	{
		return '"'.pSQL($this->date_from).' 00:00:00" AND "'.pSQL($this->date_to).' 23:59:59" ';
	}

	public function getLimitP()
	{
		return ' LIMIT '.(int)$this->start.','.(int)$this->limit;
	}

	public function getSortP()
	{
		if (Validate::isOrderBy($this->property) && Validate::isOrderWay($this->sort) && $this->property != null)
		{
			$this->addPrefixes($this->property);
			return ' ORDER BY '.pSQL($this->property).' '.pSQL($this->sort);
		}
		return '';
	}

	public function getFilterP()
	{
		return $this->filters;
	}

	public function getDateForPercent($tag = '')
	{
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow
		('SELECT DATEDIFF("'.pSQL($this->date_from).' 00:00:00", "'.pSQL($this->date_to).' 23:59:59") AS days');
		$days = isset($result['days']) ? $result['days'] : 0;
		$days = $days.' day';

		$format = 'y/m/d';
		$current = strtotime($this->date_from);
		$current = strtotime($days, $current);
		$date = date($format, $current);
		$this->date_from_for_percent = $date;
		$this->date_to_for_percent = $this->date_from;

		if ($tag != '')
				return '"'.pSQL($date).' 00:00:00" AND "'.pSQL($this->date_from).' 23:59:59" ';

		return 'date_add >= "'.pSQL($date).'00:00:00" AND date_add <= "'.pSQL($this->date_from).' 23:59:59" ';
	}

	public function addPrefixes(&$query)
	{
		$query = str_replace('#', '.', $query);
	}

	public function parseFilters($filters)
	{
		if (isset($filters) && $filters != null)
		{
			$filter_out = '';

			foreach ($filters as $k)
			{
				$type = $filters[$k]['data']['type'];
				// set filters depending on type
				if ($type == 'numeric' || $type == 'date' || $type == 'datetime')
				{
					$test = '';
					switch ($filters[$k]['data']['comparison'])
					{
						case 'gt':
							$test = '>"'.pSQL($filters[$k]['data']['value']).'"';
							break;
						case 'lt':
							$test = '<"'.pSQL($filters[$k]['data']['value']).'"';
							break;
						case 'eq':
							$test = '="'.pSQL($filters[$k]['data']['value']).'"';
							break;
					}

					$field = bqSQL($filters[$k]['field']);
					$filter_out = $filter_out.' AND `'.$field.'`'.$test;

				}
				else
				{
					$field = bqSQL($filters[$k]['field']);
					$filter_out = $filter_out.' AND `'.$field.'` LIKE "%'.
						pSQL($filters[$k]['data']['value']).'%"';
				}
			}
			$this->addPrefixes($filter_out);
			$this->filters = $filter_out;
			return 1;
		}
		return -1;
	}

	public function setDateTo($date)
	{
		if ($this->verifyDate($date))
		{
			$this->date_to = date('Y/m/d', $date);
			return 1;
		}
		return 0;
	}

	public function getDatePageViewed()
	{
		return 'date_add >= "'.pSQL($this->date_from).' 00:00:00" AND date_add <= "'.pSQL($this->date_to).' 23:59:59" ';
	}

	public function setOrder($set_order)
	{
		return (isset($set_order) && $set_order == 'ASC')? $this->order = 'ASC' : $this->order = 'DESC';
	}

	public function getDateFrom()
	{
		return $this->date_to;
	}

	public function setDateFrom($date)
	{
		if ($this->verifyDate($date))
		{
			$this->date_from = date('Y/m/d', $date);
			return 1;
		}
		return 0;
	}

	public function getTimezone()
	{
		return $this->timezone;
	}

	public function setTimezone($timezone)
	{
		if (!empty($timezone))
			$this->timezone = $timezone;
	}

	public function setSQL($sql_p)
	{
		$this->sql = $sql_p;
	}

	public function dateRange($date_start, $date_end, $step = '+1 day', $format = 'Y-m-d')
	{
		date_default_timezone_set('UTC'); // TEMPORARY, we may have to use local timezone, no documentation about that
		$dates = array();
		$current = strtotime($date_start);
		$last = strtotime($date_end);

		if ($format == 'Y')
		{
			$current = date('Y', strtotime($date_start));
			$last = date('Y', strtotime($date_end));

			while ($current <= $last)
			{
					$dates[$current] = 0;
					$current = $current + 1;
			}
		}
		else
		{
			while ($current <= $last)
			{
					$dates[date($format, $current)] = 0;
					$current = strtotime($step, $current);
			}
		}
		return $dates;
	}

	public function years($date1, $date2)
	{
		// return number of years since $date1
		if (isset($date2) && isset($date1) && $date2 != '0000-00-00')
		{
			$diff = abs(strtotime($date2) - strtotime($date1));
			return floor($diff / 31536000);
		}
		return 'unknown';
	}

	public function ordersConversion($visits, $orders)
	{
		return ($visits == 0 || $orders == 0)? 0 : round(($orders / $visits) * 100, 2, PHP_ROUND_HALF_UP);
	}
	public function getValues($values)
	{
		if (is_array($values))
		{
			$func = create_function('$val', 'return ($val == 0)? null : $val;');
			$values = array_map($func, $values);
		}
		return $values;
	}
	public function getTotal($original_value, $percentage_value)
	{
		if ($percentage_value == 0)
			$percentage_value = 1;
		if ($percentage_value != 1)
			$final = round ( $original_value / $percentage_value * 100, 2 ).' %';
		else
			$final = round ( $original_value / $percentage_value, 2 ).' %';
		if ($original_value > $percentage_value)
			$sign = '+ve';
		else
			$sign = '-ve';
		return array (
				'sign' => $sign,
				'final' => $final
		);
	}
	public function getCustomersQuery($value)
	{
		$sql = 'SELECT LEFT(date_add,"'.pSQL($value).'") as fix_date, COUNT(id_customer) as data FROM '._DB_PREFIX_.'customer WHERE
				'.$this->getDate().'GROUP BY LEFT( date_add,"'.pSQL($value).'")';
		return $sql;
	}
	public function getCustomersByGenderQuery($join, $value)
	{
		$sql = 'SELECT  LEFT(date_add, "'.pSQL($value).'") as fix_date, COUNT(id_customer) as data FROM
				'._DB_PREFIX_.'customer as c '.$join.' '.$this->getDate().'GROUP BY LEFT( date_add,"'.pSQL($value).'")';
		return $sql;
	}
	public function getLanguageQueryForVerion4($lang_name, $value)
	{
		$sql = 'SELECT   LEFT(c.date_add,"'.pSQL($value).'") as fix_date, COUNT(DISTINCT c.id_customer) as data FROM '._DB_PREFIX_.'customer as c JOIN
				'._DB_PREFIX_.'orders as o  on o.id_customer = c.id_customer JOIN
				'._DB_PREFIX_.'lang as l on  o.id_lang=l.id_lang AND  l.name="'.pSQL($lang_name['data']).'" AND
				c.date_add >= "'.pSQL($this->date_from).'  00:00:00" AND c.date_add <= "'.pSQL($this->date_to).' 23:59:59"
				GROUP BY LEFT( c.date_add,"'.pSQL($value).'")';
		return $sql;
	}
	public function getLanguageQueryForOtherVersion($lang_name, $value)
	{
		$sql =	'SELECT   LEFT(date_add,"'.pSQL($value).'") as fix_date, COUNT(id_customer) as data FROM '._DB_PREFIX_.'customer as c join
				'._DB_PREFIX_.'lang as l on  c.id_lang=l.id_lang AND l.name="'.pSQL($lang_name['data']).'" AND
				'.$this->getDate().'GROUP BY LEFT( date_add,"'.pSQL($value).'")';
		return $sql;
	}
	public function getCustomerByRegionQuery($lang_name, $value)
	{
		$sql = 'SELECT   LEFT(c.date_add,"'.pSQL($value).'") as fix_date, COUNT(DISTINCT c.id_customer) as data FROM
				'._DB_PREFIX_.'customer as c,'._DB_PREFIX_.'address as a, '._DB_PREFIX_.'country as cu,'._DB_PREFIX_.'zone as z WHERE
				c.id_customer = a.id_customer AND a.id_country=cu.id_country AND cu.id_zone=z.id_zone AND z.name="'.pSQL($lang_name['data']).'" AND 
				c.date_add >= "'.pSQL($this->date_from).'  00:00:00" AND c.date_add <= "'.pSQL($this->date_to).' 23:59:59"
				GROUP BY cu.id_zone, LEFT( c.date_add,"'.pSQL($value).'")';
		return $sql;
	}
	public function getCustomerByCountryQuery($value)
	{
		$sql = 'SELECT   LEFT(a.date_add,"'.pSQL($value).'") as fix_date, COUNT(DISTINCT a.id_customer) as data,z.name as name FROM
				       '._DB_PREFIX_.'address as a,   '._DB_PREFIX_.'country as c,'._DB_PREFIX_.'country_lang as z where
				       a.id_country=c.id_country AND c.id_country=z.id_country  AND '.$this->getDate().'GROUP BY c.id_country, LEFT( date_add,"'.pSQL($value).'")';
		return $sql;
	}
	public function getCustomerByCurrencyQuery($lang_name, $value)
	{
		$sql = 'select LEFT(o.date_add,"'.pSQL($value).'") as fix_date, COUNT(DISTINCT o.id_customer) 
				as data ,count(o.id_order) as order_data,c.name  as name from
				'._DB_PREFIX_.'orders as o join '._DB_PREFIX_.'currency as c on o.id_currency=c.id_currency WHERE o.date_add BETWEEN
				'.$this->getDateBetween().' AND  c.name="'.pSQL($lang_name['data']).'" group by o.id_currency, LEFT(o.date_add,"'.pSQL($value).'")';
		return $sql;
	}
	public function getVisitsQuery($value)
	{
	$sql = 'SELECT  LEFT(date_add,"'.pSQL($value).'") as fix_date, COUNT(*) as data FROM
			'._DB_PREFIX_.'connections WHERE '.$this->getDate().' GROUP BY LEFT( date_add,"'.pSQL($value).'")';
	return $sql;
	}
	public function getVisitorsAndRegistrationQuery($value)
	{
		$sql = 'SELECT LEFT(c.date_add,"'.pSQL($value).'") as fix_date,count(distinct g.id_guest) as data FROM 
				'._DB_PREFIX_.'guest g join '._DB_PREFIX_.'connections c ON 
				g.id_guest = c.id_guest AND g.id_customer = 0 WHERE c.date_add between '.$this->getDateBetween().'
				GROUP BY LEFT(c.date_add,"'.pSQL($value).'")';
		$sql_second = 'SELECT LEFT(c.date_add,"'.pSQL($value).'") as fix_date,count(g.id_customer) as data FROM '._DB_PREFIX_.'guest g 
						JOIN '._DB_PREFIX_.'customer c ON g.id_customer = c.id_customer AND g.id_customer != 0 
						WHERE c.date_add between '.$this->getDateBetween().' GROUP BY LEFT(c.date_add,"'.pSQL($value).'")';
		return array (
				'sql_first' => $sql,
				'sql_second' => $sql_second
		);
	}
	public function getOrdersQueryVersion4($value)
	{
		$sql = 'SELECT  LEFT(o.date_add,"'.pSQL($value).'") as fix_date, COUNT(distinct o.date_add) as data FROM
				'._DB_PREFIX_.'orders o LEFT JOIN '._DB_PREFIX_.'order_history oh ON oh.id_order = o.id_order
				LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
				WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history WHERE date_add in 
				(SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order)) AND o.date_add BETWEEN
				'.$this->getDateBetween().'GROUP BY LEFT(o.date_add,"'.pSQL($value).'")';
		return $sql;
	}
	public function getOrdersQueryOtherVersion($value)
	{
		$sql = 'SELECT  LEFT(o.date_add,"'.pSQL($value).'") as fix_date, COUNT(distinct o.date_add) as data FROM
    			'._DB_PREFIX_.'orders o  LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
				WHERE os.invoice = 1 AND o.date_add BETWEEN '.$this->getDateBetween().' GROUP BY LEFT( o.date_add,"'.pSQL($value).'")';
		return $sql;
	}
	public function getProfitRevenueQueryVersion4($value)
	{
		$sql = 'SELECT LEFT(o.date_add,"'.pSQL($value).'") as fix_date,
				ROUND(IFNULL((SUM( (d.product_price/cur.conversion_rate) * d.product_quantity )
				-SUM( d.product_quantity * (p.wholesale_price) )),0),2) AS  "profit",
				ROUND(SUM((d.product_price/cur.conversion_rate) * d.product_quantity ),2) as revenue
				FROM '._DB_PREFIX_.'orders o LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
				LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state 
				JOIN '._DB_PREFIX_.'order_detail AS d ON o.id_order = d.id_order JOIN  '._DB_PREFIX_.'product AS p ON d.product_id = p.id_product
				JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
				WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history 
				WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
				AND o.date_add between '.$this->getDateBetween().' GROUP BY LEFT(o.date_add,"'.pSQL($value).'")';
		return $sql;
	}
	public function getProfitRevenueQueryOtherVersion($value)
	{
		$sql = 'SELECT  LEFT(o.date_add,"'.pSQL($value).'") as fix_date,
				ROUND(IFNULL((SUM( (d.total_price_tax_excl/cur.conversion_rate) )
				- SUM(d.product_quantity * (d.purchase_supplier_price) )),0),2) 
				AS  "profit", SUM((d.total_price_tax_excl/cur.conversion_rate)) as revenue FROM '._DB_PREFIX_.'orders o  
				LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state 
				LEFT JOIN '._DB_PREFIX_.'order_detail AS d ON o.id_order = d.id_order 
				JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
				WHERE os.invoice = 1 AND o.date_add between '.$this->getDateBetween().' GROUP BY LEFT(o.date_add,"'.pSQL($value).'")';
		return $sql;
	}
	/**
	 * @return array Get total of registration between date range and type
	 */

	/** public function getCustomers($array_filter = array()) **/
	public function getCustomers()
	{
		switch ($this->getMethod())
		{
			case 'day':
			case 'week':
				$sql = $this->getCustomersQuery(10);
				break;
			case 'month':
				$sql = $this->getCustomersQuery(7);
				break;
			case 'year':
				$sql = $this->getCustomersQuery(4);
				break;
			default:
				break;
		}

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		$array = array();
		$array['result'] = $result;
		$get_collect = $this->getCollectedData($array);
		$x_axis = $get_collect['xAxis'];
		$values = $get_collect['values'];
		return array('xAxis' => $x_axis, 'series' => array(array('name' => 'Customers', 'data' => $values)));
	}

	public function getCustomersByGender()
	{
		$gender = array('Male', 'Female', 'Unknown');
		$array = array();
		$final_data = array();

		foreach ($gender as $val)
		{
			if ($val == 'Male')
			{
				$join = 'WHERE  c.id_gender = 1 AND ';
				$color = 'green';
			}
			elseif ($val == 'Female')
			{
				$join = 'WHERE c.id_gender IN (SELECT b.id_gender FROM '._DB_PREFIX_.'customer b WHERE b.id_gender = 3 OR b.id_gender = 2) AND ';
				$color = 'violet';
			}
			else
			{
				$join = 'WHERE c.id_gender = 0 AND';
				$color = '#2f7ed8';
			}
			switch ($this->getMethod())
			{
				case 'day':
				case 'week':
					$sql = $this->getCustomersByGenderQuery($join, 10);
					break;
				case 'month':
					$sql = $this->getCustomersByGenderQuery($join, 7);
					break;
				case 'year':
					$sql = $this->getCustomersByGenderQuery($join, 4);
					break;

				default:
					$sql = $this->getCustomersByGenderQuery($join, 7);
					break;
			}

			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
			$array['result'] = $result;
			$total = 0;

			foreach ($result as $row)
				$total = $total + $row['data'];

			$get_collect = $this->getCollectedData($array);
			$x_axis = $get_collect['xAxis'];
			$values = $get_collect['values'];
			if (is_array($values))
			{
				$func = create_function('$val', 'return ($val == 0)? null : $val;');
				$values = array_map($func, $values);
			}
			$final_data[] = array('name' => $val, 'data' =>$values, 'color' => $color, 'total' => $total);
		}

		return array('xAxis' => $x_axis, 'series' => $final_data);
	}

	public function getCustomerByLanguage()
	{
		$sql_lang = 'SELECT  name as data FROM '._DB_PREFIX_.'lang ';
		$result_lang = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_lang);
		if (count($result_lang) > 0)
		$final_data = array();

		foreach ($result_lang as $lang_name)
		{
			if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
			{
				switch ($this->getMethod())
				{

					case 'day':
					case 'week':
						$sql = $this->getLanguageQueryForVerion4($lang_name, 10);
						break;
					case 'month':
						$sql = $this->getLanguageQueryForVerion4($lang_name, 7);
						break;
					case 'year':
						$sql = $this->getLanguageQueryForVerion4($lang_name, 4);
						break;
					default:
						break;
				}
			}
			else
			{
				switch ($this->getMethod())
				{
					case 'day':
					case 'week':
						$sql = $this->getLanguageQueryForOtherVersion($lang_name, 10);
						break;

					case 'month':
						$sql = $this->getLanguageQueryForOtherVersion($lang_name, 7);
						break;

					case 'year':
						$sql = $this->getLanguageQueryForOtherVersion($lang_name, 4);
						break;

					default:
						break;
				}
			}

			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
			$array = array();

			$top_language = array();
			$array['result'] = $result;
			$total = 0;
			foreach ($result as $row)
				$total = $total + $row['data'];

			$get_collect = $this->getCollectedData($array);
			$x_axis = $get_collect['xAxis'];
			$values = $get_collect['values'];
			$values = $this->getValues($values);
			$final_data[] = array('name' => $lang_name['data'], 'data' => $values);
		}

		$array1 = array();
		if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
		{
			$toplanguagesql = 'SELECT COUNT(c.id_customer) as data, l.name as name FROM
								'._DB_PREFIX_.'customer as c join 
								'._DB_PREFIX_.'orders as o  on o.id_customer = c.id_customer join
								'._DB_PREFIX_.'lang as l on  o.id_lang=l.id_lang AND
								c.date_add >= "'.pSQL($this->date_from).'  00:00:00" AND c.date_add <= "'.pSQL($this->date_to).' 23:59:59"
								GROUP BY o.id_lang';
		}
		else
		{

			$toplanguagesql = 'SELECT  COUNT(id_customer) as data, l.name as name FROM
								'._DB_PREFIX_.'customer as c join  '._DB_PREFIX_.'lang as l on  c.id_lang=l.id_lang  AND
								'.$this->getDate().'GROUP BY c.id_lang';

		}

		$result1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($toplanguagesql);
		$top_language = array();
		$array1['result1'] = $result1;
		if (count($result1) > 0)
		{
			foreach ($result1 as $row)
				$top_language[$row['name']] = $row['data'];
		}
		return array('xAxis' => $x_axis, 'series' => $final_data, 'topLanguage' => $top_language);
	}

	public function getCustomerByRegion()
	{
		$sql_lang = 'SELECT  name as data FROM '._DB_PREFIX_.'zone ';
		$result_lang = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_lang);
		$array = array();
		$final_data = array();
		if (count($result_lang) > 0)

		foreach ($result_lang as $lang_name)
		{
			switch ($this->getMethod())
			{
				case 'day':
				case 'week':
					$sql = $this->getCustomerByRegionQuery($lang_name, 10);
				break;
				case 'month':
					$sql = $this->getCustomerByRegionQuery($lang_name, 7);
				break;
				case 'year':
					$sql = $this->getCustomerByRegionQuery($lang_name, 4);
				break;

				default:
				break;
			}

			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
			$array['result'] = $result;

			$get_collect = $this->getCollectedData($array);
			$x_axis = $get_collect['xAxis'];
			$values = $get_collect['values'];
			if (is_array($values))
			{
			$values = array_map(function ($val)
			{
				return ($val == 0)? null : $val;
			}, $values);
			}
			$final_data[] = array('name' => $lang_name['data'], 'data' => $values);
		}
		return array('xAxis' => $x_axis, 'series' => $final_data);
	}

	public function getCustomerByCountry()
	{
		switch ($this->getMethod())
		{
			case 'day':
			case 'week':
				$sql = $this->getCustomerByCountryQuery(10);
				break;
			case 'month':
				$sql = $this->getCustomerByCountryQuery(7);
				break;
			case 'year':
				$sql = $this->getCustomerByCountryQuery(4);
				break;

			default:
				$sql = $this->getCustomerByCountryQuery(7);
				break;
		}
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		$array = array();
		$top_country = array();
		$array['result'] = $result;

		$get_collect = $this->getCollectedData($array);
		$x_axis = $get_collect['xAxis'];
		$values = $get_collect['values'];
		$values = $this->getValues($values);
		$final_data = array('data' => $values);

		$array1 = array();
		$topcountrysql = 'SELECT  COUNT(DISTINCT a.id_customer) as data,z.name as name FROM
            '._DB_PREFIX_.'address as a,   '._DB_PREFIX_.'country as c,'._DB_PREFIX_.'country_lang as z where
             a.id_country=c.id_country AND c.id_country=z.id_country AND '.$this->getDate().'
             GROUP BY c.id_country';
		$result1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($topcountrysql);
		$top_country = array();
		$array1['result1'] = $result1;
		if (count($result1) > 0)
			foreach ($result1 as $row)
				$top_country[$row['name']] = $row['data'];

		return array('xAxis' => $x_axis, 'series' => $final_data, 'topCountry' => $top_country);
	}

	public function getCustomerByCurrency()
	{
		$sql_lang = 'SELECT  name as data FROM '._DB_PREFIX_.'currency ';
		$result_lang = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_lang);
		if (count($result_lang) > 0)
		$final_data = array();

		foreach ($result_lang as $lang_name)
		{
			switch ($this->getMethod())
			{
				case 'day':
				case 'week':
					$sql = $this->getCustomerByCurrencyQuery($lang_name, 10);
					break;

				case 'month':
					$sql = $this->getCustomerByCurrencyQuery($lang_name, 7);
					break;

				case 'year':
					$sql = $this->getCustomerByCurrencyQuery($lang_name, 4);
					break;

				default:
					break;
			}

			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
			$array = array();

			$array['result'] = $result;
			$total = 0;
			foreach ($result as $row)
				$total = $total + $row['order_data'];

			$get_collect = $this->getCollectedData($array);
			$x_axis = $get_collect['xAxis'];
			$values = $get_collect['values'];
			$values = $this->getValues($values);
			$final_data[] = array('name' => $lang_name['data'], 'data' => $values);
		}

			$array1 = array();
			$topcurrencysql = 'select count(o.id_order) as data,c.name as name from
								'._DB_PREFIX_.'orders as o join '._DB_PREFIX_.'currency as c on o.id_currency=c.id_currency WHERE o.date_add BETWEEN
								'.$this->getDateBetween().' group by c.name,o.id_currency';

			$result1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($topcurrencysql);
			$top_currency = array();
			$array1['result1'] = $result1;
			if (count($result1) > 0)
				foreach ($result1 as $row)
					$top_currency[$row['name']] = $row['data'];

		return array('xAxis' => $x_axis, 'series' => $final_data, 'topCurrency' => $top_currency);
	}

	public function getOrders()
	{
		// update spreadsheet with this query, we changed 001c
		switch ($this->getMethod())
		{
			case 'day':
			case 'week':
				if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
					$sql = $this->getOrdersQueryVersion4(10);
				else
					$sql = $this->getOrdersQueryOtherVersion(10);
				break;

// 			case 'week':
// 				if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
// 					$sql = $this->getOrdersQueryVersion4(10);
// 				else
// 					$sql = $this->getOrdersQueryOtherVersion(10);
// 				break;

			case 'month':
				if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
					$sql = $this->getOrdersQueryVersion4(7);
				else
					$sql = $this->getOrdersQueryOtherVersion(7);
				break;

			case 'year':
				if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
					$sql = $this->getOrdersQueryVersion4(4);
				else
					$sql = $this->getOrdersQueryOtherVersion(4);
				break;

			default:
				break;
		}

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		$i = count($result);
		$values = array();
		$category = array();
		switch ($this->getMethod())
		{
			case 'day':
				$series = $this->dateRange($this->date_from, $this->date_to, '+1 day');
				$count_day = count($result);
				for ($i = 0; $i < $count_day; $i++)
				{
					foreach ($result as $row)
					{
						if (array_key_exists($row['fix_date'], $series))
							$series[$row['fix_date']] = $row['data'];

					}
				}

				foreach ($series as $k => $row)
				{
					$y_data = $row;
					$values[] = array(strtotime($k) * 1000, $y_data);
				}
				$x_axis = array('type' => 'datetime');
				break;

			case 'month':
				$series = array();
				foreach ($result as $row)
					$series = array($row['fix_date'] => 0);
				$date_month1 = date('Y-m', strtotime($this->date_from));
				$date_month2 = date('Y-m', strtotime($this->date_to));
				$from_date = strtotime($this->date_from);
				$to_date = strtotime($this->date_to);
				$num_days = abs($from_date - $to_date) / 60 / 60 / 24;
				if ($num_days < 30)
					$series = array($date_month1 => 0,$date_month2 => 0);
				else
					$series = $this->dateRange($this->date_from, $this->date_to, '+1 month', 'Y-m');
				$count_month = count($result);
				for ($i = 0; $i < $count_month; $i++)
				{
					foreach ($result as $row)
					{
							$series[$row['fix_date']] = $row['data'];

					}
				}
				foreach ($series as $k => $row)
				{
					$y_data = $row;
					$values[] = $y_data;
					$category[] = date('M Y', strtotime($k));
				}
				$x_axis = array('categories' => $category);
				break;

			case 'year':
				$series = $this->dateRange($this->date_from, $this->date_to, '+1 year', 'Y');
				$count_year = count($result);
				for ($i = 0; $i < $count_year; $i++)
				{
					foreach ($result as $row)
					{
						if (array_key_exists($row['fix_date'], $series))
							$series[$row['fix_date']] = $row['data'];

					}
				}

				foreach ($series as $k => $row)
				{
					$y_data = $row;
					$values[] = $y_data;
					$category[] = $k;
				}
				$x_axis = array('categories' => $category);
				break;

			default:
				break;
		}

		return array('xAxis' => $x_axis, 'series' => array(array('name' => 'Orders', 'data' => $values),
			array('name' => 'Visits', 'data' => $this->getVisits())));
	}

	public function getVisits()
	{
		switch ($this->getMethod())
		{
			case 'day':
				$sql = $this->getVisitsQuery(10);
				break;

			case 'month':
				$sql = $this->getVisitsQuery(7);
				break;

			case 'year':
				$sql = $this->getVisitsQuery(4);
				break;
		}

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		$i = count($result);
		$values = array();
		$category = array();
		switch ($this->getMethod())
		{

			case 'day':
				$series = $this->dateRange($this->date_from, $this->date_to, '+1 day');
				$count_day = count($result);
				for ($i = 0; $i < $count_day; $i++)
				{
					foreach ($result as $row)
					{
						if (array_key_exists($row['fix_date'], $series))
							$series[$row['fix_date']] = $row['data'];

					}
				}
				foreach ($series as $k => $row)
				{
					$y_data = $row;
					$values[] = array(strtotime($k) * 1000, $y_data);

				}
				break;

			case 'month':
				$series = $this->dateRange($this->date_from, $this->date_to, '+1 month', 'Y-m');
				$count_month = count($result);
				for ($i = 0; $i < $count_month; $i++)
				{
					foreach ($result as $row)
					{
							$series[$row['fix_date']] = $row['data'];

					}
				}
				foreach ($series as $k => $row)
				{
					$y_data = $row;
					$category[] = date('M Y', strtotime($k));
					$values[] = $y_data;

				}
				break;

			case 'year':
				$series = $this->dateRange($this->date_from, $this->date_to, '+1 year', 'Y');
				$count_year = count($result);
				for ($i = 0; $i < $count_year; $i++)
				{
					foreach ($result as $row)
					{
						if (array_key_exists($row['fix_date'], $series))
							$series[$row['fix_date']] = $row['data'];

					}
				}
				foreach ($series as $k => $row)
				{
					$y_data = $row;
					$values[] = $y_data;

				}
				break;
		}

		return $values;
	}

	public function getProfitRevenue()
	{
		switch ($this->getMethod())
		{
			case 'day':
				if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
					$sql = $this->getProfitRevenueQueryVersion4(10);
				else
					$sql = $this->getProfitRevenueQueryOtherVersion(10);
				break;

			case 'month':
				if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
					$sql = $this->getProfitRevenueQueryVersion4(7);
				else
					$sql = $this->getProfitRevenueQueryOtherVersion(7);
				break;

			case 'year':
				if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
					$sql = $this->getProfitRevenueQueryVersion4(4);
				else
					$sql = $this->getProfitRevenueQueryOtherVersion(4);
				break;

			default:
				break;
		}

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		$i = count($result);
		$values = array();
		$values_r = array();
		$category = array();
		switch ($this->getMethod())
		{
			case 'day':
				$series = $this->dateRange($this->date_from, $this->date_to, '+1 day');
				$series_revenue = $series;
				$count_day = count($result);
				for ($i = 0; $i < $count_day; $i++)
				{
					foreach ($result as $row)
					{
						if (array_key_exists($row['fix_date'], $series))
						{
							$series[$row['fix_date']] = $row['profit'];
							$series_revenue[$row['fix_date']] = $row['revenue'];
						}
					}
				}

				foreach ($series as $k => $row)
				{
					$y_data = $row;
					$values[] = array(strtotime($k) * 1000, $y_data);
				}

				foreach ($series_revenue as $k => $row)
				{
					$y_data_r = $row;
					$values_r[] = array(strtotime($k) * 1000, $y_data_r);
				}
				$x_axis = array('type' => 'datetime');
				break;

			case 'month':
				$series = array();
				foreach ($result as $row)
					$series = array($row['fix_date'] => 0);
				$date_month1 = date('Y-m', strtotime($this->date_from));
				$date_month2 = date('Y-m', strtotime($this->date_to));
				$from_date = strtotime($this->date_from);
				$to_date = strtotime($this->date_to);
				$num_days = abs($from_date - $to_date) / 60 / 60 / 24;
				if ($num_days < 30)
					$series = array($date_month1 => 0,$date_month2 => 0);
				else
					$series = $this->dateRange($this->date_from, $this->date_to, '+1 month', 'Y-m');
				$count_month = count($result);
				for ($i = 0; $i < $count_month; $i++)
				{
					foreach ($result as $row)
					{
						//if (array_key_exists($row['fix_date'], $series))
						//{
							$series[$row['fix_date']] = $row['profit'];
							$series_revenue[$row['fix_date']] = $row['revenue'];
						//}
					}
				}
				foreach ($series as $k => $row)
				{
					$y_data = $row;
					$values[] = $y_data;

				}
				foreach ($series_revenue as $k => $row)
				{
					$y_data_r = $row;
					$values_r[] = $y_data_r;

					$category[] = date('M Y', strtotime($k));
				}
				$x_axis = array('categories' => $category);
				break;

			case 'year':
				$series = $this->dateRange($this->date_from, $this->date_to, '+1 year', 'Y');
				$series_revenue = $series;
				$count_year = count($result);
				for ($i = 0; $i < $count_year; $i++)
				{
					foreach ($result as $row)
					{
						if (array_key_exists($row['fix_date'], $series))
						{
							$series[$row['fix_date']] = $row['profit'];
							$series_revenue[$row['fix_date']] = $row['revenue'];
						}
					}
				}

				foreach ($series as $k => $row)
				{
					$y_data = $row;
					$values[] = $y_data;
				}

				foreach ($series_revenue as $k => $row)
				{
					$y_data_r = $row;
					$values_r[] = $y_data_r;
					$category[] = $k;
				}
				$x_axis = array('categories' => $category);
				break;

			default:
				break;
		}

		$return_data = array('xAxis' => $x_axis, 'series' => array(array('name' => 'Profit', 'data' => $values),
			array('name' => 'Revenue', 'data' => $values_r)));

		return $return_data;
	}

	public function getTotalVisits()
	{
		//visits
		$sql = 'SELECT count(id_connections) as total from '._DB_PREFIX_.'connections where '.$this->getDate().'';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
		$visits = isset($result['total']) ? $result['total'] : 0;

		//visits Percentage
		$sql_second = 'SELECT count(id_connections) as total from '._DB_PREFIX_.'connections where '.$this->getDateForPercent().'';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql_second);
		$visits_percentage = isset($result['total']) ? $result['total'] : 1;
		$total = $this->getTotal($visits, $visits_percentage);
		return array('total' => $visits, 'sign' => $total['sign'], 'percent' => $total['final']);
	}
	public function getTotalGuests()
	{
		//Guests
		$sql = 'SELECT count(id_customer) as total from '._DB_PREFIX_.'customer where is_guest =  1 AND '.$this->getDate().'';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
		$guests = isset($result['total']) ? $result['total'] : 0;
		//Guests
		$sql_second = 'SELECT count(id_customer) as total from '._DB_PREFIX_.'customer where is_guest =  1 AND '.$this->getDateForPercent().'';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql_second);
		$guests_percentage = isset($result['total']) ? $result['total'] : 1;
		$total = $this->getTotal($guests, $guests_percentage);
		return array('total' => $guests, 'sign' => $total['sign'], 'percent' => $total['final']);
	}

	public function getTotalRegistrations()
	{
		//Registrations
		$sql = 'SELECT count(id_customer) as total from '._DB_PREFIX_.'customer where '.$this->getDate().'';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
		$registration = isset($result['total']) ? $result['total'] : 0;
		//Registrations Percent
		$sql_second = 'SELECT count(id_customer) as total from '._DB_PREFIX_.'customer where '.$this->getDateForPercent().'';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql_second);
		$registration_percentage = isset($result['total']) ? $result['total'] : 1;
		$total = $this->getTotal($registration, $registration_percentage);
		return array('total' => $registration, 'sign' => $total['sign'], 'percent' => $total['final']);
	}


	public function getVisitorsAndRegistration()
	{
		switch ($this->getMethod())
		{
			case 'day':
			case 'week':
				$sql = $this->getVisitorsAndRegistrationQuery(10);
			break;
			case 'month':
				$sql = $this->getVisitorsAndRegistrationQuery(7);
			break;
			case 'year':
				$sql = $this->getVisitorsAndRegistrationQuery(4);
			break;
			default:
			break;
		}

		//Visitors
		$visitor_result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql['sql_first']);
		$visitor_array = array();
		$visitor_array['result'] = $visitor_result;
		$get_collect_visitor 	= $this->getCollectedData($visitor_array);
		$x_axis 				= $get_collect_visitor['xAxis'];
		$visitor_values 		= $get_collect_visitor['values'];
		//Registered user
		$registered_result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql['sql_second']);
		$registered_array = array();
		$registered_array['result'] = $registered_result;
		$get_collect_registered 	= $this->getCollectedData($registered_array);
		$registered_values 			= $get_collect_registered['values'];

		$visitor_normalized_values		= $this->getValues($visitor_values);
		$registered_normalized_values	= $this->getValues($registered_values);
		$return_data = array('xAxis' => $x_axis,'series' => array(array('name' => 'Non Converted','data' => $visitor_normalized_values,'color' => 'red'),
		array('name' => 'Converted','data' => $registered_normalized_values,'color' => 'green')));
		return $return_data;
	}
	public function topStatisticsCustomer()
	{
		$customer_total = $this->getTotalRegistrations();
		$final_array = array();
		$final_array['CustomerOverview'][] = array('name' => 'Total number of Customers Created : '.$customer_total['total']);
		$gender_information = $this->getCustomersByGender();

		$final_array['CustomerOverview'][] = array('name' => 'Genders : Male['.$gender_information['series'][0]['total']
			.'] / Female['.$gender_information['series'][1]['total'].'] / Unknown['.$gender_information['series'][2]['total'].']');
		$customer_lang = $this->getCustomerByLanguage();
		$return_lang = count($customer_lang['topLanguage']) >= 1 ? array_search(max($customer_lang['topLanguage']),
			$customer_lang['topLanguage']) : 'No Data';
		$final_array['CustomerOverview'][] = array('name' => 'Top Language : '.$return_lang);

		$customer_country = $this->getCustomerByCountry();
		$return_country = count($customer_country['topCountry']) > 1 ? array_search(max($customer_country['topCountry']),
			$customer_country['topCountry']) : 'No Data';
		$final_array['CustomerOverview'][] = array('name' => 'Top Country : '.$return_country);

		$customer_currency = $this->getCustomerByCurrency();
		$return_currency = count($customer_currency['topCurrency']) >= 1 ? array_search(max($customer_currency['topCurrency']),
			$customer_currency['topCurrency']) : 'No Data';
		$final_array['CustomerOverview'][] = array('name' => 'Top Currency : '.$return_currency);

		$get_best_customers = $this->getBestCustomers();
		if (is_array($get_best_customers))
		{
			foreach ($get_best_customers as $customers)
			{
				$customers['full_name'] = $customers['firstname'].' '.$customers['lastname'];
				$customers['total_orders'] = $customers['totalValidOrders'].'Orders';
				$customers['data_value'] = $customers['currency_symbol'].$customers['totalMoneySpent'];
				$final_array['TopCustomers'][] = array (
						'customerid' => $customers['id_customer'],
						'name' => $customers['full_name'],
						'totalorders' => $customers['total_orders'],
						'data_val' => $customers['data_value']
				);
			}
		}
		return $final_array;
	}

	public function topStatisticsOrder()
	{
		return $this->getOrdersTop(array('tag' => 'between'));
	}

	public function getOrdersTop($array = array())
	{
		if (count($array) > 0)
			$tag = isset($array['tag']) ? $array['tag'] : null;
		if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
		{

			$sql = 'SELECT COUNT(distinct o.date_add) as total FROM '._DB_PREFIX_.'orders o
					LEFT JOIN '._DB_PREFIX_.'order_history oh ON oh.id_order = o.id_order LEFT JOIN '._DB_PREFIX_.'order_state os 
					ON os.id_order_state = oh.id_order_state WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history 
					WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
					AND o.date_add BETWEEN '.$this->getDateBetween().'';
		}
		else
		{
			$sql = 'SELECT COUNT(distinct o.date_add) as total FROM '._DB_PREFIX_.'orders o LEFT JOIN
				   '._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
					WHERE os.invoice = 1 AND o.date_add BETWEEN '.$this->getDateBetween().'';
		}

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
		$orders_top = isset($result['total']) ? $result['total'] : 0;
		if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
		{
			$sql_second = 'SELECT COUNT(distinct o.date_add) as total FROM '._DB_PREFIX_.'orders o
							LEFT JOIN '._DB_PREFIX_.'order_history oh ON oh.id_order = o.id_order 
							LEFT JOIN  '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
							WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
							WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
							AND o.date_add BETWEEN '.$this->getDateForPercent($tag).' ';
		}
		else
		{
			$sql_second = 'SELECT COUNT(distinct o.date_add) as total FROM '._DB_PREFIX_.'orders o LEFT JOIN
				   			'._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state 
							WHERE os.invoice = 1 AND o.date_add BETWEEN '.$this->getDateForPercent($tag).' ';
		}
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql_second);
		$orders_top_percentage = isset($result['total']) ? $result['total'] : 1;
		$total = $this->getTotal($orders_top, $orders_top_percentage);
		return array('total' => $orders_top, 'sign' => $total['sign'], 'percent' => $total['final']);
	}

	public function getProfitTop()
	{
		$tag = 'between';
		if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
		{
			$sql = 'SELECT ROUND(IFNULL((SUM((od.product_price/cur.conversion_rate) * od.product_quantity)
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
					AND o.date_add BETWEEN '.$this->getDateBetween().'';
		}
		else
		{
			$sql = 'SELECT  ROUND(IFNULL((SUM( (d.total_price_tax_excl/cur.conversion_rate) )
					- SUM( d.product_quantity * (d.purchase_supplier_price) )),0),2) AS  total 
					FROM '._DB_PREFIX_.'orders o  LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
					LEFT JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency		
					LEFT JOIN '._DB_PREFIX_.'order_detail AS d ON o.id_order = d.id_order
					WHERE os.invoice = 1 AND o.date_add between '.$this->getDateBetween().'';
		}
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
		$profit_top = isset($result['total'])? $result['total'] : 0;
// 		foreach ($result as $k => $category)
// 		{
			$currency_symbol_sql = 'SELECT cur.sign FROM '._DB_PREFIX_.'currency cur JOIN '._DB_PREFIX_.'configuration conf ON
									cur.id_currency = conf.value where conf.name = "PS_CURRENCY_DEFAULT"';
			$currency_symbol = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($currency_symbol_sql);
			$currency_symbol = $currency_symbol[0]['sign'];
// 		}
		//Registrations Percent
		if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
{
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
							AND o.date_add BETWEEN '.$this->getDateForPercent($tag).' ';
		}
		else
		{
			$sql_second = 'SELECT  ROUND(IFNULL((SUM( (d.total_price_tax_excl/cur.conversion_rate) )
							- SUM( d.product_quantity * (d.purchase_supplier_price) )),0),2) AS  total, 
    						cur.sign as currency_symbol
							FROM '._DB_PREFIX_.'orders o  LEFT JOIN
							'._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
							LEFT JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency		
							LEFT JOIN '._DB_PREFIX_.'order_detail AS d ON o.id_order = d.id_order
							WHERE os.invoice = 1 AND o.date_add between  '.$this->getDateForPercent($tag).' ';
		}
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql_second);
		$profit_top_percentage = isset($result['total']) ? $result['total'] : 1;
		$total = $this->getTotal($profit_top, $profit_top_percentage);
		return array('total' =>  $currency_symbol.$profit_top, 'sign' => $total['sign'], 'percent' => $total['final']);
	}
	public function getRevenueTop()
	{
		$tag = 'between';
		//Revenue
		if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
		{
			$sql = 'SELECT SUM((od.product_price/cur.conversion_rate) * od.product_quantity) as total
					FROM '._DB_PREFIX_.'orders AS o LEFT JOIN '._DB_PREFIX_.'order_detail AS od ON o.id_order = od.id_order 
					LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
					LEFT JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
					WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
					WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
					AND o.date_add BETWEEN '.$this->getDateBetween().' ';
		}
		else
		{
			$sql = 'SELECT  SUM((d.total_price_tax_excl/cur.conversion_rate)) as total
					FROM '._DB_PREFIX_.'orders o  LEFT JOIN
					'._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
					LEFT JOIN '._DB_PREFIX_.'currency  AS cur ON cur.id_currency = o.id_currency
					LEFT JOIN '._DB_PREFIX_.'order_detail AS d ON o.id_order = d.id_order
					WHERE os.invoice = 1 AND o.date_add	between '.$this->getDateBetween().'';
		}
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
		$revenue_top = isset($result['total']) ? $result['total'] : 0;
// 		foreach ($result as $k => $category)
// 		{
			$currency_symbol_sql = 'SELECT cur.sign FROM '._DB_PREFIX_.'currency cur JOIN '._DB_PREFIX_.'configuration conf ON
									cur.id_currency = conf.value where conf.name = "PS_CURRENCY_DEFAULT"';
			$currency_symbol = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($currency_symbol_sql);
			$currency_symbol = $currency_symbol[0]['sign'];
// 		}
		//Revenue Percent
		if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
{
			$sql_second = 'SELECT SUM((od.product_price/cur.conversion_rate) * od.product_quantity) as total, cur.sign as currency_symbol
							FROM '._DB_PREFIX_.'orders AS o LEFT JOIN '._DB_PREFIX_.'order_detail AS od ON o.id_order = od.id_order
							LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
							LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
							LEFT JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
							WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
							WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
							AND o.date_add BETWEEN '.$this->getDateForPercent($tag).' ';
		}
		else
		{
			$sql_second = 'SELECT  SUM((d.total_price_tax_excl/cur.conversion_rate)) as total, cur.sign as currency_symbol 
							FROM '._DB_PREFIX_.'orders o  LEFT JOIN
							'._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
							LEFT JOIN '._DB_PREFIX_.'currency  AS cur ON cur.id_currency = o.id_currency
							LEFT JOIN '._DB_PREFIX_.'order_detail AS d ON o.id_order = d.id_order
							WHERE os.invoice = 1 AND o.date_add	BETWEEN '.$this->getDateForPercent($tag).' ';
		}
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql_second);
		$revenue_top_percentage = isset($result['total']) ? $result['total'] : 1;
		$total = $this->getTotal($revenue_top, $revenue_top_percentage);
		return array('total' =>  $currency_symbol.round($revenue_top, 2), 'sign' => $total['sign'],
				'percent' => $total['final'],'revenuetop' => $revenue_top);
	}
	public function getProductTop()
	{
		if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
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
            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id'.$this->getFilterP().'
				JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
				LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
				LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
            	JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
            	WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
				WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
            	AND o.date_add BETWEEN '.$this->getDateBetween().''.$this->getSortP();
		}
		else
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS SUM(pro.product_quantity) AS total,
	            	pro.id_order AS  "pro#order_id",
	            	pro.product_id AS  "pro#product_id",
	            	p.reference AS  "p#reference",
	            	pro.product_name AS "pro#product_name",
	            	pro.unit_price_tax_excl AS  "pro#unit_price_tax_excl",
					cur.sign AS "currency_symbol",
					ROUND(IFNULL(SUM( pro.total_price_tax_excl ),0),2) AS  "pro#total_price_tax_excl",
					ROUND(IFNULL((SUM( pro.total_price_tax_excl ) - (pro.product_quantity * pro.purchase_supplier_price )),0),2) AS "profit"
	            	FROM '._DB_PREFIX_.'order_detail AS pro
	            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id'.$this->getFilterP().'
					JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
	            	JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
	            	WHERE os.invoice = 1 AND o.date_add BETWEEN '.$this->getDateBetween().''.$this->getSortP();
		}
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		foreach ($result as $k)
		$count = $k['total'];
		$product_top = isset($count) ? $count : 0;
		return array('total' => $product_top);
	}
	public function getVisitTop()
	{
		//visits
		$sql = 'SELECT count(id_connections) as total from '._DB_PREFIX_.'connections where '.$this->getDate().'';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
		$visit_top = isset($result['total']) ? $result['total'] : 0;
		//visits Percentage
		$sql_second = 'SELECT count(id_connections) as total from '._DB_PREFIX_.'connections where '.$this->getDateForPercent().'';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql_second);
		$visit_top_percentage = isset($result['total']) ? $result['total'] : 1;
		$total = $this->getTotal($visit_top, $visit_top_percentage);
		return array('total' => $visit_top, 'sign' => $total['sign'], 'percent' => $total['final']);
	}
	public function getBestCustomers()
	{
		if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
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
				   	AND o.date_add   BETWEEN '.$this->getDateBetween().'
					GROUP BY c.id_customer ORDER BY totalMoneySpent DESC';
		}
		else
		{
			$sql = 'SELECT  count(distinct o.date_add) as totalValidOrders, c.id_customer,
    				c.firstname,c.lastname,c.email, 
					ROUND(SUM((d.total_price_tax_excl/cur.conversion_rate)),2) as totalMoneySpent
					FROM '._DB_PREFIX_.'orders o LEFT JOIN '._DB_PREFIX_.'customer c ON c.id_customer = o.id_customer
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
					JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
					LEFT JOIN '._DB_PREFIX_.'order_detail AS d ON o.id_order = d.id_order
					WHERE os.invoice = 1  AND o.date_add   BETWEEN '.$this->getDateBetween().'
					GROUP BY c.id_customer ORDER BY totalMoneySpent DESC';
		}
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		$count = count($result);
			for ($k = 0; $k < $count; $k++)
			{
			$currency_symbol_sql = 'SELECT cur.sign FROM '._DB_PREFIX_.'currency cur JOIN '._DB_PREFIX_.'configuration conf ON
									cur.id_currency = conf.value where conf.name = "PS_CURRENCY_DEFAULT"';
			$currency_symbol = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($currency_symbol_sql);
			$result[$k]['currency_symbol'] = $currency_symbol[0]['sign'];
			}
		return $result;
	}
	public function getBestCategories4($tag = '')
	{
		$date_between = $this->getDateBetween();
		if ($tag != '')
		$date_between = $this->getDateForPercent($tag);
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
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_best_categories);
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
		$bestcategoriescount = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_best_categories_count);
		if (count($bestcategoriescount) > 5)
		{
			$category_id = array();
			foreach ($result as $row)
				$category_id[] = $row['id_category'];
			$cat_id = implode(',', $category_id);
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
									AND o.date_add BETWEEN '.$this->getDateBetween().'
					                AND cl.id_lang = 1 and p.id_category_default NOT IN ('.$cat_id.')';
			$other_categories_result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($other_categories_sql);
			$result = array_merge($result, $other_categories_result);
		}
		$total_count = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');
		return array('result' => $result, 'totalCount' => $total_count);
	}
	public function getBestCategories($tag = '')
	{
		$date_between = $this->getDateBetween();
		if ($tag != '')
			$date_between = $this->getDateForPercent($tag);
		$sql_best_categories = 'SELECT  SQL_CALC_FOUND_ROWS p.id_category_default as id_category,
								cl.name as name,sum(pro.product_quantity) AS totalQuantitySold,
								IFNULL(SUM((pro.total_price_tax_excl/cur.conversion_rate)), 0) AS revenue,
								ROUND(IFNULL(SUM( (pro.total_price_tax_excl/cur.conversion_rate) ),0),2) AS  totalPriceSold,
								ROUND(IFNULL((SUM( (pro.total_price_tax_excl/cur.conversion_rate) ) - (pro.product_quantity
								* (pro.purchase_supplier_price) )),0),2) AS profit
				            	FROM '._DB_PREFIX_.'order_detail AS pro
				            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id
								JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
								LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
								JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
				                JOIN '._DB_PREFIX_.'category_lang as cl ON cl.id_category = p.id_category_default
				                WHERE os.invoice = 1 AND cl.id_lang = 1 AND o.date_add BETWEEN '.$date_between.'
								GROUP BY p.id_category_default ORDER BY totalPriceSold DESC LIMIT 5';
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_best_categories);
		$sql_best_categories_count = 'SELECT  SQL_CALC_FOUND_ROWS p.id_category_default as id_category,
								cl.name as name,sum(pro.product_quantity) AS totalQuantitySold, 
								IFNULL(SUM((pro.total_price_tax_excl/cur.conversion_rate)), 0) AS revenue,	
								ROUND(IFNULL(SUM( (pro.total_price_tax_excl/cur.conversion_rate) ),0),2) AS  totalPriceSold,
								ROUND(IFNULL((SUM( (pro.total_price_tax_excl/cur.conversion_rate) ) - (pro.product_quantity
								* (pro.purchase_supplier_price) )),0),2) AS profit
				            	FROM '._DB_PREFIX_.'order_detail AS pro
				            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id
								JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
								LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
								JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
				                JOIN '._DB_PREFIX_.'category_lang as cl ON cl.id_category = p.id_category_default
				                WHERE os.invoice = 1 AND cl.id_lang = 1 AND o.date_add BETWEEN '.$date_between.'
								GROUP BY p.id_category_default ORDER BY totalPriceSold DESC';
		$bestcategoriescount = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_best_categories_count);
		if (count($bestcategoriescount) > 5)
		{
			$category_id = array();
			foreach ($result as $row)
				$category_id[] = $row['id_category'];
			$cat_id = implode(',', $category_id);
			$other_categories_sql = 'SELECT sum(pro.product_quantity) AS totalQuantitySold,
									IFNULL(SUM((pro.total_price_tax_excl/cur.conversion_rate)), 0) AS revenue,
									ROUND(IFNULL(SUM( (pro.total_price_tax_excl/cur.conversion_rate) ),0),2) AS  totalPriceSold,
									ROUND(IFNULL((SUM( (pro.total_price_tax_excl/cur.conversion_rate) ) - (pro.product_quantity
									* (pro.purchase_supplier_price) )),0),2) AS profit
					            	FROM '._DB_PREFIX_.'order_detail AS pro
					            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id
									JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
									LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
									JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
					                JOIN '._DB_PREFIX_.'category_lang as cl ON cl.id_category = p.id_category_default
					                WHERE os.invoice = 1 AND o.date_add BETWEEN '.$this->getDateBetween().'
					                AND cl.id_lang = 1 and p.id_category_default NOT IN ('.$cat_id.')';
			$other_categories_result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($other_categories_sql);
			$result = array_merge($result, $other_categories_result);
		}
		$total_count = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');
		return array('result' => $result, 'totalCount' => $total_count);
	}
	public function getData()
	{
		return true;
	}
	public function getBestCategoriesPie()
	{
		$tag = 'between';
		if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
		{
			$get_best_categories_result = $this->getBestCategories4();
			$get_best_categories_result_second = $this->getBestCategories4($tag);
		}
		else
		{
			$get_best_categories_result = $this->getBestCategories();
			$get_best_categories_result_second = $this->getBestCategories($tag);
		}

		//$i = 0;
		$sum = 0;
		$sum_revenue = 0;
		$sum_profit = 0;
		$return_data = array();
		foreach ($get_best_categories_result['result'] as $row)
		{
			$sum = $sum + $row['totalQuantitySold'];
			$sum_profit = $sum_profit + $row['profit'];
			$sum_revenue = $sum_revenue + $row['revenue'];
		}
		foreach ($get_best_categories_result['result'] as $row)
		{
			$percent = ($sum > 0) ? round($row['totalQuantitySold'] / $sum * 100, 2) : 0;
			$profit_percent = ($sum_profit > 0) ? round($row['profit'] / $sum_profit * 100, 2) : 0;
			$revenue_percent = ($sum_revenue > 0) ? round($row['revenue'] / $sum_revenue * 100, 2) : 0;
			$return_data['data']['product'][] = array(isset($row['name']) ? $row['name'] : 'Others', $percent);
			$return_data['data']['profit'][] = array(isset($row['name']) ? $row['name'] : 'Others', $profit_percent);
			$return_data['data']['revenue'][] = array(isset($row['name']) ? $row['name'] : 'Others', $revenue_percent);
		}
		// Second SQL
		//$j = 0;
		$sum_second = 0;
		$sum_revenue_second = 0;
		$sum_profit_second = 0;
		foreach ($get_best_categories_result_second['result'] as $row)
		{
			$sum_second = $sum_second + $row['totalQuantitySold'];
			$sum_profit_second = $sum_profit_second + $row['profit'];
			$sum_revenue_second = $sum_revenue_second + $row['revenue'];
		}
		$sum_second = ($sum_second > 0) ? $sum_second : 1;
		$sum_profit_second = ($sum_profit_second > 0) ? $sum_profit_second : 1;
		$sum_revenue_second = ($sum_revenue_second > 0) ? $sum_revenue_second : 1;
		$product_percentage_data = $this->getNegativePositiveSign($sum, $sum_second);
		$profit_percentage_data = $this->getNegativePositiveSign($sum_profit, $sum_profit_second);
		$revenue_percentage_data = $this->getNegativePositiveSign($sum_revenue, $sum_revenue_second);
		$return_data['data']['TOTAL_PRODUCT_SOLD'] = $product_percentage_data;
		$return_data['data']['TOTAL_TOP_PROFIT'] = $profit_percentage_data;
		$return_data['data']['TOTAL_TOP_REVENUE'] = $revenue_percentage_data;
		return $return_data;
	}
	public function getNegativePositiveSign($sum, $sum_second)
	{
		$total = $this->getTotal($sum, $sum_second);
		return array('total' => $sum, 'percentage' => $total['final'], 'sign' => $total['sign']);
	}
	public function getCustomersRegistered()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT id_customer, firstname, lastname, email,DATE(date_add) as "date_add" 
			FROM '._DB_PREFIX_.'customer WHERE '.$this->getDate().' '.$this->getFilterP().$this->getSortP().$this->getLimitP());
	}
	public function getCustomersRegisteredTotal()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT id_customer
			FROM '._DB_PREFIX_.'customer WHERE '.$this->getDate());
	}
	public function getCustomersWithOrdersTotal()
	{
		if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
		{
			$sql = 'SELECT  * FROM '._DB_PREFIX_.'orders o  LEFT JOIN '._DB_PREFIX_.'customer c ON c.id_customer = o.id_customer
					LEFT JOIN '._DB_PREFIX_.'order_history oh ON oh.id_order = o.id_order
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
					WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
					WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
					AND o.date_add between '.$this->getDateBetween().' '.$this->getFilterP().'
					GROUP BY date(o.date_add),c.id_customer';
		}
		else
		{
			$sql = 'SELECT  * FROM '._DB_PREFIX_.'orders o  LEFT JOIN '._DB_PREFIX_.'customer c ON c.id_customer = o.id_customer LEFT JOIN
				'._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state WHERE os.invoice = 1 AND o.date_add between '.$this->getDateBetween().'
				'.$this->getFilterP().' GROUP BY date(o.date_add),c.id_customer';
		}
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
	}
	public function getCustomersSales()
	{
		if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
		{
			$sql_profit = 'SELECT  count(distinct o.date_add) as total_orders, c.id_customer AS "c#id_customer",
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
					AND o.date_add between '.$this->getDateBetween().' '.$this->getFilterP().'
					GROUP BY date(o.date_add),c.id_customer'.$this->getSortP().$this->getLimitP();
		}
		else
		{
			$sql_profit = 'SELECT  count(distinct o.date_add) as total_orders, c.id_customer AS "c#id_customer",
    						CONCAT( c.firstname,  " ", c.lastname ) AS  "name",
							c.firstname as "c#firstname",c.lastname as "c#lastname",
							c.email as "c#email",
							IF(s.name!="",s.name,"unknown") as "s#name",
							ROUND(IFNULL((SUM( (d.total_price_tax_excl/cur.conversion_rate) )
							- SUM( d.product_quantity * (d.purchase_supplier_price) )),0),2) AS  "profit", 
							ROUND(SUM((d.total_price_tax_excl/cur.conversion_rate)),2) as revenue, 
							date(o.date_add) as "o#invoice_date", SUM( o.total_paid_real ) AS  "total paid real",
							SUM( o.total_paid ) AS  "total_paid",
							cou.id_country as "country_id"
							FROM '._DB_PREFIX_.'orders o  LEFT JOIN
							'._DB_PREFIX_.'customer c ON (c.id_customer = o.id_customer) LEFT JOIN
							'._DB_PREFIX_.'order_state os ON (os.id_order_state = o.current_state)
							LEFT JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
							JOIN '._DB_PREFIX_.'address AS a ON (c.id_customer=a.id_customer AND a.id_address=o.id_address_invoice) 
							JOIN '._DB_PREFIX_.'country as cou ON a.id_country=cou.id_country
							LEFT JOIN '._DB_PREFIX_.'state as s ON a.id_state = s.id_state
							LEFT JOIN '._DB_PREFIX_.'order_detail AS d ON o.id_order = d.id_order
							WHERE os.invoice = 1 AND o.date_add between '.$this->getDateBetween().' '.$this->getFilterP().'
							GROUP BY date(o.date_add),c.id_customer'.$this->getSortP().$this->getLimitP();
		}
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_profit);
		foreach ($result as $k => $customer)
		{
				$sql_country = 'SELECT name FROM '._DB_PREFIX_.'country_lang WHERE
								id_country = '.$customer['country_id'].'';
				$country_name = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_country);
				$result[$k]['l#name'] = $country_name[0]['name'];
				$currency_symbol_sql = 'SELECT cur.sign FROM '._DB_PREFIX_.'currency cur JOIN '._DB_PREFIX_.'configuration conf ON
										cur.id_currency = conf.value where conf.name = "PS_CURRENCY_DEFAULT"';
				$currency_symbol = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($currency_symbol_sql);
				$result[$k]['currency_symbol'] = $currency_symbol[0]['sign'];
				$id_customer = $customer['c#id_customer'];
				$sql_information = 'SELECT c.id_customer as "id_customer",
							CONCAT(c.firstname," ",c.lastname) as "name",
							IF(c.birthday!="0000-00-00",c.birthday,"unknown") as "birthday", 
							IF(s.name!="",s.name,"unknown") as "state" 
							FROM '._DB_PREFIX_.'customer AS c
							LEFT OUTER JOIN '._DB_PREFIX_.'orders AS o ON c.id_customer = o.id_customer
							JOIN '._DB_PREFIX_.'address as a ON c.id_customer=a.id_customer
							JOIN '._DB_PREFIX_.'country as cou ON a.id_country=cou.id_country
							JOIN '._DB_PREFIX_.'country_lang as co ON cou.id_country = co.id_country
							LEFT OUTER JOIN '._DB_PREFIX_.'state as s ON a.id_state = s.id_state
							WHERE c.id_customer ="'.pSQL($id_customer).'"  
							GROUP BY c.id_customer';
			$total_order = 'SELECT count(o.id_customer) as "total_order"
                         FROM '._DB_PREFIX_.'customer as c
                         JOIN '._DB_PREFIX_.'orders as o ON c.id_customer = o.id_customer
                         WHERE c.id_customer = '.(int)$id_customer;
			$total_order = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($total_order);
			$total_visits = 'SELECT count(DISTINCT con.id_connections)
                                as "total_connections"
                         FROM '._DB_PREFIX_.'customer as c
                         JOIN '._DB_PREFIX_.'connections as con ON c.id_customer = con.id_guest
                         WHERE c.id_customer = '.(int)$id_customer;
			$total_visits = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($total_visits);
			$customer_information = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql_information);
			$age = $this->years(date('Y-m-d'), $customer_information[0]['birthday']);
			$result[$k]['age'] = ($customer_information[0]['birthday'] == 'unknown' ? 'unknown' : $age);
			$result[$k]['c#birthday'] = $customer_information[0]['birthday'];
			$result[$k]['conversion'] = $this->ordersConversion($total_visits[0]['total_connections'],
				$total_order[0]['total_order']);
			$result[$k]['total_connections'] = $total_visits[0]['total_connections'];
			$result[$k]['ordinal'] = $k + 1;
		}
		return $result;
	}
	public function getAbandonedCustomerCartsTotal()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT count(ca.id_cart) as "total"
			FROM '._DB_PREFIX_.'cart as ca WHERE ca.id_customer <> 0 GROUP BY ca.id_customer');
	}
	public function getAbandonedCustomerCarts()
	{
		$sql = 'SELECT SQL_CALC_FOUND_ROWS (SELECT cur.sign FROM '._DB_PREFIX_.'currency cur JOIN '._DB_PREFIX_.'configuration conf ON
					cur.id_currency = conf.value where conf.name = "PS_CURRENCY_DEFAULT") as "default_currency",
					ca.id_cart  as "ca#id_cart",ca.id_guest as "ca#id_guest", ca.id_customer as "ca#id_customer",
					ct.firstname as "ct#firstname",ct.lastname as "ct#lastname",
	            	ord.id_customer  as "ord#id_customer",date(ca.date_add) as "ca#date_add",
					IFNULL((cp.price* pr.quantity),0) as "cp#price",
	            	IFNULL(((cp.price/cur.conversion_rate)* pr.quantity),0) as conversion_value,
	            	cur.sign as "currency_symbol" FROM  '._DB_PREFIX_.'cart as ca
                	LEFT JOIN  '._DB_PREFIX_.'cart_product as pr ON ca.id_cart = pr.id_cart
                	LEFT JOIN  '._DB_PREFIX_.'product as cp ON cp.id_product = pr.id_product
	            	LEFT JOIN  '._DB_PREFIX_.'customer as ct ON ct.id_customer = ca.id_customer
	            	LEFT JOIN  '._DB_PREFIX_.'currency AS cur ON cur.id_currency = ca.id_currency
					LEFT JOIN  '._DB_PREFIX_.'orders AS ord ON ord.id_cart = ca.id_cart
					WHERE ord.id_customer IS NULL AND ct.firstname IS NOT NULL AND ct.lastname IS NOT NULL
					AND ca.date_add BETWEEN '.$this->getDateBetween().' '.$this->getFilterP().' '.$this->getSortP().' '.$this->getLimitP();
					$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
					$total = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');
				$totalcarts = 'SELECT (SELECT cur.sign FROM '._DB_PREFIX_.'currency cur JOIN '._DB_PREFIX_.'configuration conf ON
							   cur.id_currency = conf.value where conf.name = "PS_CURRENCY_DEFAULT")  "sign",
							   IFNULL(ROUND(SUM(((cp.price/cur.conversion_rate)* pr.quantity)),2),0) as "totalcarts"
			         		   FROM  '._DB_PREFIX_.'cart as ca
                			   LEFT JOIN  '._DB_PREFIX_.'cart_product as pr ON ca.id_cart = pr.id_cart
                			   LEFT JOIN  '._DB_PREFIX_.'product as cp ON cp.id_product = pr.id_product
	            			   LEFT JOIN  '._DB_PREFIX_.'customer as ct ON ct.id_customer = ca.id_customer
	            			   LEFT JOIN  '._DB_PREFIX_.'currency AS cur ON cur.id_currency = ca.id_currency
							   LEFT JOIN  '._DB_PREFIX_.'orders AS ord ON ord.id_cart = ca.id_cart
							   WHERE ord.id_customer IS NULL AND ct.firstname IS NOT NULL AND ct.lastname IS NOT NULL
							   AND ca.date_add BETWEEN '.$this->getDateBetween();
		$result1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($totalcarts);
		foreach ($result1 as $carts)
			$totalcartvalue = $carts['sign'].$carts['totalcarts'];
		return $result = array('result' => $result, 'total' => $total,'totalcartvalue' => $totalcartvalue);
	}
	public function getProductsSalesTotal()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT COUNT( DISTINCT pro.product_id ) as "total"
			FROM '._DB_PREFIX_.'order_detail AS pro
			JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id');
	}
	public function getProductsSales()
	{
		if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
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
	            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id'.$this->getFilterP().'
					JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
					LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
	            	JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
	            	WHERE os.invoice = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history
					WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order)) AND o.date_add BETWEEN '.$this->getDateBetween().'
					GROUP BY pro.product_id'.$this->getSortP().' '.$this->getLimitP();
		}
		else
		{
			$sql = 'SELECT SQL_CALC_FOUND_ROWS SUM(pro.product_quantity) AS  "pro#quantity_sold",
	            	pro.id_order AS  "pro#order_id",
	            	pro.product_id AS  "pro#product_id",
	            	p.reference AS  "p#reference",
	            	pro.product_name AS "pro#product_name",
	            	pro.unit_price_tax_excl AS  "pro#unit_price_tax_excl",
					ROUND(IFNULL(SUM( (pro.total_price_tax_excl/cur.conversion_rate) ),0),2) AS  "pro#total_price_tax_excl",
					ROUND(IFNULL((SUM( (pro.total_price_tax_excl/cur.conversion_rate) )
					- SUM(pro.product_quantity * (pro.purchase_supplier_price) )),0),2) AS "profit"
	            	FROM '._DB_PREFIX_.'order_detail AS pro
	            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id'.$this->getFilterP().'
					JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
	            	JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
	            	WHERE os.invoice = 1 AND o.date_add BETWEEN '.$this->getDateBetween().'
					GROUP BY pro.product_id'.$this->getSortP().' '.$this->getLimitP();
		}
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		$total 	= Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');
		$days = $this->getDateDays($this->date_from, $this->date_to);
		$nodays = $days + 1;
		foreach ($result as $k => $product)
		{
			// avg of quantity sold in the date range number of days
			$result[$k]['pro#quantity_sold_daily'] = round(($product['pro#quantity_sold'] / $nodays), 3, PHP_ROUND_HALF_UP);
			$currency_symbol_sql = 'SELECT cur.sign FROM '._DB_PREFIX_.'currency cur JOIN '._DB_PREFIX_.'configuration conf ON
									cur.id_currency = conf.value where conf.name = "PS_CURRENCY_DEFAULT"';
			$currency_symbol = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($currency_symbol_sql);
			$result[$k]['currency_symbol'] = $currency_symbol[0]['sign'];
		}
		$result = array('result' => $result, 'total' => $total);
		return $result;
	}
	public function getCategorySales()
	{
		if (version_compare(_PS_VERSION_, '1.4.11.0', '='))
		{
			$sql = 'SELECT  SQL_CALC_FOUND_ROWS p.id_category_default as "ca#id_category",
					cl.name as "calang#name",sum(pro.product_quantity) AS totalQuantitySold,  	
					cur.sign AS currency_symbol,
					ROUND(IFNULL(SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity ),0),2) AS  totalPriceSold,
					ROUND(IFNULL((SUM( (pro.product_price/cur.conversion_rate) * pro.product_quantity ) - 
					SUM( pro.product_quantity * (p.wholesale_price) )),0),2) AS profit
	            	FROM '._DB_PREFIX_.'order_detail AS pro
	            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id'.$this->getFilterP().'
					JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
					LEFT JOIN '._DB_PREFIX_.'order_history oh on oh.id_order = o.id_order
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = oh.id_order_state
	            	JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
	                JOIN '._DB_PREFIX_.'category_lang as cl ON cl.id_category = p.id_category_default
	                WHERE os.invoice =1 AND cl.id_lang = 1 AND oh.date_add IN (select date_add from '._DB_PREFIX_.'order_history 
					WHERE date_add in (SELECT max(date_add) FROM '._DB_PREFIX_.'order_history group by id_order))
	                AND o.date_add BETWEEN '.$this->getDateBetween().'
					GROUP BY p.id_category_default '.$this->getSortP().''.$this->getLimitP();
		}
		else
		{
			$sql = 'SELECT  SQL_CALC_FOUND_ROWS p.id_category_default as "ca#id_category",
					cl.name as "calang#name",sum(pro.product_quantity) AS totalQuantitySold,
					ROUND(IFNULL(SUM( (pro.total_price_tax_excl/cur.conversion_rate) ),0),2) AS  totalPriceSold,
					ROUND(IFNULL((SUM( (pro.total_price_tax_excl/cur.conversion_rate) ) - SUM(pro.product_quantity
					* (pro.purchase_supplier_price) )),0),2) AS profit
	            	FROM '._DB_PREFIX_.'order_detail AS pro
	            	JOIN '._DB_PREFIX_.'product AS p ON p.id_product = pro.product_id'.$this->getFilterP().'
					JOIN '._DB_PREFIX_.'orders AS o ON o.id_order = pro.id_order
					LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
	            	JOIN '._DB_PREFIX_.'currency AS cur ON cur.id_currency = o.id_currency
	                JOIN '._DB_PREFIX_.'category_lang as cl ON cl.id_category = p.id_category_default
	                WHERE os.invoice = 1 AND cl.id_lang = 1 AND o.date_add BETWEEN '.$this->getDateBetween().'
					GROUP BY p.id_category_default '.$this->getSortP().''.$this->getLimitP();
		}
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

		$total = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');
		$count = count($result);
			for ($k = 0; $k < $count; $k++)
			{
			$currency_symbol_sql = 'SELECT cur.sign FROM '._DB_PREFIX_.'currency cur JOIN '._DB_PREFIX_.'configuration conf ON
									cur.id_currency = conf.value where conf.name = "PS_CURRENCY_DEFAULT"';
			$currency_symbol = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($currency_symbol_sql);
			$result[$k]['currency_symbol'] = $currency_symbol[0]['sign'];
			}
		$result = array('result' => $result, 'total' => $total);
		return $result;
	}
	private function getDateDays($date1, $date2)
	{
		$datetime1 = new DateTime($date1);
		$datetime2 = new DateTime($date2);
		$interval = $datetime1->diff($datetime2);
		return $interval->format('%a');
	}
	private function getCollectedData($array = array())
	{
		if (count($array) == 0)
			return 1;
		$i = count($array['result']);
		$result = $array['result'];
		$values = array();
		$category = array();
		switch ($this->getMethod())
		{
			case 'day':
				$series = $this->dateRange($this->date_from, $this->date_to, '+1 day');
				$countday = count($result);
				for ($i = 0; $i < $countday; $i++)
				{
					foreach ($result as $row)
					{
						if (array_key_exists($row['fix_date'], $series))
							$series[$row['fix_date']] = $row['data'];
					}
				}
				foreach ($series as $k => $row)
				{
					$y_data = $row;
					$values[] = array(strtotime($k) * 1000, $y_data);
				}
				$x_axis = array('type' => 'datetime');
				break;

			case 'month':
				$date_month1 = date('Y-m', strtotime($this->date_from));
				$date_month2 = date('Y-m', strtotime($this->date_to));
				$from_date = strtotime($this->date_from);
				$to_date = strtotime($this->date_to);
				$num_days = abs($from_date - $to_date) / 60 / 60 / 24;
				if ($num_days < 30)
					$series = array($date_month1 => 0,$date_month2 => 0);
				else
					$series = $this->dateRange($this->date_from, $this->date_to, '+1 month', 'Y-m');
				$countmonth = count($result);
				for ($i = 0; $i < $countmonth; $i++)
				{
					foreach ($result as $row)
					{
						if (array_key_exists($row['fix_date'], $series))
							$series[$row['fix_date']] = $row['data'];
					}
				}

				foreach ($series as $k => $row)
				{
					$y_data = $row;
					$values[] = $y_data;
					$category[] = date('M Y', strtotime($k));
				}
				$x_axis = array('categories' => $category);
				break;

			case 'year':
				$series = $this->dateRange($this->date_from, $this->date_to, '+1 year', 'Y');
				$countyear = count($result);
				for ($i = 0; $i < $countyear; $i++)
				{
					foreach ($result as $row)
					{
						if (array_key_exists($row['fix_date'], $series))
							$series[$row['fix_date']] = $row['data'];
					}
				}
				foreach ($series as $k => $row)
				{
					$y_data = $row;
					$values[] = $y_data;
					$category[] = $k;
				}
				$x_axis = array('categories' => $category);
				break;
			default:
				break;
		}
		$return_data = array('values' => $values, 'xAxis' => $x_axis);
		return $return_data;
	}
	public function getConvertedCarts()
	{
		$carts           = $this->getAbandonedCarts();
		$abandoned_carts = array('carts' => $carts, 'cnum' => count($carts));
		$total           = $this->getAbandonedCartsTotal();
		$result          = array ('result'                  => $abandoned_carts['carts'],
									'cnum'                    => $abandoned_carts['cnum'],
									'percentage_converted'    => ($abandoned_carts['cnum'] / $total) * 100,
									'total'                   => $total
									);
		return $result;
	}
public function getOriginTraffic()
{
	$direct_link = $this->l('Direct link');
	$final_array = array();
	$sql = 'SELECT http_referer FROM '._DB_PREFIX_.'connections
			WHERE date_add BETWEEN '.$this->getDateBetween().'';

	$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
	$websites = array($direct_link => 0);
	foreach ($result as $row)
	{
		if (!isset($row['http_referer']) || empty($row['http_referer']))
			++$websites[$direct_link];
		else
		{
			$website = preg_replace('/^www./', '', parse_url($row['http_referer'], PHP_URL_HOST));
			if (!isset($websites[$website]))
				$websites[$website] = 1;
			else
				++$websites[$website];
		}
	}
	arsort($websites);
	foreach ($websites as $key => $value)
		$final_array['OriginTraffic'][] = array('name' => $key,'data_val' => $value);

	return $final_array;
}
}
