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

class Label
{
	public static function createLabels($label_data, $tipo)
	{
		try
		{
			if (Validate::isFileName($label_data['pedido']))
				$label_name = $label_data['pedido'];
			else
			{
				$module_instance = Module::getInstanceByName('seur');
				return SeurLib::displayErrors($label_data['pedido'].' '.$module_instance->l('could not be used as file name', 'Label'));
			}

			$sc_options = array(
				'connection_timeout' => 30
			);

			$soap_client = new SoapClient((string)Configuration::get('SEUR_URLWS_ET'), $sc_options);

			$merchant_data = SeurLib::getMerchantData();
			$notification = SeurLib::getConfigurationField('notification_advice_radio');
			$advice_checkbox = SeurLib::getConfigurationField('advice_checkbox');
			$distribution_checkbox = SeurLib::getConfigurationField('distribution_checkbox');
			$servicio = 31;
			$producto = 2;
			$mercancia = false;
			$claveReembolso = '';
			$valorReembolso = '';

			if (SeurLib::getConfigurationField('international_orders') == 1 && ($label_data['iso'] != 'ES' &&
				$label_data['iso'] != 'PT' && $label_data['iso'] != 'AD'))
			{
				$servicio = 77;
				$producto = 70;
				$mercancia = true;
				$label_data['total_bultos'] = 1;
			}
			if (isset($label_data['reembolso']) && ($label_data['iso'] == 'ES' || $label_data['iso'] == 'PT' || $label_data['iso'] == 'AD'))
			{
				$claveReembolso = 'f';
				$valorReembolso = (float)$label_data['reembolso'];
			}
			if (isset($label_data['cod_centro']) && ($label_data['iso'] == 'ES' || $label_data['iso'] == 'PT' || $label_data['iso'] == 'AD'))
			{
				$servicio = 1;
				$producto = 48;
			}

			$total_weight = $label_data['total_kilos'];
			$total_packages = $label_data['total_bultos'];
			$pesoBulto = $total_weight / $total_packages;

			if ($pesoBulto < 1)//1kg
			{
				$pesoBulto = 1;
				$total_weight = $total_packages;
			}

			$cont = 0;
			$xml = '<?xml version="1.0" encoding="ISO-8859-1"?><root><exp>';

			for ($i = 0; $i <= (float)$total_packages - 1; $i++)
			{
				$cont++;

				$xml .= '<bulto>
							<ci>'.(int)$merchant_data['cit'].'</ci>
							<nif>'.pSQL($merchant_data['nif_dni']).'</nif>
							<ccc>'.(int)$merchant_data['ccc'].'</ccc>
							<servicio>'.pSQL($servicio).'</servicio>
							<producto>'.pSQL($producto).'</producto>';

				if ($mercancia)
					$xml .= '<id_mercancia>382</id_mercancia>';

				$xml .= '<cod_centro></cod_centro>
							<total_bultos>'.pSQL($total_packages).'</total_bultos>
							<total_kilos>'.pSQL($total_weight).'</total_kilos>
							<pesoBulto>'.pSQL($pesoBulto).'</pesoBulto>
							<observaciones>'.pSQL($label_data['info_adicional']).'</observaciones>
							<referencia_expedicion>'.pSQL($label_data['pedido']).'</referencia_expedicion>
							<ref_bulto>'.pSQL($label_data['pedido'].sprintf('%03d', (int)$i + 1)).'</ref_bulto>
							<clavePortes>F</clavePortes>
							<clavePod></clavePod>
							<claveReembolso>'.pSQL($claveReembolso).'</claveReembolso>
							<valorReembolso>'.pSQL($valorReembolso).'</valorReembolso>
							<libroControl></libroControl>
							<nombre_consignatario>'.pSQL($label_data['name']).'</nombre_consignatario>
							<direccion_consignatario>'.pSQL($label_data['direccion_consignatario']).'</direccion_consignatario>
							<tipoVia_consignatario>CL</tipoVia_consignatario>
							<tNumVia_consignatario>N</tNumVia_consignatario>
							<numVia_consignatario>.</numVia_consignatario>
							<escalera_consignatario>.</escalera_consignatario>
							<piso_consignatario>.</piso_consignatario>
							<puerta_consignatario>.</puerta_consignatario>
							<poblacion_consignatario>'.pSQL($label_data['consignee_town']).'</poblacion_consignatario>';

				if (!empty($label_data['codPostal_consignatario']))
					$xml .= '<codPostal_consignatario>'.pSQL($label_data['codPostal_consignatario']).'</codPostal_consignatario>';

				$xml .= '   <pais_consignatario>'.pSQL($label_data['iso']).'</pais_consignatario>
							<codigo_pais_origen>'.pSQL($label_data['iso_merchant']).'</codigo_pais_origen>
							<email_consignatario>'.pSQL($label_data['email_consignatario']).'</email_consignatario>
							<sms_consignatario>'.((int)$notification ? pSQL($label_data['movil']) : '' ).'</sms_consignatario>
							<test_sms>'.((int)$notification ? 'S' : 'N').'</test_sms>
							<test_preaviso>'.((int)$advice_checkbox ? 'S' : 'N').'</test_preaviso>
							<test_reparto>'.((int)$distribution_checkbox ? 'S' : 'N').'</test_reparto>
							<test_email>'.((int)$notification ? 'N' : 'S').'</test_email>
							<eci>N</eci>
							<et>N</et>
							<telefono_consignatario>'.pSQL($label_data['telefono_consignatario']).'</telefono_consignatario>
							<atencion_de>'.pSQL($label_data['companyia']).'</atencion_de>
						 </bulto>
						 ';
			}

			$xml .= '</exp></root>';

			$xml_name = (int)$merchant_data['franchise'].'_'.(int)$merchant_data['cit'].'_'.date('dmYHi').'.xml';

			$make_pickup = false;
			$auto = false;
			$pickup_data = Pickup::getLastPickup();

			if (!empty($pickup_data))
			{
				$datepickup = explode(' ', $pickup_data['date']);
				$datepickup = $datepickup[0];

				if (strtotime( date('Y-m-d')) != strtotime($datepickup))
					$make_pickup = true;

				if (SeurLib::getConfigurationField('pickup') == 0)
					$auto = true;
			}

			if ($tipo == 'pdf')
			{
				$data = array(
					'in0' => $merchant_data['user'],
					'in1' => $merchant_data['pass'],
					'in2' => $xml,
					'in3' => $xml_name,
					'in4' => $merchant_data['nif_dni'],
					'in5' => $merchant_data['franchise'],
					'in6' => '-1',
					'in7' => 'prestashop',
				);
				$response = $soap_client->impresionIntegracionPDFConECBWS($data);

				if ($response->out == 'ERROR')
					return SeurLib::displayErrors ((string)$response->out);

				if ($response->out->mensaje != 'OK')
					return SeurLib::displayErrors ((string)$response->out->mensaje);
				else
				{
					$pdf = base64_decode($response->out->PDF);

					if (is_writable(_PS_MODULE_DIR_.'seur/files/deliveries_labels/'))
						file_put_contents(_PS_MODULE_DIR_.'seur/files/deliveries_labels/'.$label_name.'.pdf', $pdf);

					SeurLib::setSeurOrder($label_data['pedido'], $total_packages, $total_weight, 'PDF');

					if ($make_pickup && $auto)
						Pickup::createPickup();
				}
			}
			elseif ($tipo == 'zebra')
			{
				$data = array(
					'in0' => pSQL($merchant_data['user']),
					'in1' => pSQL($merchant_data['pass']),
					'in2' => 'ZEBRA',
					'in3' => 'LP2844-Z',
					'in4' => '2C',
					'in5' => $xml,
					'in6' => $xml_name,
					'in7' => pSQL($merchant_data['nif_dni']),
					'in8' => pSQL($merchant_data['franchise']),
					'in9' => '-1',
					'in10' => 'prestashop',
				);

				$response = $soap_client->impresionIntegracionConECBWS($data);

				if ($response->out == 'ERROR' || $response->out->mensaje != 'OK')
					return SeurLib::displayErrors('Error al crear el envio y la etiqueta: '.$response->out->mensaje); // @TODO check if must be translatable
				else
				{
					if (is_writable(_PS_MODULE_DIR_.'seur/files/deliveries_labels/'))
						file_put_contents(_PS_MODULE_DIR_.'seur/files/deliveries_labels/'.pSQL($label_name).'.txt', (string)$response->out->traza);

					SeurLib::setSeurOrder(pSQL($label_data['pedido']), (float)$total_packages, (float)$total_weight, 'zebra');

					if ($make_pickup && $auto)
						Pickup::createPickup();
				}
			}
		}
		catch (PrestaShopException $e)
		{
			$e->displayMessage();
		}

		return true;
	}
}