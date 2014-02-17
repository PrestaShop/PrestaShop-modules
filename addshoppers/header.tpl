{*
* 2007-2014 PrestaShop
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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<meta property="og:title" content="{$meta_title|escape:'htmlall':'UTF-8'}" />
{if $is_product_page}
<meta property="og:type" content="product" />
{if isset($image_url)}
<meta property="og:image" content="{$image_url}" />
{/if}
{else}
<meta property="og:type" content="website" />
<meta property="og:image" content="{$logo_url}" />
{/if}
<meta property="og:site_name" content="{$shop_name|escape:'htmlall':'UTF-8'}" />
<meta property="og:description" content="{$meta_description|escape:html:'UTF-8'}" />

{literal}
<!-- AddShoppers.com Sharing Script -->
<script type="text/javascript">
// <![CDATA[

  var AddShoppersTracking = {
  {/literal}{if $is_product_page}{literal}
      name:        '{/literal}{$product_name|unescape:'htmlall'|escape:'quotes':'UTF-8'}{literal}',
      description: '{/literal}{$product_description|unescape:'htmlall'|escape:'quotes':'UTF-8'|replace:"\r\n":''|replace:"\n":''}{literal}',
      image:       '{/literal}{if isset($image_url)}{$image_url}{else}{$logo_url}{/if}{literal}',
      price:       '{/literal}{$price|unescape:'htmlall'|escape:'quotes':'UTF-8'}{literal}',
      stock:       '{/literal}{$stock}{literal}'
      {/literal}{if isset($instock)},instock: {$instock}{/if}{literal}
  {/literal}{else}{literal}
      name:        '{/literal}{$meta_title|unescape:'htmlall'|escape:'quotes':'UTF-8'}{literal}',
      description: '{/literal}{$meta_description|unescape:'htmlall'|escape:'quotes':'UTF-8'}{literal}',
      image:       '{/literal}{if isset($image_url)}{$image_url}{else}{$logo_url}{/if}{literal}'
  {/literal}{/if}{literal}
  };

  var js = document.createElement('script'); js.type = 'text/javascript'; js.async = true; js.id = 'AddShoppers';
  js.src = ('https:' == document.location.protocol ? 'https://shop.pe/widget/' : 'http://cdn.shop.pe/widget/') + 'widget_async.js#{/literal}{$shop_id}{literal}';
  document.getElementsByTagName("head")[0].appendChild(js);
// ]]>
</script>
{/literal}

<!-- AddShoppers.com Buttons Script -->
{if $floating_buttons}
<div class="share-buttons share-buttons-tab" data-buttons="twitter,facebook,email,pinterest" data-style="medium" data-counter="true" data-hover="true" data-promo-callout="true" data-float="left"></div>
{/if}

{literal}
<script type="text/javascript">
  jQuery(document).ready(function() {
    var header = $("#header");
    if (header.length > 0)
      header.after($("#addshoppers_buttons"));

    var fb = $("#left_share_fb");
    if (fb.length > 0)
      fb.hide();
  });
</script>
{/literal}
