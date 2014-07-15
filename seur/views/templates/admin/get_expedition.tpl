{*
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
 *}
{*@TODO in some labels text is not translatable*}
<div id="seur_module" class="consultaExpedicion">
    <h3>{$delivery.DESCRIPCION_PARA_CLIENTE|escape:'htmlall':'UTF-8'}</h3>
    <ul class="cols2">
        <li>
            <label>Refer&eacute;ncia remitente:</label>
            {$delivery.REMITE_REF|escape:'htmlall':'UTF-8'}
        </li>
        <li class="clear"></li>
        <li>
            <label>N. Expedici&oacute;n:</label>
            {$delivery.EXPEDICION_NUM|escape:'htmlall':'UTF-8'}
        </li>
        <li>
            <label>Remitente:</label>
            {$delivery.REMITE_CCC_COD|escape:'htmlall':'UTF-8'}
        </li>
        <li class="clear"></li>
        <li>
            <label>Nombre destino:</label>
            {$delivery.DESTINA_NOMBRE|escape:'htmlall':'UTF-8'}
        </li>
        <li class="clear"></li>
        <li>
            <label>Direcci&oacute;n destino:</label>
            {$delivery.DESTINA_VIA_TIPO|escape:'htmlall':'UTF-8'} {$delivery.DESTINA_VIA_NOMBRE|escape:'htmlall':'UTF-8'} {$delivery.DESTINA_NUM|escape:'htmlall':'UTF-8'}
        </li>
        <li class="clear"></li>
        <li>
            <label>Poblaci&oacute;n destino:</label>
            {$delivery.DESTINA_POBLACION|escape:'htmlall':'UTF-8'}
        </li>
        <li>
            <label>Pa&iacute;s:</label>
            {$delivery.DESTINA_PAIS|escape:'htmlall':'UTF-8'}
        </li>
        <li class="clear"></li>
        <li>
            <label>Fecha de situaci&oacute;n:</label>
            {$delivery.FECHA_CAPTURA|escape:'htmlall':'UTF-8'}
        </li>
        <li class="clear"></li>
        <li>
            <label>Situaci&oacute;n:</label>
            {$delivery.COD_SITUACION|escape:'htmlall':'UTF-8'} {$delivery.DESCRIPCION_PARA_CLIENTE|escape:'htmlall':'UTF-8'}
        </li>
    </ul>
</div>