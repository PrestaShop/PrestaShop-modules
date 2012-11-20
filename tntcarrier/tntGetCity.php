	<?php
include( '../../config/config.inc.php' );
require_once(_PS_MODULE_DIR_."/tntcarrier/classes/TntWebService.php");

$postal = htmlentities($_GET['code']);

$cities = array();

if ($postal == '75000')
{
	for ($i = 1; $i <= 20; $i++)
	{
		if ($i < 10)
			$nb = '0'.$i;
		else
			$nb = $i;
		echo '<option value="PARIS '.$nb.'">PARIS '.$nb.'</option>';
	}
}
else if ($postal == '69000')
{
	for ($i = 1; $i < 10; $i++)
	{
		echo '<option value="LYON 0'.$i.'">LYON 0'.$i.'</option>';
	}
}
else if ($postal == '13000')
{
	for ($i = 1; $i <= 16; $i++)
	{
		if ($i < 10)
			$nb = '0'.$i;
		else
			$nb = $i;
		echo '<option value="MARSEILLE '.$nb.'">MARSEILLE '.$nb.'</option>';
	}
}
else
{
	try
	{
		if (isset($_GET['id_shop']))
			$tntWebService = new TntWebService(Tools::safeOutput($_GET['id_shop']));
		else
			$tntWebService = new TntWebService();
		$cities = $tntWebService->getCity($postal);
	} 
	catch( SoapFault $e ) 
	{
		$erreur = $e->faultstring;
	}
	catch( Exception $e ) 
	{
		$erreur = "Problem : follow failed";
	}
	if (!isset($erreur))
	{
		if (isset($cities->City) && is_array($cities->City))
			{
				foreach ($cities->City as $v)
					echo '<option value="'.$v->name.'">'.$v->name.'</option>';
			}
		else if (isset($cities->City))
			echo '<option value="'.$cities->City->name.'">'.$cities->City->name.'</option>';
		else
			echo '<option value="">Aucune ville</option>';
	}
	else if (!Configuration::get('TNT_CARRIER_LOGIN') || !Configuration::get('TNT_CARRIER_PASSWORD') || !Configuration::get('TNT_CARRIER_NUMBER_ACCOUNT'))
		echo 'account';
	else
		echo '<option>ERROR</option>';
}
?>