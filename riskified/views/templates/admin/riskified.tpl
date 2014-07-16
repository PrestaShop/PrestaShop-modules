{*
 *  Riskified payments security module for Prestashop. Riskified reviews, approves & guarantees transactions you would otherwise decline.
 *
 *  @author    riskified.com <support@riskified.com>
 *  @copyright 2013-Now riskified.com
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Riskified 
 *}

<script type="text/javascript">

function riskifiedGetPostUrl()
{
  return "{$base_url|escape:'htmlall':'UTF-8'}{$base_uri|escape:'htmlall':'UTF-8'}";
}

function riskifiedGetOrderId()
{
  return "{$order_id|escape:'htmlall':'UTF-8'}";
}

function riskifiedGetToken()
{
  return "{$token|escape:'htmlall':'UTF-8'}";
}

</script>
