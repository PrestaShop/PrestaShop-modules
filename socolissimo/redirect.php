<?php
/*
* 2007-2010 PrestaShop 
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
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

require_once('../../config/config.inc.php');
require_once(_PS_ROOT_DIR_.'/init.php');
require_once(dirname(__FILE__).'/classes/SCFields.php');

$so = new SCfields('API');

$fields = $so->getFields();

// Build back the fields list for SoColissimo, gift infos are send using the JS
$inputs = array();
foreach($_GET as $key => $value)
	if (in_array($key, $fields))
		$inputs[$key] = Tools::getValue($key);

$param_plus = array(
	// Get the data set before
	Tools::getValue('trParamPlus'),
	Tools::getValue('gift'),
	Tools::getValue('gift_message')
);

$inputs['trParamPlus'] = implode('|', $param_plus);
// Add signature to get the gift and gift message in the trParamPlus
$inputs['signature'] = $so->generateKey($inputs);

$onload_script = 'parent.$.fancybox.close();';
if (Tools::isSubmit('first_call'))
	$onload_script = 'document.getElementById(\'socoForm\').submit();';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
	<head>
		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=iso-8859-1" />
	</head>
	<body onload="<?php echo $onload_script; ?>">
		<form id="socoForm" name="form" action="<?php echo Configuration::get('SOCOLISSIMO_URL'); ?>" method="POST">
			<?php
				foreach($inputs as $key => $val)
					echo '<input type="hidden" name="'.Tools::safeOutput($key).'" value="'.Tools::safeOutput($val).'"/>';
			?>
		</form>
	</body>
</html>
