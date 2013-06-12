{*
* 2007-2013 PrestaShop
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
*  @copyright  2007-2013 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
    <head>
        <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=iso-8859-1" />
    </head>
    <body onload="document.getElementById('socoForm').submit();">
        <div style="width:320px;margin:0 auto;text-align:center;">
            <form id="socoForm" name="form" action="{$socolissimo_url}" method="POST">

                {foreach from=$inputs key=key item=val}
                    <input type="hidden" name="{$key}" value="{$val}"/>
                {/foreach}
                <img src="logo.gif" />
                <p>{l s='You will be redirect to socolissimo in few moment. If it is not the case, please click button.' mod='socolissimo'}</p>
                <p><img src="img/ajax-loader.gif" /></p>
                <input type="submit" value="Envoyer" />
            </form>
        </div>
    </body>
</html>