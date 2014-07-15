{*
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
 * @author     boxdrop Group AG
 * @copyright  boxdrop Group AG
 * @license    http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of boxdrop Group AG
 *}

 <div class="bshp-bordered bshp-full-width bshp-padded-box">
  <strong>{l s='Congratulations, you have selected to drop off your order using boxdropDropOff!' mod='boxdropshipment'}</strong><br /><br />

  <strong>{$shop->company|escape:'htmlall':'UTF-8'}</strong><br />
  {$shop->street|escape:'htmlall':'UTF-8'}<br />
  {$shop->zip|escape:'htmlall':'UTF-8'} {$shop->city|escape:'htmlall':'UTF-8'}<br />
  {l s='Telephone' mod='boxdropshipment'}: {$shop->gmap_telephone|escape:'htmlall':'UTF-8'}<br /><br />
  {l s='Openings' mod='boxdropshipment'}: <br />
  {$shop->openings|escape:'htmlall':'UTF-8'}

  <br /><br />
  <a href="javascript:;" class="bshp-change-dropoff-point">{l s='Change point' mod='boxdropshipment'}</a> -
  <a href="javascript:;" class="bshp-change-carrier">{l s='Change carrier' mod='boxdropshipment'}</a>
</div>