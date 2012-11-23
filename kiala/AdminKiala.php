<?php
/*
* 2007-2011 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__).'/classes/KialaOrder.php');
require_once(dirname(__FILE__).'/classes/ExportFormat.php');
require_once(dirname(__FILE__).'/classes/KialaCountry.php');
require_once(dirname(__FILE__).'/classes/KialaRequest.php');

class AdminKiala extends AdminTab
{
	public function __construct()
	{
		global $cookie;
		$this->table = 'kiala_order';
		$this->className = 'KialaOrder';

	 	$this->edit = true;
	 	$this->noAdd = true;

	 	if(!$id_lang = $cookie->id_lang)
	 		$id_lang = Configuration::get('PS_LANG_DEFAULT');

 		$this->fieldsDisplay = array(
		'id_kiala_order' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'exported' => array('title' => $this->l('Exported'), 'width' => 25, 'align' => 'center', 'type' => 'bool', 'filter_key' => 'exported', 'tmpTableFilter' => true, 'icon' => array(0 => 'blank.gif', 1 => 'enabled.gif')),
		'customer' => array('title' => $this->l('Customer'), 'width' => 120, 'filter_key' => 'customer', 'tmpTableFilter' => true),
 		'country' => array('title' => $this->l('Country'), 'width' => 80, 'filter_key' => 'country', 'tmpTableFilter' => true),
		'kiala_point' => array('title' => $this->l('Kiala point'), 'width' => 60),
 		'total_paid' => array('title' => $this->l('Total'), 'width' => 60, 'align' => 'right', 'prefix' => '<b>', 'suffix' => '</b>', 'price' => true, 'currency' => true),
		'osname' => array('title' => $this->l('Payment'), 'width' => 90),
		'date_add' => array('title' => $this->l('Order date'), 'width' => 35, 'align' => 'right', 'type' => 'datetime', 'filter_key' => 'a!date_add'),
		'invoice_date' => array('title' => $this->l('Invoice date'), 'width' => 35, 'align' => 'right', 'type' => 'datetime', 'filter_key' => 'a!invoice_date'));

 		$this->_select = 'o.total_paid, o.id_currency, o.date_add, o.invoice_date, CONCAT(c.firstname, \' \', c.lastname) as customer, cl.name as country, osl.`name` AS `osname`';
		$this->_join = 'LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = a.`id_order`)
						LEFT JOIN '._DB_PREFIX_.'customer c ON (c.id_customer = a.id_customer)
						LEFT JOIN '._DB_PREFIX_.'country_lang cl ON (cl.id_country = a.id_country_delivery) AND (cl.id_lang = '.(int)$id_lang.')
						LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON (oh.`id_order` = a.`id_order`)
						LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = oh.`id_order_state`)
						LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)($cookie->id_lang).')';
		$this->_where = 'AND a.id_order != 0 AND oh.`id_order_history` = (SELECT MAX(`id_order_history`) FROM `'._DB_PREFIX_.'order_history` moh WHERE moh.`id_order` = a.`id_order` GROUP BY moh.`id_order`)';
		$this->_orderWay = 'DESC';
		// Clean old empty kiala orders
		KialaOrder::cleanEmptyOrders();

		parent::__construct();

	}

	/**
	 * Override parent function to display a list with bulk selection and export
	 */
	public function displayListContent($token = NULL)
	{
		/* Display results in a table
		 *
		 * align  : determine value alignment
		 * prefix : displayed before value
		 * suffix : displayed after value
		 * image  : object image
		 * icon   : icon determined by values
		 * active : allow to toggle status
		 */

		global $currentIndex, $cookie;
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

		$id_category = 1; // default categ

		$irow = 0;
		if ($this->_list AND isset($this->fieldsDisplay['position']))
		{
			$positions = array_map(create_function('$elem', 'return (int)($elem[\'position\']);'), $this->_list);
			sort($positions);
		}

		if ($this->_list)
		{
			$isCms = false;
			$keyToGet = 'id_'.($isCms ? 'cms_' : '').'category'.(in_array($this->identifier, array('id_category', 'id_cms_category')) ? '_parent' : '');
			foreach ($this->_list AS $tr)
			{
				$id = $tr[$this->identifier];
				echo '<tr'.(array_key_exists($this->identifier,$this->identifiersDnd) ? ' id="tr_'.(($id_category = (int)(Tools::getValue('id_'.($isCms ? 'cms_' : '').'category', '1'))) ? $id_category : '').'_'.$id.'_'.$tr['position'].'"' : '').($irow++ % 2 ? ' class="alt_row"' : '').' '.((isset($tr['color']) AND $this->colorOnBackground) ? 'style="background-color: '.$tr['color'].'"' : '').'>
							<td class="center">';

				echo '<input type="checkbox" name="'.$this->table.'Box[]" value="'.$id.'" class="noborder" />';
				echo '</td>';
				foreach ($this->fieldsDisplay AS $key => $params)
				{
					$tmp = explode('!', $key);
					$key = isset($tmp[1]) ? $tmp[1] : $tmp[0];
					echo '
					<td '.(isset($params['position']) ? ' id="td_'.(isset($id_category) AND $id_category ? $id_category : 0).'_'.$id.'"' : '').' class="'.((!isset($this->noLink) OR !$this->noLink) ? 'pointer' : '').((isset($params['position']) AND $this->_orderBy == 'position')? ' dragHandle' : ''). (isset($params['align']) ? ' '.$params['align'] : '').'" ';
						if (!isset($params['position']) AND (!isset($this->noLink) OR !$this->noLink))
							echo ' onclick="document.location = \''.$currentIndex.'&'.$this->identifier.'='.$id.($this->view? '&view' : '&update').$this->table.'&token='.($token != NULL ? $token : $this->token).'\'">'.(isset($params['prefix']) ? $params['prefix'] : '');
						else
							echo '>';
						if (isset($params['active']) AND isset($tr[$key]))
							$this->_displayEnableLink($token, $id, $tr[$key], $params['active'], Tools::getValue('id_category'), Tools::getValue('id_product'));
						elseif (isset($params['activeVisu']) AND isset($tr[$key]))
							echo '<img src="../img/admin/'.($tr[$key] ? 'enabled.gif' : 'disabled.gif').'"
							alt="'.($tr[$key] ? $this->l('Enabled') : $this->l('Disabled')).'" title="'.($tr[$key] ? $this->l('Enabled') : $this->l('Disabled')).'" />';
						elseif (isset($params['position']))
						{
							if ($this->_orderBy == 'position' AND $this->_orderWay != 'DESC')
							{
								echo '<a'.(!($tr[$key] != $positions[sizeof($positions) - 1]) ? ' style="display: none;"' : '').' href="'.$currentIndex.
										'&'.$keyToGet.'='.(int)($id_category).'&'.$this->identifiersDnd[$this->identifier].'='.$id.'
										&way=1&position='.(int)($tr['position'] + 1).'&token='.($token != NULL ? $token : $this->token).'">
										<img src="../img/admin/'.($this->_orderWay == 'ASC' ? 'down' : 'up').'.gif"
										alt="'.$this->l('Down').'" title="'.$this->l('Down').'" /></a>';

								echo '<a'.(!($tr[$key] != $positions[0]) ? ' style="display: none;"' : '').' href="'.$currentIndex.
										'&'.$keyToGet.'='.(int)($id_category).'&'.$this->identifiersDnd[$this->identifier].'='.$id.'
										&way=0&position='.(int)($tr['position'] - 1).'&token='.($token != NULL ? $token : $this->token).'">
										<img src="../img/admin/'.($this->_orderWay == 'ASC' ? 'up' : 'down').'.gif"
										alt="'.$this->l('Up').'" title="'.$this->l('Up').'" /></a>';						}
							else
								echo (int)($tr[$key] + 1);
						}

						elseif (isset($params['icon']) AND (isset($params['icon'][$tr[$key]]) OR isset($params['icon']['default'])))
							echo '<img src="../img/admin/'.(isset($params['icon'][$tr[$key]]) ? $params['icon'][$tr[$key]] : $params['icon']['default'].'" alt="'.$tr[$key]).'" title="'.$tr[$key].'" />';
						elseif (isset($params['price']))
							echo Tools::displayPrice($tr[$key], (isset($params['currency']) ? Currency::getCurrencyInstance((int)($tr['id_currency'])) : $currency), false);
						elseif (isset($params['float']))
							echo rtrim(rtrim($tr[$key], '0'), '.');
						elseif (isset($params['type']) AND $params['type'] == 'date')
							echo Tools::displayDate($tr[$key], (int)$cookie->id_lang);
						elseif (isset($params['type']) AND $params['type'] == 'datetime')
						{
							// Tools::displayDate on empty date can result in a die(), we don't want that.
							if ($tr[$key] == "0000-00-00 00:00:00")
								echo '-';
							else
								echo Tools::displayDate($tr[$key], (int)$cookie->id_lang, true);
						}
						elseif (isset($tr[$key]))
						{
							$echo = ($key == 'price' ? round($tr[$key], 2) : isset($params['maxlength']) ? Tools::substr($tr[$key], 0, $params['maxlength']).'...' : $tr[$key]);
							echo isset($params['callback']) ? call_user_func_array(array($this->className, $params['callback']), array($echo, $tr)) : $echo;
						}
						elseif ($key == 'kiala_point')
						{
							$kiala_request = new KialaRequest();
							$url = $kiala_request->getDetailsRequest($tr['point_short_id'], $tr['id_country_delivery'], $cookie->id_lang);
							if ($url)
								echo '<a class="link blue" href="'.Tools::safeOutput($url).'">'.$tr['point_name'].'</a>';
							else
								echo '--';
						}
						else
							echo '--';

						echo (isset($params['suffix']) ? $params['suffix'] : '').
					'</td>';
				}

				echo '<td class="center" style="white-space: nowrap;">';
				$this->_displayEditLink($token, $id);
				$this->_displayExportLink($token, $id);
				echo '</td>';

				echo '</tr>';
			}
		}
	}

	protected function _displayExportLink($token = NULL, $id)
	{
		global $currentIndex;

		$_cacheLang['Export'] = $this->l('Export');

		echo '
			<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&export&token='.($token != NULL ? $token : $this->token).'">
			<img src="../img/admin/arrow_down.png	" alt="" title="'.$_cacheLang['Export'].'" /></a>';
	}

	/**
	 * Close list table and submit button
	 */
	public function displayListFooter($token = NULL)
	{
		echo '</table>';
		echo '<p><input type="submit" class="button" name="exportBatch" value="'.$this->l('Export selection').'" onclick="return confirm(\''.$this->l('Export selected items?', __CLASS__, TRUE, FALSE).'\');" /></p>';
		echo '
				</td>
			</tr>
		</table>
		<input type="hidden" name="token" value="'.($token ? $token : $this->token).'" />
		</form>';
		if (isset($this->_includeTab) AND sizeof($this->_includeTab))
			echo '<br /><br />';
	}

	/**
	 * Manage page display (form, list...)
	 * Overrides parent to display by descending ID by default
	 *
	 * @global string $currentIndex Current URL in order to keep current Tab
	 */
	public function display()
	{
		global $currentIndex, $cookie;

		// Include current tab
		if ((Tools::getValue('submitAdd'.$this->table) AND sizeof($this->_errors)) OR isset($_GET['add'.$this->table]))
		{
			if ($this->tabAccess['add'] === '1')
			{
				$this->displayForm();
				if ($this->tabAccess['view'])
					echo '<br /><br /><a href="'.((Tools::getValue('back')) ? Tools::getValue('back') : $currentIndex.'&token='.$this->token).'"><img src="../img/admin/arrow2.gif" /> '.((Tools::getValue('back')) ? $this->l('Back') : $this->l('Back to list')).'</a><br />';
			}
			else
				echo $this->l('You do not have permission to add here');
		}
		elseif (isset($_GET['update'.$this->table]))
		{
			if ($this->tabAccess['edit'] === '1' OR ($this->table == 'employee' AND $cookie->id_employee == Tools::getValue('id_employee')))
			{
				$this->displayForm();
				if ($this->tabAccess['view'])
					echo '<br /><br /><a href="'.((Tools::getValue('back')) ? Tools::getValue('back') : $currentIndex.'&token='.$this->token).'"><img src="../img/admin/arrow2.gif" /> '.((Tools::getValue('back')) ? $this->l('Back') : $this->l('Back to list')).'</a><br />';
			}
			else
				echo $this->l('You do not have permission to edit here');
		}
		elseif (isset($_GET['view'.$this->table]))
			$this->{'view'.$this->table}();

		else
		{
			$this->getList((int)($cookie->id_lang), !Tools::getValue($this->table.'Orderby') ? $this->_defaultOrderBy : NULL, !Tools::getValue($this->table.'Orderway') ? 'DESC' : NULL);
			$this->displayList();
		}
	}

	public function postProcess()
	{
		global $currentIndex;

		$token = Tools::getValue('token') ? Tools::getValue('token') : $this->token;

		if (Tools::isSubmit('export'))
		{
			$export = new ExportFormat(Module::getInstanceByName('kiala'));
			$kiala_order = new KialaOrder(Tools::getValue('id_kiala_order'));
			$export->export($kiala_order);

			if(!$export->exportContent)
				$this->_errors[] = Tools::displayError('Exporting failed.');
			else
			{
				// Add the correct headers, this forces the file is saved
				ob_clean();
				header('Content-Type: text/plain');
				header('Content-Disposition: attachment; filename="'.Configuration::get('KIALA_LAST_EXPORT_FILE').'"');
				echo $export->exportContent;
				exit;
			}
		}
		elseif(Tools::isSubmit('exportBatch'))
		{
			$kiala_orders = array();
			foreach(Tools::getValue($this->table.'Box') as $id)
			{
				$kiala_order = new KialaOrder($id);
				$kiala_orders[] = $kiala_order;
			}

			$export = new ExportFormat(Module::getInstanceByName('kiala'));

			if(!$export->exportBatch($kiala_orders))
				$this->_errors[] = Tools::displayError('Exporting failed.');
			else
			{
				ob_clean();
				// Add the correct headers, this forces the file is saved
				header('Content-Type: text/plain');
				header('Content-Disposition: attachment; filename="'.Configuration::get('KIALA_LAST_EXPORT_FILE').'"');
				echo $export->exportContent;
				exit;
			}
		}
		parent::postProcess();
	}

	public function displayForm($isMainTab = true)
	{
		global $currentIndex;
		parent::displayForm();

		if (!($kiala_order = $this->loadObject(true)))
			return;

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data">
		'.($kiala_order->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$kiala_order->id.'" />' : '').'
			<fieldset><legend><img src="'._PS_ADMIN_IMG_.'AdminKiala.gif" />'.$this->l('Kiala orders').'</legend>
				<label>'.$this->l('Commercial value:').' </label>
				<div class="margin-form">
					<input type="text" size="9" name="commercialValue" value="'.(float)$kiala_order->commercialValue.'" /> <sup>*</sup>
				</div>
				<label>'.$this->l('Parcel volume:').' </label>
				<div class="margin-form">
					<input type="text" size="6" name="parcelVolume" value="'.(float)$kiala_order->parcelVolume.'" /> <sup>*</sup>
				</div>
				<label>'.$this->l('Parcel description:').' </label>
				<div class="margin-form">
					<textarea cols="100" rows="3" name="parcelDescription" value="'.htmlentities($kiala_order->parcelDescription, ENT_COMPAT, 'UTF-8').'"></textarea>
				</div>
				<label>'.$this->l('Already exported:').' </label>
				<div class="margin-form">
					<input type="radio" name="exported" id="exported_on" value="1" '.($this->getFieldValue($kiala_order, 'exported') ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Yes').'" title="'.$this->l('Yes').'" /></label>
					<input type="radio" name="exported" id="exported_off" value="0" '.(!$this->getFieldValue($kiala_order, 'exported') ? 'checked="checked" ' : '').'/>
					<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('No').'" title="'.$this->l('No').'" /></label>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}

	public function displayConf()
	{
		if (Tools::getValue('export_success') == 1)
			echo '
			<div class="conf">
				<img src="../img/admin/ok2.png" alt="" /> '.$this->l('Export successful to file ').Configuration::get('KIALA_LAST_EXPORT_FILE').$this->l(' in folder ').Configuration::get('KIALA_EXPORT_FOLDER').'
			</div>';
	}
}

?>
