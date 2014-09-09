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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @version  Release: 0.4.4
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class User
{
	const FILENAME = 'User';

	public static function newUser()
	{
		try
		{
			$nif_dni = utf8_decode(Tools::getValue('nif_dni'));
			$nif_dni = preg_replace('([^A-Za-z0-9])', '', $nif_dni);

			$sc_options = array('connection_timeout' => 30 );
			$soap_client = new SoapClient((string)Configuration::get('SEUR_URLWS_SP'), $sc_options);
			$plano = '<peticion>
				<p_nif>'.pSQL($nif_dni).'</p_nif>
				<p_franquicia>'.pSQL(Tools::getValue('franchise_cfg')).'</p_franquicia>
				<p_ccc>'.pSQL(Tools::getValue('ccc_cfg')).'-'.pSQL((Tools::getValue('franchise_cfg'))).'</p_ccc>
				<razon_social>'.pSQL(Tools::getValue('company_name')).'</razon_social>
				<p_nombre>'.pSQL(Tools::getValue('name')).'</p_nombre>
				<p_apellidos>'.pSQL(Tools::getValue('first_name')).'</p_apellidos>
				<p_tipo_via>'.pSQL(Tools::getValue('street_type')).'</p_tipo_via>
				<p_nom_via>'.pSQL(Tools::getValue('street_name')).'</p_nom_via>
				<p_tipo_num_via>N.</p_tipo_num_via>
				<p_numero>'.pSQL(Tools::getValue('street_number')).'</p_numero>
				<p_escalera>'.pSQL(Tools::getValue('staircase')).'</p_escalera>
				<p_piso>'.pSQL(Tools::getValue('floor')).'</p_piso>
				<p_puerta>'.pSQL(Tools::getValue('door')).'</p_puerta>
				<p_poblacion>'.pSQL(Tools::getValue('town_cfg')).'</p_poblacion>
				<p_provincia>'.pSQL(Tools::getValue('state_cfg')).'</p_provincia>
				<p_cp>'.pSQL(Tools::getValue('post_code_cfg')).'</p_cp>
				<p_pais>'.pSQL(Tools::getValue('country_cfg')).'</p_pais>
				<p_telefono>'.pSQL(Tools::getValue('phone')).'</p_telefono>
				<p_fax>'.pSQL(Tools::getValue('fax')).'</p_fax>
				<p_email>'.pSQL(Tools::getValue('email')).'</p_email>
				<p_tipo_ecommerce>4</p_tipo_ecommerce>
				<usuario>'.Configuration::get('SEUR_WS_USERNAME').'</usuario>
				<password>'.Configuration::get('SEUR_WS_PASSWORD').'</password>
			</peticion>';

			$data = array('in0' => Tools::strtoupper($plano));

			$user = $pass = $ccc = '';
			$cit = 0;
			$success = false;

			$response = $soap_client->creacionClienteIntegradoStr($data);

			$string_xml = htmlspecialchars_decode((($response->out)));
			$xml = simplexml_load_string($string_xml);

			if (!empty($xml->REG1->DESCRIPCION_ERROR))
			{
				$user = Tools::isSubmit('user_cfg') ? Tools::getValue('user_cfg') : '';
				$pass = Tools::isSubmit('pass_cfg') ? Tools::getValue('pass_cfg') : '';
				$ccc = Tools::isSubmit('ccc_cfg') ? Tools::getValue('ccc_cfg') : '';
				$cit = Tools::isSubmit('ci') ? Tools::getValue('ci') : '';
				if (!SeurLib::setMerchantField('user', $user) ||
					!SeurLib::setMerchantField('pass', $pass) ||
					!SeurLib::setMerchantField('ccc', $ccc ||
					!SeurLib::setMerchantField('cit', $cit)))
					return -2;
				return $xml->REG1->DESCRIPCION_ERROR;
			}

			$user = (string)$xml->REG1->USUARIO_CIT;
			$pass = (string)$xml->REG1->CLAVE_CONEX__CIT;
			$ccc = (string)$xml->REG1->COD_CLIENTE_CCC;
			$cit = (int)$xml->REG1->COD_CLIENTE_CIT;

			if ((Tools::strlen($user) > 0)
				&& (Tools::strlen($pass) > 0)
				&& (Tools::strlen($ccc) > 0)
				&& ($cit > 0))
				$success = true;

			if ($success == false)
				return -1;
		}
		catch (PrestaShopException $e)
		{
			$e->displayMessage();
		}

		$ccc = explode('-', $ccc);

		if (!SeurLib::setMerchantField('user', $user) ||
			!SeurLib::setMerchantField('pass', $pass) ||
			!SeurLib::setMerchantField('ccc', $ccc[0]) ||
			!SeurLib::setMerchantField('cit', $cit))
			return -2;

		$emailData = array(
			'{nif_dni}' => $nif_dni,
			'{franquicia}' => (Tools::getValue('franchise_cfg')),
			'{ccc}' => (Tools::getValue('ccc_cfg')).'-'.(Tools::getValue('franchise_cfg')),
			'{razon_social}' => Tools::getValue('company_name'),
			'{nombre}' => Tools::getValue('name'),
			'{apellidos}' => Tools::getValue('first_name'),
			'{tipo_via}' => Tools::getValue('street_type'),
			'{nombre_via}' => Tools::getValue('street_name'),
			'{numero_via}' => Tools::getValue('street_number'),
			'{escalera}' => Tools::getValue('staircase'),
			'{piso}' => Tools::getValue('floor'),
			'{puerta}' => Tools::getValue('door'),
			'{poblacion}' => (Tools::getValue('town_cfg')),
			'{provincia}' => (Tools::getValue('state_cfg')),
			'{cp}' => (Tools::getValue('post_code_cfg')),
			'{pais}' => (Tools::getValue('country_cfg')),
			'{telefono}' => Tools::getValue('phone'),
			'{fax}' => Tools::getValue('fax'),
			'{COD_CLIENTE_CIT}' => $cit,
			'{USUARIO_CIT}' =>$user,
			'{email}' => Tools::getValue('email')
		);

		$emailSubject = 'Alta Prestashop;FRQ:'.(Tools::getValue('franchise_cfg')).';NIF:'.$nif_dni.';CI:'.$cit.';CCC:'.(Tools::getValue('ccc_cfg')).'';
		$emailTemplate = _PS_MODULE_DIR_.'/seur/mails/';
		$email = 'operaciones.ggcc@seur.net';
		$id_email_language = self::getIdEmailLanguage();

		if ($id_email_language && !Mail::Send((int)$id_email_language, 'seur',
			$emailSubject, $emailData, $email, null, null, null, null, null, $emailTemplate))
		{
			$module_instance = Module::getInstanceByName('seur');
			Context::getContext()->smarty->assign(array(
				'email_warning_message' => $module_instance->l('Email could not be sent', self::FILENAME),
				'module_instance' => $module_instance
			));
		}

		return 1;
	}

	public static function getIdEmailLanguage()
	{
		$available_iso_codes = self::getAvailableEmailLanguagesIsoCodes();
		$available_language_ids = self::getLanguagesIdsByIsoCodes($available_iso_codes);
		$id_admin_language = (int)Context::getContext()->language->id;
		$id_default_language = (int)Configuration::get('PS_LANG_DEFAULT');
		$id_email_language = 0;

		if (in_array($id_admin_language, $available_language_ids))
			$id_email_language = (int)$id_admin_language;
		elseif (in_array($id_default_language, $available_language_ids))
			$id_email_language = (int)$id_default_language;

		return $id_email_language;
	}

	private static function getAvailableEmailLanguagesIsoCodes()
	{
		$path = _PS_MODULE_DIR_.'seur/mails';
		$folders = scandir($path);
		$iso_codes = array();

		foreach ($folders as $folder)
		{
			if ($folder === '.' || $folder === '..')
				continue;

			if (is_dir($path.'/'.$folder))
				$iso_codes[] = $folder;
		}

		return $iso_codes;
	}

	private static function getLanguagesIdsByIsoCodes(array $available_iso_codes)
	{
		$language_ids = array();

		if (empty($available_iso_codes))
			return array();

		foreach ($available_iso_codes as $iso_code)
			if ($language_id = Language::getIdByIso($iso_code))
				$language_ids[] = (int)$language_id;

		return $language_ids;
	}
}
