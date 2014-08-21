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

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');

if (class_exists('SeurLib') == false)
	include_once(_PS_MODULE_DIR_.'seur/classes/SeurLib.php');

$token = Tools::getValue('token');
$admin_token = Tools::getAdminToken('AdminOrders'.(int)Tab::getIdFromClassName('AdminOrders').(int)Tools::getValue('id_employee'));

if ($token != $admin_token)
	exit;

try
{
	$servicio = 31;
	$producto = 2;
	$serviciosComplementarios = '';

	$iso = Tools::getValue('iso');
	$iso_list = array('ES', 'PT', 'AD');

	if (Tools::isSubmit('reembolso') && (in_array($iso, $iso_list) == true))
		$serviciosComplementarios = '30;P;'.Tools::getValue('reembolso');

	if (Tools::isSubmit('cod_centro') && (in_array($iso, $iso_list) == true))
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
		<NOM_POBLA_DEST>'.pSQL(Tools::getValue('town')).'</NOM_POBLA_DEST>
		<Peso>'.pSQL(Tools::getValue('peso')).'</Peso>
		<CODIGO_POSTAL_DEST>'.pSQL(Tools::getValue('post_code')).'</CODIGO_POSTAL_DEST>
		<CodContableRemitente>'.pSQL(Tools::getValue('ccc')).'-'.pSQL(Tools::getValue('franchise')).'</CodContableRemitente>
		<PesoVolumen>'.pSQL(Tools::getValue('peso')).'</PesoVolumen>
		<Bultos>'.pSQL(Tools::getValue('bultos')).'</Bultos>
		<CodServ>'.pSQL($servicio).'</CodServ>
		<CodProd>'.(int)$producto.'</CodProd>
		<TipoEnvioAduanero></TipoEnvioAduanero>
		<ValDeclarado></ValDeclarado>
		<TpDespAduanaEntrada></TpDespAduanaEntrada>
		<TpDespAduanaSalida></TpDespAduanaSalida>
		<dateVigenciaTasacion>20120615</dateVigenciaTasacion>
		<SERVICIOS_COMPLEMENTARIOS>'.pSQL($serviciosComplementarios).'</SERVICIOS_COMPLEMENTARIOS>
		<COD_IDIOMA>'.pSQL(Tools::getValue('iso_merchant')).'</COD_IDIOMA>
	</REG>';

	$data = array('in0' => $plano);
	$response = $soap_client->tarificacionPrivadaStr($data);

	if (empty($response->out) || (isset($response->error) && !empty($response->error)))
		return false;
	else
	{
		$delivery = array();
		$total = 0;

		foreach (simplexml_load_string($response->out) as $key => $price)
		{
			if ((string)$price->NOM_CONCEPTO_IMP != 'IVA')
				$delivery[] = array('concepto' => utf8_decode( (string)$price->NOM_CONCEPTO_IMP ), 'importe' => (string)$price->VALOR);
			else
			{
				$iva = array(
					'concepto' => (string)$price->NOM_CONCEPTO_IMP,
					'importe' => (string)$price->VALOR
				);
			}
			$total = $total + (float)$price->VALOR;
		}

		$delivery[] = $iva;
		$delivery[] = array(
			'concepto' => 'Total',
			'importe' => (string)$total
		);

		echo Tools::jsonEncode($delivery);
	}
}
catch (SoapFault $fault)
{
	$url = urlencode(Tools::getValue('back')).'&token='.urlencode(Tools::getValue('token')).'&codigo=Error&error='.urlencode($fault->getMessage());
	die(Tools::redirect($url));
}
