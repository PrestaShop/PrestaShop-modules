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

class Rate
{
	public static function getPrivateRate($order_data)
	{
		try
		{
			$servicio = 31;
			$producto = 2;
			$serviciosComplementarios = '';

			if (isset($order_data['reembolso']) && ($order_data['iso'] == 'ES' || $order_data['iso'] == 'PT' || $order_data['iso'] == 'AD'))
				$serviciosComplementarios = '30;P;'.$order_data['reembolso'];

			if (isset($order_data['cod_centro']) && ($order_data['iso'] == 'ES' || $order_data['iso'] == 'PT' || $order_data['iso'] == 'AD'))
			{
				$servicio = 1;
				$producto = 48;
			}

			$sc_options = array(
				'connection_timeout' => 30
			);

			$soap_client = new SoapClient((string)Configuration::get('SEUR_URLWS_SP'), $sc_options);

			$plano = '<REG>
				<USUARIO>'.Configuration::get('SEUR_WS_USERNAME').'</USUARIO>
				<PASSWORD>'.Configuration::get('SEUR_WS_PASSWORD').'</PASSWORD>
				<NOM_POBLA_DEST>'.pSQL($order_data['town']).'</NOM_POBLA_DEST>
				<Peso>'.pSQL($order_data['peso']).'</Peso>
				<CODIGO_POSTAL_DEST>'.pSQL($order_data['post_code']).'</CODIGO_POSTAL_DEST>
				<CodContableRemitente>'.pSQL($order_data['ccc']).'-'.pSQL($order_data['franchise']).'</CodContableRemitente>
				<PesoVolumen>'.pSQL($order_data['peso']).'</PesoVolumen>
				<Bultos>'.pSQL($order_data['bultos']).'</Bultos>
				<CodServ>'.pSQL($servicio).'</CodServ>
				<CodProd>'.pSQL($producto).'</CodProd>
				<TipoEnvioAduanero></TipoEnvioAduanero>
				<ValDeclarado></ValDeclarado>
				<TpDespAduanaEntrada></TpDespAduanaEntrada>
				<TpDespAduanaSalida></TpDespAduanaSalida>
				<dateVigenciaTasacion>20120615</dateVigenciaTasacion>
				<SERVICIOS_COMPLEMENTARIOS>'.pSQL($serviciosComplementarios).'</SERVICIOS_COMPLEMENTARIOS>
				<COD_IDIOMA>'.pSQL($order_data['iso_merchant']).'</COD_IDIOMA>
			</REG>';

			$data = array('in0' => $plano);
			$response = $soap_client->tarificacionPrivadaStr($data);

			if (empty($response->out) || (isset($response->error) && !empty($response->error)))
				return false;
			else
			{
				$string_xml = htmlspecialchars_decode($response->out);
				$xml = simplexml_load_string($string_xml);

				return $xml;
			}
		}
		catch (PrestaShopException $e)
		{
			$e->displayMessage();
		}
	}
}