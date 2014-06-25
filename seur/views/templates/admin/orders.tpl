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
{if $configured == 1}
    <script>
        var rate_data_ajax = {$rate_data_ajax}; {* $rate_data_ajax - type of JSon *}
    </script>
    <br />
    <fieldset id="detalleseur">
        <legend>
            <img src="{$path|escape:'htmlall':'UTF-8'}img/logonew_32.png" alt="{l s='SEUR' mod='seur'}" title="{l s='SEUR' mod='seur'}" />
        </legend>

        {if isset($pickup_point_warning)}
            <p class="alertaconfiguracion">
                {l s='PickUp point address could not be saved as order delivery address in PrestaShop system' mod='seur'}
            </p>
        {/if}

        <form id="fichaPedidoSeur" method="POST" action="{$request_uri|escape:'htmlall':'UTF-8'}">
            <input type="hidden" name="module_dir" id="module_dir" value="{$path|escape:'htmlall':'UTF-8'}" />
            <input type="hidden" name="module_non_ssl_href" id="module_non_ssl_href" value="{$module_instance->name|escape:'htmlall':'UTF-8'}"/>
        {if isset($error)}
            {include file=$smarty.const._PS_MODULE_DIR_|escape:'htmlall':'UTF-8'|cat:'seur/views/templates/admin/error_message.tpl' seur_error_message=$error|escape:'htmlall':'UTF-8'}
        {/if}
        {if $address_error == 1}
            {include file=$smarty.const._PS_MODULE_DIR_|escape:'htmlall':'UTF-8'|cat:'seur/views/templates/admin/error_message.tpl' seur_error_message=$address_error_message|escape:'htmlall':'UTF-8'}
        {/if}
        <p>{l s='Pickup localizator' mod='seur'}:
            {if $pickup_s == 1}
                {$pickup.localizer|escape:'htmlall':'UTF-8'}
            {else}
                {l s='Pending' mod='seur'}
            {/if}
        </p>
        {if $isEmptyState}
            <p>{l s='Expedition Number' mod='seur'}: {l s='Pending' mod='seur'}</p>
            <p>{l s='Status' mod='seur'}: {l s='Pending' mod='seur'}</p>
        {else}
            <p>{l s='Expedition Number' mod='seur'}: {$xml_s.EXPEDICION.EXPEDICION_NUM|escape:'htmlall':'UTF-8'}</p>
            <p>{l s='Status' mod='seur'}: {$xml_s.EXPEDICION.DESCRIPCION_PARA_CLIENTE|escape:'htmlall':'UTF-8'}</p>
        {/if}
        {if $iso_country == 'ES' || $iso_country == 'PT' || $iso_country == 'AD'}
            {if $order_data.imprimido == NULL}
                <p id="nBultos"><label>{l s='Number of packages:' mod='seur'}</label>
                    {if $printed}{$order_data.numero_bultos|escape:'htmlall':'UTF-8'}{else}<input type="text" name="numBultos" value="{$order_data.numero_bultos|escape:'htmlall':'UTF-8'}" />{/if}
                </p>
                <p id="pBultos">
                    <label>{l s='Weigth:' mod='seur'}</label>
                    {if $printed}{$order_weigth|escape:'htmlall':'UTF-8'}{else}<input type="text" name="pesoBultos" value="{$order_weigth|escape:'htmlall':'UTF-8'}" />{/if} {l s='Kg.' mod='seur'}
                </p>
                <br />
                {if !$printed}
                    <input class="buttonguardar" type="submit" name="submitBultos" value="{l s='Save' mod='seur'}" />
                {/if}
            {/if}
            <br />
            <p class="tarifa">{l s='Delivery price: ' mod='seur'}{Tools::displayPrice((float)$delivery_price)|escape:'htmlall':'UTF-8'}
                <img id="btnDesgloseCosteEnvio" src="{$path|escape:'htmlall':'UTF-8'}img/unknown.png" alt="{l s='Distribution of the cost' mod='seur'}" title="{l s='Distribution of the cost' mod='seur'}" />
            </p>
            <div id="loaderTarifa"></div>
            <div id="desgloseCosteEnvio">
            <table>
                <caption>
                    {l s='Distribution of the cost' mod='seur'}
                    <img src="{$path|escape:'htmlall':'UTF-8'}img/close.png" alt="close" title="close" />
                </caption>
                <thead>
                <tr>
                    <th>{l s='Concept' mod='seur'}</th>
                    <th>{l s='Price' mod='seur'}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="2" class="center">
                    </td>
                </tr>
                </tbody>
            </table>
            </div>
        {else}
            <p><img src="{$path|escape:'htmlall':'UTF-8'}img/unknown.gif" alt="close" title="close" /> {l s='The number of packages in an international shipping is always one.' mod='seur'}</p>
            <p id="bultos">
                <input type="hidden" name="numBultos" value="1" />
            </p>
        {/if}
        {if $order_data.imprimido == NULL}
            {if SeurLib::getConfigurationField('print_type') == 1}
                <a class="buttongenerar" href="{Tools::safeOutput($request_uri)}&id_order={$order->id|escape:'htmlall':'UTF-8'}&vieworder&token={$token|escape:'htmlall':'UTF-8'}&submitLabel=1" onClick="setTimeout('location.reload()',5000);" target="_blank">{l s='Generate PDF' mod='seur'}</a>
            {elseif SeurLib::getConfigurationField('print_type') == 0}
                <a class="buttongenerar" href="{Tools::safeOutput($request_uri)|escape:'htmlall':'UTF-8'}&id_order={$order->id|escape:'htmlall':'UTF-8'}&vieworder&token={$token|escape:'htmlall':'UTF-8'}&submitPrint=1" target="_blank">{l s='Print Label' mod='seur'}</a>
            {/if}
        {else}
            <p>{l s='This order have ' mod='seur'}{$order_data.numero_bultos|escape:'htmlall':'UTF-8'}{if $order_data.numero_bultos == 1}{l s=' package' mod='seur'}{else}{l s=' packages' mod='seur'}{l s=' in format of: ' mod='seur'}{$order_data.imprimido|escape:'htmlall':'UTF-8'}{/if}</p>
            {if $order_data.imprimido == 'PDF'}
                <a class="buttongenerar" href="{Tools::safeOutput($request_uri)|escape:'htmlall':'UTF-8'}&id_order={$order->id|escape:'htmlall':'UTF-8'}&vieworder&token={$token|escape:'htmlall':'UTF-8'}&submitLabel=1" target="_blank">{l s='Generate PDF' mod='seur'}</a>
            {elseif $order_data.imprimido == 'zebra'}
                <a class="buttongenerar" href="{Tools::safeOutput($request_uri)|escape:'htmlall':'UTF-8'}&id_order={$order->id|escape:'htmlall':'UTF-8'}&vieworder&token={$token|escape:'htmlall':'UTF-8'}&submitPrint=1" target="_blank">{l s='Print Label' mod='seur'}</a>
            {/if}
        {/if}
        {if ($datospos ne '') && ((int) $order->id_carrier == $carrier_pos.id)}
        <br /><br />
        <div class="pudos ">
            <img src="{$path|escape:'htmlall':'UTF-8'}img/puntoRecogidaOrder.png" alt="pos" title="pos" />
            <strong>{l s='Sending data to point of sale:' mod='seur'}</strong>
            <table class="{$versionSpecialClass|escape:'htmlall':'UTF-8'}">
                <thead>
                <tr>
                    <th>{l s='Company' mod='seur'}</th>
                    <th>{l s='Address' mod='seur'}</th>
                    <th>{l s='City' mod='seur'}</th>
                    <th>{l s='Postal Code' mod='seur'}</th>
                    <th>{l s='Opening times' mod='seur'}</th>
                    <th>{l s='Phone' mod='seur'}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="center">{$datospos.company|escape:'htmlall':'UTF-8'}</td>
                    <td class="center">{$datospos.address|escape:'htmlall':'UTF-8'}</td>
                    <td class="center">{$datospos.city|escape:'htmlall':'UTF-8'}</td>
                    <td class="center">{$datospos.postal_code|escape:'htmlall':'UTF-8'}</td>
                    <td class="center">{$datospos.timetable|escape:'htmlall':'UTF-8'}</td>
                    <td class="center">{$datospos.phone|escape:'htmlall':'UTF-8'}</td>
                </tr>
                </tbody>
            </table>
        </div>
        {/if}
        <br />
        </form>
    </fieldset>
{else}
    <br />
    <fieldset id="detalleseur">
        <legend><img src="{$path|escape:'htmlall':'UTF-8'}img/logonew_32.png" alt="{l s='SEUR' mod='seur'}" title="{l s='SEUR' mod='seur'}" border="0" /></legend>
        {include file=$smarty.const._PS_MODULE_DIR_|escape:'htmlall':'UTF-8'|cat:'seur/views/templates/admin/warning_message.tpl' seur_warning_message=$configuration_warning_message|escape:'htmlall':'UTF-8'}
    </fieldset>
{/if}