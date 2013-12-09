<?php
/*
* 2007-2013 Profileo
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to contact@profileo.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade Profileo to newer
* versions in the future. If you wish to customize Profileo for your
* needs please refer to http://www.profileo.com for more information.
*
*  @author Profileo <contact@profileo.com>
*  @copyright  2007-2013 Profileo
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of Profileo
*/

if (!defined('_PS_VERSION_'))
	exit;

class TrustPilot extends Module
{

	public function __construct()
	{
		$this->name = 'trustpilot';
		$this->tab = 'advertising_marketing';
		$this->version = '1.0';
		$this->author = 'Profileo Labs';
		$this->need_instance = 0;
		$this->module_key = '';

		parent::__construct();

		$this->displayName = $this->l('Trustpilot : Customer reviews');
		$this->description = $this->l('Increase your visibility and sales with customer reviews');
		$this->secure_key = Tools::encrypt($this->name);
		/* Backward compatibility */
		if (version_compare(_PS_VERSION_, '1.5', '<'))
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
	}

	public function install()
	{
		if (!parent::install()
			|| !$this->registerHook('top')
			|| !$this->registerHook('footer')
			|| !$this->registerHook('leftColumn')
			|| !$this->registerHook('rightColumn')
			|| !$this->registerHook('shoppingCart')
			|| !$this->registerHook('postupdateorderstatus')
			|| !Configuration::updateValue('TRUSTPILOT_WIDGET1_CODE', '')
			|| !Configuration::updateValue('TRUSTPILOT_WIDGET2_CODE', '')
			|| !Configuration::updateValue('TRUSTPILOT_WIDGET1_HOOK', '')
			|| !Configuration::updateValue('TRUSTPILOT_WIDGET2_HOOK', '')
			|| !Configuration::updateValue('TRUSTPILOT_EMAIL', '')
			|| !Configuration::updateValue('TRUSTPILOT_ORDER_STATUS', '')
			|| !Configuration::updateValue('TRUSTPILOT_DELAY', '')
			|| !Configuration::updateValue('TRUSTPILOT_DOMAIN', ''))
				return false;

		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall()
		|| !Configuration::deleteByName('TRUSTPILOT_WIDGET1_CODE')
		|| !Configuration::deleteByName('TRUSTPILOT_WIDGET2_CODE')
		|| !Configuration::deleteByName('TRUSTPILOT_WIDGET1_HOOK')
		|| !Configuration::deleteByName('TRUSTPILOT_WIDGET2_HOOK')
		|| !Configuration::deleteByName('TRUSTPILOT_EMAIL')
		|| !Configuration::deleteByName('TRUSTPILOT_ORDER_STATUS')
		|| !Configuration::deleteByName('TRUSTPILOT_DELAY')
		|| !Configuration::deleteByName('TRUSTPILOT_DOMAIN'))
			return false;

		return true;
	}

	/*
	 * Main display function
	 */
	public function getContent()
	{
		$this->_html = '<h2>'.$this->l('Trustpilot Widget Configuration').'</h2>';
		if ((Tools::isSubmit('alreadHasAcc') === false) && (Configuration::get('TRUSTPILOT_DOMAIN') == ''))
			$this->_html .= $this->displayIntroForm();
		else
		{
			if (!empty($_POST))
				$this->_html .= $this->postDataProcess();
			$this->_html .= $this->displayForm();
		}
		return $this->_html;
	}

	/**
	 * Displays the Intro screen
	 */
	private function displayIntroForm()
	{
		global $currentIndex;
		$lang_id = new Language((int)$this->context->language->id);
		$this->context->smarty->assign(array(
			'currentIndex' => $currentIndex,
			'lang' => Tools::strtolower($lang_id->iso_code),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
			'module_name' => $this->name,
			'admin_token' => Tools::getAdminTokenLite('AdminModules')
		));
		return $this->display(__FILE__, 'views/templates/admin/intro_form.tpl');
	}

	/**
	 * Displays the Configuration screen
	 */
	private function displayForm()
	{
		if (!$id_lang = $this->context->language->id)
			$id_lang = Configuration::get('PS_LANG_DEFAULT');
		$tp_states = explode( ',', Configuration::get('TRUSTPILOT_ORDER_STATUS') );
		$all_states = OrderState::getOrderStates($id_lang);
		$order_statuses = array();
		if (is_array($all_states))
		{
			foreach ($all_states as $s)
				$order_statuses[$s['id_order_state']] = $s['name'];
			ksort($order_statuses);
		}
		
		$arr_widget1_pos = explode( ',', Configuration::get('TRUSTPILOT_WIDGET1_HOOK') );
		$arr_widget2_pos = explode( ',', Configuration::get('TRUSTPILOT_WIDGET2_HOOK') );

		/* Populate Hook positions */
		$arr_positions = array(
			/*'' => $this->l('Do not show'),*/
			'right' => $this->l('Right column'),
			'left' => $this->l('Left column'),
			'top' => $this->l('Top'),
			'footer' => $this->l('Footer'),
			'cart' => $this->l('Cart')
		);
		$this->context->smarty->assign(array(
			'module_name' => $this->name,
			'server_request' => Tools::safeOutput($_SERVER['REQUEST_URI']),
			'email' => Tools::safeOutput(Tools::getValue('TRUSTPILOT_EMAIL', Configuration::get('TRUSTPILOT_EMAIL'))),
			'order_statuses'=> $order_statuses,
			'tp_states' => $tp_states,
			'delay' => Tools::safeOutput(Tools::getValue('TRUSTPILOT_DELAY', Configuration::get('TRUSTPILOT_DELAY'))),
			'domain' => Tools::safeOutput(Tools::getValue('TRUSTPILOT_DOMAIN', Configuration::get('TRUSTPILOT_DOMAIN'))),
			'tp_widget1' => (Tools::getValue('tp_widget1', Configuration::get('TRUSTPILOT_WIDGET1_CODE'))),
			'tp_widget2' => (Tools::getValue('tp_widget2', Configuration::get('TRUSTPILOT_WIDGET2_CODE'))),
			'arr_positions' => $arr_positions,
			'arr_widget1_pos' => $arr_widget1_pos,
			'arr_widget2_pos' => $arr_widget2_pos,
		));
		return $this->display(__FILE__, 'views/templates/admin/form.tpl');
	}

	/**
	 * Form submit processing
	 */
	private function postDataProcess()
	{
		/* Trustpilot account configuration submit */
		if (Tools::getIsset('submitTrustPilotConfig'))
		{
			if (Tools::getValue('tp_email') == '')
				return $this->displayError( $this->l('Email used by Trustpilot is mandatory.') );
			elseif (Tools::getValue('tp_email') != '' && !Validate::isEmail(Tools::getValue('tp_email')))
				return $this->displayError( $this->l('The Trustpilot email entered is invalid.') );

			Configuration::updateValue( 'TRUSTPILOT_EMAIL', Tools::getValue('tp_email') );
			Configuration::updateValue( 'TRUSTPILOT_DELAY', Tools::getValue('tp_delay') );
			Configuration::updateValue( 'TRUSTPILOT_DOMAIN', Tools::getValue('tp_domain') );

			if (Tools::getIsset('tp_order_status') && is_array( Tools::getValue('tp_order_status')))
				Configuration::updateValue( 'TRUSTPILOT_ORDER_STATUS', implode( ',', Tools::getValue('tp_order_status') ) );
			else
				Configuration::updateValue( 'TRUSTPILOT_ORDER_STATUS', '' );

			return $this->displayConfirmation( $this->l('Configuration saved.') );
		}

		/* Widget Box form submit */
		if (Tools::getIsset('submitTrustPilotTrustBox'))
		{
			Configuration::updateValue( 'TRUSTPILOT_WIDGET1_CODE', Tools::getValue('tp_widget1'), true);
			Configuration::updateValue( 'TRUSTPILOT_WIDGET2_CODE', Tools::getValue('tp_widget2'), true);

			/* Select Multi fields */
			if (Tools::getIsset('tp_widget1_pos') && is_array( Tools::getValue('tp_widget1_pos')))
				Configuration::updateValue( 'TRUSTPILOT_WIDGET1_HOOK', implode( ',', Tools::getValue('tp_widget1_pos') ) );
			else
				Configuration::updateValue( 'TRUSTPILOT_WIDGET1_HOOK', '' );

			/* Select Multi fields */
			if (Tools::getIsset('tp_widget2_pos') && is_array( Tools::getValue('tp_widget2_pos')))
				Configuration::updateValue( 'TRUSTPILOT_WIDGET2_HOOK', implode( ',', Tools::getValue('tp_widget2_pos') ) );
			else
				Configuration::updateValue( 'TRUSTPILOT_WIDGET2_HOOK', '' );

			return $this->displayConfirmation( $this->l('Widgets configuration saved.') );
		}

		/* Kickstart form submit */
		if (Tools::getIsset('submitKickstart'))
		{
			if (Tools::getIsset('tp_kstartnb'))
			{
				if (is_numeric(Tools::getValue('tp_kstartnb')) && ceil(Tools::getValue('tp_kstartnb')) > 0)
				{
					if (version_compare(_PS_VERSION_, '1.5.0') >= 0)
					{
						$sql = 'SELECT c.firstname, c.lastname, c.email, o.id_order
						FROM '._DB_PREFIX_.'customer c
						LEFT JOIN '._DB_PREFIX_.'orders o ON c.id_customer = o.id_customer
						LEFT JOIN '._DB_PREFIX_.'order_state os ON os.id_order_state = o.current_state
						WHERE o.id_order IS NOT NULL
						AND os.paid = 1
						GROUP BY c.id_customer
						ORDER BY o.id_order DESC
						LIMIT 0,'.ceil(Tools::getValue('tp_kstartnb'));
					}
					else
					{
						$sql = 'SELECT c.firstname, c.lastname, c.email, o.id_order
						FROM '._DB_PREFIX_.'customer c
						LEFT JOIN '._DB_PREFIX_.'orders o ON c.id_customer = o.id_customer
						WHERE o.id_order IS NOT NULL
						AND o.invoice_number > 0
						GROUP BY c.id_customer
						ORDER BY o.id_order DESC
						LIMIT 0,'.ceil(Tools::getValue('tp_kstartnb'));
					}

					$results = Db::getInstance()->ExecuteS($sql);
					$index = 0;
					foreach ($results as $row)
					{
						$arr_rows[$index][] = ''.$row['email'];
						$arr_rows[$index][] = ''.$row['firstname'].' '.$row['lastname'];
						$strlength = Tools::strlen($row['id_order']);
						if ($strlength < 6)
							$addzero = 6 - $strlength;
						$zero = '';
						while ($addzero != 0)
						{
							$zero .= '0';
							$addzero--;
						}
						$arr_rows[$index][] = ''.$zero.$row['id_order'];
						$index++;
					}
					$this->exportCSVData($arr_rows, ceil(Tools::getValue('tp_kstartnb')));
				}
				else
					return $this->displayError( $this->l('Kickstart: Number of clients is mandatory and should be a numerical value.') );
			}
		}
	}

	/**
	 * Export to CSV general function
	 */
	private function exportCSVData($arr_customers, $nb)
	{
		$filename = $nb.'_customer_export_'.date('Y-m-d').'.csv';
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check = 0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Type: application/octet-stream');
		header('Content-Type: application/download');
		header('Content-Disposition: attachment;filename = '.$filename);
		header('Content-Transfer-Encoding: binary');

		ob_get_clean();
		$df = fopen('php://output', 'w');

		if (is_array($arr_customers))
		{
			foreach ($arr_customers as $row)
				fputs($df, implode($row, ',')."\n");
		}
		fclose($df);
		exit();
	}

	/**
	 * Order status change : send mail
	 */
	public function hookPostUpdateOrderStatus($params)
	{
		/* Language tag array */
		$lang_tag_iso = array( 'da', 'fr', 'de', 'it', 'nl', 'no', 'sv', 'en', 'fi', 'el', 'pt', 'ro', 'ru', 'es', 'tr' );
		$lang_tag = array(
			'da' => array( 'lang' => 'da-DK', 'tld' => 'dk' ),
			'fr' => array( 'lang' => 'fr-FR', 'tld' => 'fr' ),
			'de' => array( 'lang' => 'de-DE', 'tld' => 'de' ),
			'it' => array( 'lang' => 'it-IT', 'tld' => 'it' ),
			'nl' => array( 'lang' => 'nl-NL', 'tld' => 'nl' ),
			'no' => array( 'lang' => 'no-NO', 'tld' => 'no' ),
			'sv' => array( 'lang' => 'sv-SE', 'tld' => 'se' ),
			'en' => array( 'lang' => 'en-GB', 'tld' => 'co.uk' ),
			'fi' => array( 'lang' => 'fi-FI', 'tld' => 'fi' ),
			'el' => array( 'lang' => 'el-GR', 'tld' => 'gr' ),
			'pt' => array( 'lang' => 'pt-PT', 'tld' => 'pt' ),
			'ro' => array( 'lang' => 'ro-RO', 'tld' => 'ro' ),
			'ru' => array( 'lang' => 'ru-RU', 'tld' => 'ru' ),
			'es' => array( 'lang' => 'es-ES', 'tld' => 'es' ),
			'tr' => array( 'lang' => 'tr-TR', 'tld' => 'tr' )
		);
		$new_status_id = $params['newOrderStatus']->id;
		$strlength = Tools::strlen($params['id_order']);
		if ($strlength < 6)
			$addzero = 6 - $strlength;
		$zero = '';
		while ($addzero != 0)
		{
			$zero .= '0';
			$addzero--;
		}
		$order_id = $zero.$params['id_order'];

		$template_vars['{tp_domain}'] = Configuration::get( 'TRUSTPILOT_DOMAIN' );
		$template_vars['{tp_delay}'] = Configuration::get( 'TRUSTPILOT_DELAY' );
		$template_vars['{order_id}'] = $order_id;
		$template_vars['{shop_logo}'] = '';

		$mail_to = Configuration::get( 'TRUSTPILOT_EMAIL' ); /* Trustpilot email */

		if (!$id_lang = Language::getIdByIso('fr'))
			$id_lang = $this->context->language->id;

		$template_name = 'trustpilotmail'; /* Mail template */
		$mail_dir = _PS_MODULE_DIR_.'trustpilot/mails/'; /* Mail template directory */
		$title = Mail::l('Trustpilot Mail'); /* Mail subject */
		$from = Configuration::get('PS_SHOP_EMAIL');   /* Sender's email */
		$from_name = Configuration::get('PS_SHOP_NAME'); /* Sender's name */

		/* Check if new status is in module's settings */
		$order_statuses = explode( ',', Configuration::get( 'TRUSTPILOT_ORDER_STATUS' ) );
		if (in_array($new_status_id, $order_statuses))
		{
			$customer_sql = 'SELECT c.firstname, c.lastname, c.email, lng.iso_code 
			FROM '._DB_PREFIX_.'customer c
			LEFT JOIN '._DB_PREFIX_.'orders o ON c.id_customer = o.id_customer 
			LEFT JOIN '._DB_PREFIX_.'lang lng ON o.id_lang = lng.id_lang 
			WHERE o.id_order ='.(int)$order_id;

			if ($customer = Db::getInstance()->getRow($customer_sql))
			{
				/* Send email */
				$template_vars['{client_fullname}'] = $customer['firstname'].' '.$customer['lastname'];
				$template_vars['{client_mail}'] = $customer['email'];
				/* Language tag */
				$lang_tag_id = 'en';
				if (in_array($customer['iso_code'], $lang_tag_iso))
					$lang_tag_id = $customer['iso_code'];

				$template_vars['{client_lang}'] = $lang_tag[$lang_tag_id]['lang'];
				$template_vars['{client_lien}'] = $lang_tag[$lang_tag_id]['tld'];

				if (Mail::Send($id_lang, $template_name, $title, $template_vars, $mail_to, null, $from, $from_name, null, null, $mail_dir))
					/* display success message */$test;
				else
					return $this->displayError( $this->l('Trustpilot mail was not sent.') );

			}
		}
	}

	/**
	* Displays widget according to hook selected
	*/
	public function displayWidget($hook)
	{
		$widget1 = Configuration::get('TRUSTPILOT_WIDGET1_CODE', null);
		$widget2 = Configuration::get('TRUSTPILOT_WIDGET2_CODE', null);

		if ((!$widget1 && !$widget2) || (!$this->isHooked(1, $hook) && !$this->isHooked(2, $hook)))
			return;
		//init
		$this->context->smarty->assign(array(
			'widget1' => '',
			'widget2' => ''
		));

		/* assign widgets only if the corresponding hook has been selected in config */
		if ($widget1 && $this->isHooked(1, $hook))
		{
			$tmp_tp_widget1 = $widget1;
			
			// Initializing variables
			$widget1 = false;
			$tp_wg1_id_domaine = '';
			$tp_wg1_site_url = '';
			$tp_wg1_domaine = '';
			$tp_wg1_site_name = '';
			
			$exploded_str1 = explode('"', $tmp_tp_widget1);
			$proceed_extraction1 = false;
			if(isset($exploded_str1[3]) && $exploded_str1[3]!='') 
			{
				if(isset($exploded_str1[5]) && $exploded_str1[5]!='') 
				{
					if(isset($exploded_str1[8]) && $exploded_str1[8]!='') 
					{
						$proceed_extraction1 = true;
					}
				}
			}
			
			if ($proceed_extraction1) 
			{
				$segment_1 = $exploded_str1[3]; // [3] => domainId:5349800
				$segment_2 = $exploded_str1[5]; // [5] => http://www.trustpilot.fr/review/tolstrupbech.com
				$segment_3 = $exploded_str1[8]; // [8] =>  hidden>Tolstrupbech Avis</a>...
				
				// Retrieving ID Domaine
				$exploded_str1_1 = explode(':', $segment_1);
				$tp_wg1_id_domaine = (int)$exploded_str1_1[1];
				
				// Retrieving Site URL & domain name
				$exploded_str1_2 = explode('/', $segment_2);
				$tp_wg1_site_url = $exploded_str1_2[2];
				$tp_wg1_domaine = $exploded_str1_2[4];
				
				// Retrieving site name
				$exploded_str1_3 = explode('>', $segment_3);
				$exploded_str1_4 = explode('<', $exploded_str1_3[1]);
				$tp_wg1_site_name = $exploded_str1_4[0];
				
				if (!empty($tp_wg1_id_domaine) &&
						!empty($tp_wg1_site_url) &&
						!empty($tp_wg1_domaine) &&
						!empty($tp_wg1_site_name) )
					$widget1 = true;
				
				
			} 

			$this->context->smarty->assign('widget1', $widget1);
			$this->context->smarty->assign('tp_wg1_id_domaine', $tp_wg1_id_domaine);
			$this->context->smarty->assign('tp_wg1_site_url', $tp_wg1_site_url);
			$this->context->smarty->assign('tp_wg1_domaine', $tp_wg1_domaine);
			$this->context->smarty->assign('tp_wg1_site_name', $tp_wg1_site_name);
			
		}
                
		if ($widget2 && $this->isHooked(2, $hook)) 
		{
			$tmp_tp_widget2 = $widget2;
			
			// Initializing variables
			$widget2 = false;
			$tp_wg2_id_domaine = '';
			$tp_wg2_site_url = '';
			$tp_wg2_domaine = '';
			$tp_wg2_site_name = '';
			
			$exploded_str2 = explode('"', $tmp_tp_widget2);
			$proceed_extraction2 = false;
			if(isset($exploded_str2[3]) && $exploded_str2[3]!='') 
			{
				if(isset($exploded_str2[5]) && $exploded_str2[5]!='') 
				{
					if(isset($exploded_str2[8]) && $exploded_str2[8]!='') 
					{
						$proceed_extraction2 = true;
					}
				}
			}
			
			if ($proceed_extraction2) 
			{
				$segment_1 = $exploded_str2[3]; // [3] => domainId:5349800
				$segment_2 = $exploded_str2[5]; // [5] => http://www.trustpilot.fr/review/tolstrupbech.com
				$segment_3 = $exploded_str2[8]; // [8] =>  hidden>Tolstrupbech Avis</a>...
				
				// Retrieving ID Domaine
				$exploded_str2_1 = explode(':', $segment_1);
				$tp_wg2_id_domaine = (int)$exploded_str2_1[1];
				
				// Retrieving Site URL & domain name
				$exploded_str2_2 = explode('/', $segment_2);
				$tp_wg2_site_url = $exploded_str2_2[2];
				$tp_wg2_domaine = $exploded_str2_2[4];
				
				// Retrieving site name
				$exploded_str2_3 = explode('>', $segment_3);
				$exploded_str2_4 = explode('<', $exploded_str2_3[1]);
				$tp_wg2_site_name = $exploded_str2_4[0];
				
				
				if (!empty($tp_wg2_id_domaine) &&
						!empty($tp_wg2_site_url) &&
						!empty($tp_wg2_domaine) &&
						!empty($tp_wg2_site_name) )
					$widget2 = true;
			}

			$this->context->smarty->assign('widget2', $widget2);
			$this->context->smarty->assign('tp_wg2_id_domaine', $tp_wg2_id_domaine);
			$this->context->smarty->assign('tp_wg2_site_url', $tp_wg2_site_url);
			$this->context->smarty->assign('tp_wg2_domaine', $tp_wg2_domaine);
			$this->context->smarty->assign('tp_wg2_site_name', $tp_wg2_site_name);
		}

		$this->context->smarty->assign('position', $hook);
		return $this->display(__FILE__, 'views/templates/hook/trustpilot_widget.tpl');
	}

	/*
	* Top Hook
	*/
	public function hookTop()
	{
		return $this->displayWidget('top');
	}

	/*
	* Footer Hook
	*/
	public function hookFooter()
	{
		return $this->displayWidget('footer');
	}

	/*
	* Right Column Hook
	*/
	public function hookRightColumn()
	{
		return $this->displayWidget('right');
	}

	/*
	* Left Column Hook
	*/
	public function hookLeftColumn()
	{
		return $this->displayWidget('left');
	}

	/*
	* Cart Hook
	*/
	public function hookShoppingCart()
	{
		return $this->displayWidget('cart');
	}

	/*
	* Recover Hook
	*/
	private function isHooked($id_widget = 1, $hook)
	{
		$hooks = Configuration::get('TRUSTPILOT_WIDGET'.$id_widget.'_HOOK', null);
		if ($hooks)
		{
			$hooks = explode(',', $hooks);
			return in_array($hook, $hooks);
		}
		return false;
	}
}