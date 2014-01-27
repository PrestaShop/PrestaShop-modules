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

{if !isset($no_footer) || !$no_footer}
  {if !$content_only}
			<div style="text-align: center; margin-top: 20px; color: #BBB;">{l s='Powered By'} <a href="http://www.prestashop.com" rel="external" style="color: #AAA;">PrestaShop</a> &bull; <a data-ajax="false" class="ps_full_site" rel="external" href="{$base_dir}?ps_full_site=1">{l s='View Full Site'}</a></div>
       </div>

     <!-- Footer -->
     <div class="footer" data-role="footer" data-theme="{$ps_mobile_styles.PS_MOBILE_THEME_HEADER_FOOTER}" data-position="fixed" style="height: 40px;">
		<a data-ajax="false" href="{$link->getPageLink('order.php', true)}" data-icon="grid" data-iconpos="left" class="ui-btn-left" data-mini="true">{l s='Cart'}{if $cart_qties} ({$cart_qties}){/if}</a>
		<a  data-ajax="false" href="{$link->getPageLink('sitemap.php', true)}" data-icon="star" data-iconpos="notext" class="ui-btn-right" style="margin-right: 35px;"></a>
		<a href="#jqm_page_options" data-icon="gear" data-iconpos="notext" class="ui-btn-right"></a>
    </div>
  {/if}
  </div>
</div><!-- End of data-role="page" div for jQuery Mobile -->
{/if}