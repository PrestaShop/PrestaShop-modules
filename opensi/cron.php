<?php
/*
 * OpenSi Connect for Prestashop
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation; either version 2 of the License, or (at your
 * option) any later version.
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Speedinfo SARL
 * @copyright 2003-2012 Speedinfo SARL
 * @contact contact@speedinfo.fr
 * @url http://www.speedinfo.fr
 *
 */

error_reporting(1);

/* Loading prestashop classes */
if (file_exists(dirname(__FILE__).'/../../config/config.inc.php'))
	include(dirname(__FILE__).'/../../config/config.inc.php');

if (file_exists(dirname(__FILE__).'/../../init.php'))
	include(dirname(__FILE__).'/../../init.php');


/* Loading config module class */
if (file_exists(dirname(__FILE__).'/config.inc.php'))
	include_once("config.inc.php");


/* Loading utils */
if (file_exists(dirname(__FILE__).'/utils/log.class.php'))
	include_once("utils/log.class.php");

if (file_exists(dirname(__FILE__).'/utils/date.class.php'))
	include_once("utils/date.class.php");

if (file_exists(dirname(__FILE__).'/utils/mapping.class.php'))
	include_once("utils/mapping.class.php");

if (file_exists(dirname(__FILE__).'/utils/xml.class.php'))
	include_once("utils/xml.class.php");

if (file_exists(dirname(__FILE__).'/utils/scheduler.class.php'))
	include_once("utils/scheduler.class.php");

if (file_exists(dirname(__FILE__).'/utils/forceRequest.class.php'))
	include_once("utils/forceRequest.class.php");


/* Init log default type */
Log::setDefaultType($CONF_default_log);

/* Init of scheduler */
Scheduler::initTimer($CONF_WsGetReqList, $CONF_WsPostReqList, $CONF_CachetimeLbl, $CONF_LastrequestLbl);

/* Init of forceRequest (used to test only one request) */
ForceRequest::initForceRequest(Scheduler::getWsNameGetList(), Scheduler::getWsNamePostList());
if(ForceRequest::isForcedRequest() && isset($_GET['log'])){
	Log::setDefaultType($_GET['log']);
}


/*
 * Execute POST requests
 */
Log::write("###### DO POST REQUESTS ######", "info");
$listPOST = '';
for($i=0;$i<sizeof($CONF_WsPostReqList);$i++) {
	$listPOST .= ($i == 0)?$CONF_WsPostReqList[$i]:' - '.$CONF_WsPostReqList[$i];
}
Log::write("Webservices to call : ".$listPOST, "info");
include("postSynchCtrl.php");
Log::write("###### END POST REQUESTS / TREATMENTS ######<br /><br />", "info");		
			

/*
 * Execute GET requests
 */
Log::write("###### DO GET REQUESTS ######", "info");
$listGET = '';
for($i=0;$i<sizeof($CONF_WsGetReqList);$i++) {
	$listGET .= ($i == 0)?$CONF_WsGetReqList[$i]:' - '.$CONF_WsGetReqList[$i];
}
Log::write("Webservices to call : ".$listGET, "info");
include("getSynchCtrl.php");
Log::write("###### END GET REQUESTS / TREATMENTS ######<br /><br />", "info");	