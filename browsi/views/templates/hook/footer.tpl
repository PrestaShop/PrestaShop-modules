{*
* 2013 Brow.si
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
*  @author MySiteApp Ltd. <support@mysiteapp.com>
*  @copyright  2013 MySiteApp Ltd.
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of MySiteApp Ltd
*}
<script type="text/javascript">
    var _brPrestashop = {
        checkout: '{$link->getPageLink('order.php', true, NULL, 'step=1')|escape:'htmlall':'UTF-8'}',
        cart: '{$link->getPageLink('order.php', true)|escape:'htmlall':'UTF-8'}',
        cartAjax: {if ($smarty.const._PS_VERSION_ < 1.5)}'{$link->getPageLink('cart.php', true)|escape:'htmlall':'UTF-8'}'{else}baseDir{/if}
    };
    (function(w, d){
{if !empty($browsi_site_id)}        w['_brSiteId'] = '{$browsi_site_id}';
{/if}        w['_brPlatform'] = ['prestashop', '{$smarty.const._PS_VERSION_|escape:'htmlall':'UTF-8'}'];{literal}
        function br() {
            var i='browsi-js'; if (d.getElementById(i)) {return;}
            var siteId = /^[a-zA-Z0-9]{1,7}$/.test(w['_brSiteId']) ? w['_brSiteId'] : null;
            var js=d.createElement('script'); js.id=i; js.async=true;
            js.src='//js.brow.si/' + ( siteId != null ? siteId + '/' : '' ) + 'br.js';
            (d.head || d.getElementsByTagName('head')[0]).appendChild(js);
        }
        d.readyState == 'complete' ? br() :
                ( w.addEventListener ? w.addEventListener('load', br, false) : w.attachEvent('onload', br) );
    })(window, document);
</script>
{/literal}