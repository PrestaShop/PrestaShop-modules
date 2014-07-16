{*
 * 	Riskified payments security module for Prestashop. Riskified reviews, approves & guarantees transactions you would otherwise decline.
 *
 *  @author    riskified.com <support@riskified.com>
 *  @copyright 2013-Now riskified.com
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of Riskified 
 *}
<script type="text/javascript"> //<![CDATA[
(function() {
	function riskifiedBeaconLoad() {
	var store_domain = '{$shop_url|escape:'htmlall':'UTF-8'}';
	var session_id = '{$session_id|escape:'htmlall':'UTF-8'}';
	var url = ('https:' == document.location.protocol ? 'https://' : 'http://')
	+ "beacon.riskified.com?shop=" + store_domain + "&sid=" + session_id;
	var s = document.createElement('script');
	s.type = 'text/javascript';
	s.async = true;
	s.src = url;
	var x = document.getElementsByTagName('script')[0];
	x.parentNode.insertBefore(s, x);
}
if (window.attachEvent)
	window.attachEvent('onload', riskifiedBeaconLoad)
else
	window.addEventListener('load', riskifiedBeaconLoad, false);
})();
//]]>
</script>