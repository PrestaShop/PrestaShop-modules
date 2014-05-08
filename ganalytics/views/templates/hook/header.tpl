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
<script type="text/javascript">
    {if $universal_analytics eq true}
    {literal}
    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o),
                m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
    {/literal}

    ga('create', '{$ganalytics_id|escape:'htmlall':'UTF-8'}'{if isset($pageTrack)}, '{$pageTrack|escape:'htmlall':'UTF-8'}'{/if});

    {if $isOrder eq true}
    ga('require', 'ecommerce', 'ecommerce.js');
    {else}
    ga('send', 'pageview');
    {/if}

    {if $isOrder eq true}
    ga('ecommerce:addTransaction', {
        'id': '{$trans.id|escape:'htmlall':'UTF-8'}',
        'affiliation': '{$trans.store|escape:'htmlall':'UTF-8'}',
        'revenue': '{$trans.total|escape:'htmlall':'UTF-8'}',
        'tax': '{$trans.tax|escape:'htmlall':'UTF-8'}',
        'shipping': '{$trans.shipping|escape:'htmlall':'UTF-8'}',
        'city': '{$trans.city|escape:'htmlall':'UTF-8'}',
        'state': '{$trans.state|escape:'htmlall':'UTF-8'}',
        'country': '{$trans.country|escape:'htmlall':'UTF-8'}',
        'currency': '{$trans.currency|escape:'htmlall':'UTF-8'}'
    });

    {foreach from=$items item=item}
    ga('ecommerce:addItem', {
        'id': '{$item.OrderId|escape:'htmlall':'UTF-8'}',
        'sku': '{$item.SKU|escape:'htmlall':'UTF-8'}',
        'name': '{$item.Product|escape:'htmlall':'UTF-8'}',
        'category': '{$item.Category|escape:'htmlall':'UTF-8'}',
        'price': '{$item.Price|escape:'htmlall':'UTF-8'}',
        'quantity': '{$item.Quantity|escape:'htmlall':'UTF-8'}'
    });
    {/foreach}
    {literal}
    (function() {
	    {/literal}{if $isOrder eq true}{literal}
			var key = 'ga_trans';
			var idtrans = {/literal}{$trans.id|intval}{literal};
			if (!!$.prototype.totalStorage)
				var view_ga_trans = parseInt($.totalStorage(key));
			else if (typeof localStorage !== 'undefined' && localStorage)
				var view_ga_trans = parseInt(localStorage.getItem(key));

			if (typeof view_ga_trans !== 'undefined' &&  view_ga_trans > 0 && idtrans == view_ga_trans )
				return false;

			if (!!$.prototype.totalStorage)
				$.totalStorage(parseInt(key, idtrans));
			else if (typeof localStorage !== 'undefined' && localStorage)
				localStorage.setItem(key, parseInt(idtrans));
		{/literal}{/if}{literal}
		ga('ecommerce:send');
    })();
    {/literal}
    {/if}
    {else}
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '{$ganalytics_id|escape:'htmlall':'UTF-8'}']);
    // Recommended value by Google doc and has to before the trackPageView
    _gaq.push(['_setSiteSpeedSampleRate', 5]);

    _gaq.push(['_trackPageview'{if isset($pageTrack)}, '{$pageTrack|escape:'htmlall':'UTF-8'}'{/if}]);

    {if $isOrder eq true}            {* If it's an order we need more data for stats *}
    _gaq.push(['_addTrans',
        '{$trans.id|escape:'htmlall':'UTF-8'}', {* order ID - required *}
        '{$trans.store|escape:'htmlall':'UTF-8'}', {* affiliation or store name *}
        '{$trans.total|escape:'htmlall':'UTF-8'}', {* total - required *}
        '{$trans.tax|escape:'htmlall':'UTF-8'}', {* tax *}
        '{$trans.shipping|escape:'htmlall':'UTF-8'}', {* shipping *}
        '{$trans.city|escape:'htmlall':'UTF-8'}', {* city *}
        '{$trans.state|escape:'htmlall':'UTF-8'}', {* state or province *}
        '{$trans.country|escape:'htmlall':'UTF-8'}' {* country *}
    ]);

    {foreach from=$items item=item}
    _gaq.push(['_addItem',
        '{$item.OrderId|escape:'htmlall':'UTF-8'}', {* order ID - required *}
        '{$item.SKU|escape:'htmlall':'UTF-8'}', {* SKU/code - required *}
        '{$item.Product|escape:'htmlall':'UTF-8'}', {* product name *}
        '{$item.Category|escape:'htmlall':'UTF-8'}', {* category or variation *}
        '{$item.Price|escape:'htmlall':'UTF-8'}', {* unit price - required *}
        '{$item.Quantity|escape:'htmlall':'UTF-8'}' {* quantity - required *}
    ]);
    {/foreach}
    {* submits transaction to the Analytics servers *}
    {literal}
    _gaq.push(['_trackTrans']);
    {/literal}
    {/if}
    {literal}
    (function() {
	    {/literal}{if $isOrder eq true}{literal}
			var key = 'ga_trans';
			var idtrans = {/literal}{$trans.id|intval}{literal};
			if (!!$.prototype.totalStorage)
				var view_ga_trans = parseInt($.totalStorage(key));
			else if (typeof localStorage !== 'undefined' && localStorage)
				var view_ga_trans = parseInt(localStorage.getItem(key));

			if (typeof view_ga_trans !== 'undefined' &&  view_ga_trans > 0 && idtrans == view_ga_trans )
				return false;

			if (!!$.prototype.totalStorage)
				$.totalStorage(parseInt(key, idtrans));
			else if (typeof localStorage !== 'undefined' && localStorage)
				localStorage.setItem(key, parseInt(idtrans));
		{/literal}{/if}{literal}

        var ga = document.createElement('script');
        ga.type = 'text/javascript';
        ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(ga, s);
    })();
    {/literal}
    {/if}
</script>