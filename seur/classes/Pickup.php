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

class Pickup extends ObjectModel
{
	public static function createPickup()
	{
		if ((int)date('H') < '14')
		{
			try
			{
				$sc_options = array(
					'connection_timeout' => 30
				);

				$soap_client = new SoapClient((string)Configuration::get('SEUR_URLWS_R'), $sc_options);

				$merchant_data = SeurLib::getMerchantData();

				if (!isset($merchant_data['street_number']))
					return false;

				$numeroVia = filter_var($merchant_data['street_number'], FILTER_SANITIZE_NUMBER_INT);

				$plano = '
					<recogida>
						<usuario>'.Configuration::get('SEUR_WS_USERNAME').'</usuario>
						<password>'.Configuration::get('SEUR_WS_PASSWORD').'</password>
						<razonSocial>'.pSQL($merchant_data['company_name']).'</razonSocial>
						<nombreEmpresa>'.pSQL($merchant_data['company_name']).'</nombreEmpresa>
						<nombreContactoOrdenante>'.pSQL($merchant_data['name']).'</nombreContactoOrdenante>
						<apellidosContactoOrdenante>'.pSQL($merchant_data['first_name']).'</apellidosContactoOrdenante>
						<prefijoTelefonoOrdenante>34</prefijoTelefonoOrdenante>
						<telefonoOrdenante>'.pSQL($merchant_data['phone']).'</telefonoOrdenante>
						<prefijoFaxOrdenante />
						<faxOrdenante />
						<nifOrdenante>'.pSQL($merchant_data['nif_dni']).'</nifOrdenante>
						<paisNifOrdenante>ES</paisNifOrdenante>
						<mailOrdenante>'.pSQL($merchant_data['email']).'</mailOrdenante>
						<tipoViaOrdenante>'.pSQL($merchant_data['street_type']).'</tipoViaOrdenante>
						<calleOrdenante>'.pSQL($merchant_data['street_name']).'</calleOrdenante>
						<tipoNumeroOrdenante>N.</tipoNumeroOrdenante>
						<numeroOrdenante>'.pSQL($numeroVia).'</numeroOrdenante>
						<escaleraOrdenante />
						<pisoOrdenante />
						<puertaOrdenante />
						<codigoPostalOrdenante>'.pSQL($merchant_data['post_code']).'</codigoPostalOrdenante>
						<poblacionOrdenante>'.pSQL($merchant_data['town']).'</poblacionOrdenante>
						<provinciaOrdenante>'.pSQL($merchant_data['state']).'</provinciaOrdenante>
						<paisOrdenante>'.pSQL($merchant_data['country']).'</paisOrdenante>

						<diaRecogida>'.pSQL(sprintf( '%02d', date('d') )).'</diaRecogida>
						<mesRecogida>'.date('m').'</mesRecogida>
						<anioRecogida>'.date('Y').'</anioRecogida>
						<servicio>1</servicio>
						<horaMananaDe></horaMananaDe>
						<horaMananaA></horaMananaA>
						<numeroBultos>1</numeroBultos>
						<mercancia>2</mercancia>
						<horaTardeDe>16:00</horaTardeDe>
						<horaTardeA>20:00</horaTardeA>
						<tipoPorte>P</tipoPorte>
						<observaciones></observaciones>
						<tipoAviso>EMAIL</tipoAviso>
						<idiomaContactoOrdenante>'.pSQL($merchant_data['country']).'</idiomaContactoOrdenante>

						<razonSocialDestino>'.pSQL($merchant_data['company_name']).'</razonSocialDestino>
						<nombreContactoDestino>'.pSQL($merchant_data['name']).'</nombreContactoDestino>
						<apellidosContactoDestino>'.pSQL($merchant_data['first_name']).'</apellidosContactoDestino>
						<telefonoDestino>'.pSQL($merchant_data['phone']).'</telefonoDestino>
						<tipoViaDestino>'.pSQL($merchant_data['street_type']).'</tipoViaDestino>
						<calleDestino>'.pSQL($merchant_data['street_name']).'</calleDestino>
						<tipoNumeroDestino>N.</tipoNumeroDestino>
						<numeroDestino>'.pSQL($numeroVia).'</numeroDestino>
						<escaleraDestino />
						<pisoDestino />
						<puertaDestino />
						<codigoPostalDestino>'.pSQL($merchant_data['post_code']).'</codigoPostalDestino>
						<poblacionDestino>'.pSQL($merchant_data['town']).'</poblacionDestino>
						<provinciaDestino>'.pSQL($merchant_data['state']).'</provinciaDestino>
						<paisDestino>'.pSQL($merchant_data['country']).'</paisDestino>
						<prefijoTelefonoDestino>34</prefijoTelefonoDestino>

						<razonSocialOrigen>'.pSQL($merchant_data['company_name']).'</razonSocialOrigen>
						<nombreContactoOrigen>'.pSQL($merchant_data['name']).'</nombreContactoOrigen>
						<apellidosContactoOrigen>'.pSQL($merchant_data['first_name']).'</apellidosContactoOrigen>
						<telefonoRecogidaOrigen>'.pSQL($merchant_data['phone']).'</telefonoRecogidaOrigen>
						<tipoViaOrigen>'.pSQL($merchant_data['street_type']).'</tipoViaOrigen>
						<calleOrigen>'.pSQL($merchant_data['street_name']).'</calleOrigen>
						<tipoNumeroOrigen>N.</tipoNumeroOrigen>
						<numeroOrigen>'.pSQL($numeroVia).'</numeroOrigen>
						<escaleraOrigen />
						<pisoOrigen />
						<puertaOrigen />
						<codigoPostalOrigen>'.pSQL($merchant_data['post_code']).'</codigoPostalOrigen>
						<poblacionOrigen>'.pSQL($merchant_data['town']).'</poblacionOrigen>
						<provinciaOrigen>'.pSQL($merchant_data['state']).'</provinciaOrigen>
						<paisOrigen>'.pSQL($merchant_data['country']).'</paisOrigen>
						<prefijoTelefonoOrigen>34</prefijoTelefonoOrigen>

						<producto>2</producto>
						<entregaSabado>N</entregaSabado>
						<entregaNave>N</entregaNave>
						<tipoEnvio>N</tipoEnvio>
						<valorDeclarado>0</valorDeclarado>
						<listaBultos>1;1;1;1;1/</listaBultos>
						<cccOrdenante>'.pSQL($merchant_data['ccc']).'-'.pSQL($merchant_data['franchise']).'</cccOrdenante>
						<numeroReferencia></numeroReferencia>
						<ultimaRecogidaDia />
						<nifOrigen></nifOrigen>
						<paisNifOrigen></paisNifOrigen>
						<aviso>N</aviso>
						<cccDonde />
						<cccAdonde></cccAdonde>
						<tipoRecogida></tipoRecogida>
					 </recogida>
			   ';

				$data = array(
					'in0' => utf8_encode($plano)
				);

				$response = $soap_client->crearRecogida($data);
				$string_xml = htmlspecialchars_decode((($response->out)));
				$xml = simplexml_load_string($string_xml);

				if (!empty($xml->DESCRIPCION))
					return (string)$xml->DESCRIPCION;
				elseif (!self::insertPickup((int)$xml->LOCALIZADOR, (string)$xml->NUM_RECOGIDA, (float)$xml->TASACION))
					return 'Error en base de datos.'; // @TODO check if must be translatable

				Configuration::updateValue('SEUR_CONFIGURATION_OK', true);
			}
			catch (PrestaShopException $e)
			{
				$e->displayMessage();
			}
		}
		else
		{
			$module_instance = Module::getInstanceByName('seur');
			$module_instance->adminDisplayWarning($module_instance->l('Pickups after 2pm cannot be arranged via module, contact us by phone to arrange it manually.'));
		}
	}

	private static function insertPickup($localizer, $numPickup, $tasacion)
	{
		return Db::getInstance()->Execute('
			INSERT INTO `'._DB_PREFIX_.'seur_pickup`
				(`id_seur_pickup`, `localizer`, `num_pickup`, `tasacion`, `date`)
			VALUES
				(NULL, "'.pSQL($localizer).'", "'.pSQL($numPickup).'", "'.(float)$tasacion.'", NULL)
		');
	}

	public static function getLastPickup()
	{
		$pickup_data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM `'._DB_PREFIX_.'seur_pickup`
			ORDER BY `id_seur_pickup` DESC
		');

		return ($pickup_data ? $pickup_data : '');
	}
}
