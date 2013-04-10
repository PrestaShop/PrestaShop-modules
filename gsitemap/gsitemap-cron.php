<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/gsitemap.php');

$gsitemap = new Gsitemap();
if ($gsitemap->active)
{
	$gsitemap->cron = true;
	if (!isset($_GET['continue']))
		$gsitemap->removeSitemap();
	$gsitemap->createSitemap();
}