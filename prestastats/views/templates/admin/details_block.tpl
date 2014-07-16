{**
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2014 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *
 *}
<script type="text/javascript">$('a.prestastats-video-btn').fancybox();</script>
<script type="text/javascript">
$(document).ready(function() {
	$('a.prestastats-video-btn').fancybox({
		'type' : 'iframe',
		'width':605,
		'height':340
	});
});
</script>
<div class="row">
	<div class="prestastats-intro">
		<div class="col-lg-4">
			<a href="http://www.prestastats.com" class="prestastats-logo" >
				<img src="{$path_url|escape:'htmlall':'UTF-8'}img/prestastats.png" alt="prestastats" border="0"></a>
		</div>
		<div class="col-lg-7 pw-offset-1">
			<p>
				{l s='PrestaStats and PrestaShop have partnered to provide the easiest way for you to get meaningful stats acccol-sm-offset-1urately for your shop.' mod='prestastats'}
			</p>
		</div>
	</div>
</div>	
<div class="clear"></div>
<div class="prestastats-content row">
	<div class="prestastats-video col-lg-4">
		<h1>{l s='See how PrestaStats can drive your Business' mod='prestastats'}</h1>
		<p>{l s='Watch the video below and learn how PrestaStats can keep you on top of your most critical eCommerce Data. Or click on the link below to register your account, today.' mod='prestastats'}</p>
		<a href="https://player.vimeo.com/video/86295040" class="prestastats-video-btn" >
		<img src="{$path_url|escape:'htmlall':'UTF-8'}img/prestastats-video-screen.jpg" alt="prestastats Video">
		<img src="{$path_url|escape:'htmlall':'UTF-8'}img/btn-video.png" alt="video" class="video-icon"></a>
	</div>
	<div class="col-lg-7 pw-offset-1">
		<h1>{l s='A brighter future with PrestaStats.' mod='prestastats'}</h1>
		<p>{l s='"Diamonds polish Diamonds". This is the best way to clean a diamond and you have data which needs to be distilled, refined and interpreted so that your business shines like a diamond. With the PrestaStats executive dashboard and your knowledge of the business these two diamonds will polish one another and produce a brighter future for you.' mod='prestastats'}</p>
		<div class="right-fieldset">
		    <a href=http://www.prestastats.com/prestashop/ target=_blank title="Visit PrestaStats.com"><img src="{$path_url|escape:'htmlall':'UTF-8'}img/PrestaShop-darkbg-partner.png" alt="PrestaShop Official Partner" class="prestastats-badge"></a></br>
	        <a href=http://www.prestastats.com/en/prestashop-signup/ target=_blank title="Signup for PrestaStats Account"><img src="http://www.prestastats.com/wp-content/uploads/2014/02/25-dollar-price.png" alt="$25 per month" class="margin-left-93" ></a>
	    </div>
	                        
		<ul>
			<li>{l s='Profits' mod='prestastats'}</li>
			<li>{l s='Abandoned carts' mod='prestastats'}</li>
			<li>{l s='All Historical Data' mod='prestastats'}</li>
			<li>{l s='Customer conversions' mod='prestastats'}</li>
			<li>{l s='Exportable data' mod='prestastats'}</li>
			<li>{l s='Dynamic Charts & Graphs' mod='prestastats'}</li>
			<li>{l s='Interactive Grids & Tables' mod='prestastats'}</li>
		</ul>
	</div>
</div>	
<div class="clear"></div>