{*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<link rel="stylesheet" type="text/css" href="{$module_dir}static/css/shop.css" />
<meta property="og:title" content="{$meta_title|escape:'htmlall':'UTF-8'}" />
{if $is_product_page}
<meta property="og:type" content="addshoppers:product" />
{if isset($id_image)}
<meta property="og:image" content="{$absolute_base_url}img/p/{$id_product}-{$id_image}-large.jpg" />
{/if}
{else}
<meta property="og:type" content="addshoppers:website" />
<meta property="og:image" content="{$absolute_base_url}img/logo.jpg" />
{/if}
<meta property="og:site_name" content="{$shop_name|escape:'htmlall':'UTF-8'}" />
<meta property="og:description" content="{$meta_description|escape:html:'UTF-8'}" />

{literal}
<!-- AddShoppers.com Sharing Script -->
<script type="text/javascript">
// <![CDATA[

  var AddShoppersTracking = {
{/literal}{if $is_product_page}{literal}
      name: "{/literal}{$product_name|escape:'htmlall':'UTF-8'}{literal}",
      description: "{/literal}{$product_description|escape:html:'UTF-8'|replace:"\r\n":''|replace:"\n":''}{literal}",
      image: "{/literal}{if isset($id_image)}{$absolute_base_url}img/p/{$id_product}-{$id_image}-large.jpg{/if}{literal}",
      price: "{/literal}{$price}{literal}",
      stock: "{/literal}{$stock}{literal}"
      {/literal}{if isset($instock)},instock: {$instock}{/if}{literal}
  {/literal}{else}{literal}
      name: '{/literal}{$meta_title|escape:'htmlall':'UTF-8'}{literal}',
      description: '{/literal}{$meta_description|escape:html:'UTF-8'}{literal}',
      image: '{/literal}{$absolute_base_url}img/logo.jpg{literal}'
  {/literal}{/if}{literal}
  };

  var js = document.createElement('script'); js.type = 'text/javascript'; js.async = true; js.id = 'AddShoppers';
  js.src = ('https:' == document.location.protocol ? 'https://shop.pe/widget/' : 'http://cdn.shop.pe/widget/') + 'widget_async.js#{/literal}{$shop_id}{literal}';
  document.getElementsByTagName("head")[0].appendChild(js);
// ]]>
</script>
{/literal}

<!-- AddShoppers.com Buttons Script -->
<div id="addshoppers_buttons" class="{if !empty($buttons_social)}addshoppers-enabled grid_9 alpha omega{else}addshoppers-disabled{/if}">
    {if !empty($buttons_opengraph) && !empty($buttons_social) && !$default_account }
      {if $opengraph && $is_product_page}
        <div style="float:left">{$buttons_opengraph}</div>
      {/if}
      {if $social }
        {$buttons_social}
      {/if}
    {else}
      {if ($opengraph || $default_account) && $is_product_page }
        <div style="float:left">
          <div data-style="standard" class="share-buttons share-buttons-fb-like"></div>
          <div class="share-buttons share-buttons-og" data-action="want" data-counter="false"></div>
          <div class="share-buttons share-buttons-og" data-action="own" data-counter="false"></div>
        </div>
      {/if}
      {if $social || $default_account }
        <div class="share-buttons share-buttons-panel" data-style="medium" data-counter="true" data-oauth="true" data-hover="true" data-buttons="twitter,facebook,pinterest"></div>
      {/if}
    {/if}
</div>
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
