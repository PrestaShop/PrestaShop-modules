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
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}


<div style="height:200px; text-align:center; padding-top:100px;margin:0px; font-weight:bold; color: #8f2b72;">
	{l s='Please wait while your order is being processed...' mod='syspay'}
	<br/><br/>
	<img src="{$base_dir|escape:'htmlall':'UTF-8'}img/loader.gif" />
</div>
<script type="text/javascript">
function checkwaitingorder()
{ldelim}
	$.ajax({ldelim}
		type:"POST",
		async:true,
		url:'{$base_dir|escape:'htmlall':'UTF-8'}modules/syspay/checkwaitingorder.php',
		data:'id_cart={$id_cart|intval}&id_module={$id_module|intval}&key={$key|escape}',
		success:function (r) {ldelim}
			if (r == 'ok')
				window.location.href = '{$syspay_link|escape:'htmlall':'UTF-8'}?id_cart={$id_cart|intval}&id_module={$id_module|intval}&key={$key|escape}';
		{rdelim}
	{rdelim});
	setTimeout('checkwaitingorder()', 5000);
{rdelim}
setTimeout('checkwaitingorder()', 5000);
</script>
<br class="clear"/>
