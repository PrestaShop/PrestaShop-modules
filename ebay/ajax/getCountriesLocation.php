<?php 


$configPath = '../../../config/config.inc.php';
if (file_exists($configPath))
{
	include('../../../config/config.inc.php');
	
	$sql = "SELECT * FROM " . _DB_PREFIX_ . "ebay_shipping_zone_excluded WHERE region = '" . pSQL(Tools::getValue('region')) . "'";
	$countries = Db::getInstance()->ExecuteS($sql);
	$string = '';
	if(count($countries) > 0){
		foreach ($countries as $country) {
			
			$string .= '<div class="excludeCountry">';
			$string .= '<input type="checkbox" name="excludeLocation['.$country['location'].']" ';
				if($country['excluded'] == 1)
					$string .= ' checked="checked" ';

			$string .= '/>'.$country['description'];
			$string .= '</div>';
		}	
		echo $string;	
	}
	else
		echo "No countries were found for this region";	
}
else
	echo "Problem with configuration file";

	
