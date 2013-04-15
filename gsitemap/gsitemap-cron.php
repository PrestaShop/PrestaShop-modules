<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
if (substr(Tools::encrypt('gsitemap/cron'), 0, 10) != Tools::getValue('token') || !Module::isInstalled('gsitemap'))
	die('Bad token');

include(dirname(__FILE__).'/gsitemap.php');

$gsitemap = new Gsitemap();
if ($gsitemap->active)
{
	$gsitemap->cron = true;
	if (!isset($_GET['continue']))
		$gsitemap->removeSitemap();
	$gsitemap->createSitemap();
}