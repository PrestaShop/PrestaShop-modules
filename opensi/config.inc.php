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

$CONF_CachetimeLbl = "OSI_CACHETIME_";
$CONF_LastrequestLbl = "OSI_LASTREQUEST_";
$CONF_WsReqListLbl = array(	"WSO-G002" => "Stock articles",
							"WSO-G003" => "Code colis",
							"WSO-G008" => "Etat commandes",
							"WSO-G009" => "Tarifs articles",
							"WSO-P005" => "Ajout article",
							"WSO-P006" => "Editer article",
							"WSO-P010" => "Ajout client",
							"WSO-P011" => "Ajout commande",
							"WSO-P015" => "Ajout transaction",
							"WSO-P025" => "Editer client"
						);

/* GET request list */
$CONF_WsGetReqList = array();
if(Configuration::get('OSI_ACTIVE_WSO-G002') == 1)
	$CONF_WsGetReqList[] = 'WSO-G002';

if(Configuration::get('OSI_ACTIVE_WSO-G003') == 1)
	$CONF_WsGetReqList[] = 'WSO-G003';

if(Configuration::get('OSI_ACTIVE_WSO-G008') == 1)
	$CONF_WsGetReqList[] = 'WSO-G008';

if(Configuration::get('OSI_ACTIVE_WSO-G009') == 1)
	$CONF_WsGetReqList[] = 'WSO-G009';


/* POST requests list */
$CONF_WsPostReqList = array();
if(Configuration::get('OSI_ACTIVE_WSO-P005') == 1)
	$CONF_WsPostReqList[] = 'WSO-P005';

if(Configuration::get('OSI_ACTIVE_WSO-P006') == 1)
	$CONF_WsPostReqList[] = 'WSO-P006';

if(Configuration::get('OSI_ACTIVE_WSO-P010') == 1)
	$CONF_WsPostReqList[] = 'WSO-P010';

if(Configuration::get('OSI_ACTIVE_WSO-P011') == 1)
	$CONF_WsPostReqList[] = 'WSO-P011';

if(Configuration::get('OSI_ACTIVE_WSO-P015') == 1)
	$CONF_WsPostReqList[] = 'WSO-P015';

if(Configuration::get('OSI_ACTIVE_WSO-P025') == 1)
	$CONF_WsPostReqList[] = 'WSO-P025';


/* Logs configuration */
$CONF_default_log = "debug"; //debug, info, warn, error


/* Do not edit */
class GlobalConfig {

	private static $isInit = false;
	private static $globalConfigList;

	private static $webServiceUrl 				= 'OSI_WS_URL';
	private static $webServicePort 				= 'OSI_WS_PORT';
	private static $webServiceLogin				= 'OSI_WS_LOGIN';
	private static $webServicePasswd			= 'OSI_WS_PASSWD';

	private static $serviceCode 				= 'OSI_SERVICE_CODE';
	private static $depositCode 				= 'OSI_DEPOSIT_CODE';
	private static $webSiteCode 				= 'OSI_WEBSITE_CODE';	

	private static $caracIdHeight				= 'OSI_CARAC_ID_HEIGHT';	
	private static $caracIdWeight 				= 'OSI_CARAC_ID_WEIGHT';	
	private static $caracIdDepth 				= 'OSI_CARAC_ID_DEPTH';	

	private static $stateIdOnPreparation 		= 'OSI_STATE_ID_ON_PREPARATION';	
	private static $stateIdOnDelivery			= 'OSI_STATE_ID_ON_DELIVERY';	
	private static $stateIdCanceled				= 'OSI_STATE_ID_CANCELED';	

	private static $linkAttribute1				= 'OSI_LINK_ATTRIBUTE1';
	private static $linkAttribute2				= 'OSI_LINK_ATTRIBUTE2';
	private static $linkAttribute3				= 'OSI_LINK_ATTRIBUTE3';
	private static $linkAttribute4				= 'OSI_LINK_ATTRIBUTE4';
	private static $linkAttribute5				= 'OSI_LINK_ATTRIBUTE5';
	private static $linkAttribute6				= 'OSI_LINK_ATTRIBUTE6';
	private static $linkAttribute1_isfeature	= 'OSI_LINK_ATTRIBUTE1_ISFEATURE';
	private static $linkAttribute2_isfeature	= 'OSI_LINK_ATTRIBUTE2_ISFEATURE';
	private static $linkAttribute3_isfeature	= 'OSI_LINK_ATTRIBUTE3_ISFEATURE';
	private static $linkAttribute4_isfeature	= 'OSI_LINK_ATTRIBUTE4_ISFEATURE';
	private static $linkAttribute5_isfeature	= 'OSI_LINK_ATTRIBUTE5_ISFEATURE';
	private static $linkAttribute6_isfeature	= 'OSI_LINK_ATTRIBUTE6_ISFEATURE';

	private static $linkFeatureVolume			= 'OSI_LINK_FEATURE_VOLUME';
	private static $linkFeatureHeight			= 'OSI_LINK_FEATURE_HEIGHT';
	private static $linkFeatureWidth			= 'OSI_LINK_FEATURE_WIDTH';
	private static $linkFeatureLength			= 'OSI_LINK_FEATURE_LENGTH';

	private static $defaultPrice				= 'OSI_DEFAULT_PRICE';

	private static $defaultLangId 				= 'PS_LANG_DEFAULT';
	private static $defaultCountryId			= 'PS_COUNTRY_DEFAULT';	

	private static $configurationMode			= 'OSI_CONFIGURATION_MODE';


	/*
	 * constructor
	 * init global config
	 */
	public static function initGlobalConfig() {
		if(!self::$isInit) {
			$globalConfList = array(0=>self::$webServiceUrl, self::$webServicePort, self::$webServiceLogin, self::$webServicePasswd, self::$serviceCode,
									self::$depositCode, self::$webSiteCode, self::$stateIdOnPreparation, self::$stateIdOnDelivery, 
									self::$stateIdCanceled, self::$linkAttribute1, self::$linkAttribute2, self::$linkAttribute3, 
									self::$linkAttribute4, self::$linkAttribute5, self::$linkAttribute6, self::$linkAttribute1_isfeature, 
									self::$linkAttribute2_isfeature, self::$linkAttribute3_isfeature, self::$linkAttribute4_isfeature,
									self::$linkAttribute5_isfeature, self::$linkAttribute6_isfeature, self::$linkFeatureVolume,	self::$linkFeatureHeight,
									self::$linkFeatureWidth, self::$linkFeatureLength, self::$defaultLangId, self::$defaultCountryId, self::$defaultPrice,
									self::$configurationMode
									);
			self::$globalConfigList = configuration::getMultiple($globalConfList);
		}
	}


	/*
	 * return config value
	 */
	public static function getWsUrl(){
		self::initGlobalConfig();
		return self::$globalConfigList[self::$webServiceUrl];
	}

	/*
	 * return config value
	 */
	public static function getWsPort(){
		self::initGlobalConfig();
		return self::$globalConfigList[self::$webServicePort];
	}

	/*
	 * return config value
	 */	
	public static function getWsLogin(){
		self::initGlobalConfig();
		return self::$globalConfigList[self::$webServiceLogin];
	}

	/*
	 * return config value
	 */	
	public static function getWsPasswd(){
		self::initGlobalConfig();
		return self::$globalConfigList[self::$webServicePasswd];
	}

	/*
	 * return config value
	 */	
	public static function getServiceCode(){
		self::initGlobalConfig();
		return self::$globalConfigList[self::$serviceCode];
	}

	/*
	 * return config value
	 */	
	public static function getDepositCode(){
		self::initGlobalConfig();
		return self::$globalConfigList[self::$depositCode];
	}

	/*
	 * return config value
	 */	
	public static function getWebSiteCode(){
		self::initGlobalConfig();
		return self::$globalConfigList[self::$webSiteCode];
	}

	/*
	 * return config value
	 */	
	public static function getStateIdOnPreparation(){
		self::initGlobalConfig();
		return self::$globalConfigList[self::$stateIdOnPreparation];
	}

	/*
	 * return config value
	 */	
	public static function getStateIdOnDelivery(){
		self::initGlobalConfig();
		return self::$globalConfigList[self::$stateIdOnDelivery];
	}

	/*
	 * return config value
	 */	
	public static function getStateIdCanceled(){
		self::initGlobalConfig();
		return self::$globalConfigList[self::$stateIdCanceled];
	}

	/*
	 * return config value
	 */	
	public static function getDefaultLangId(){
		self::initGlobalConfig();
		return self::$globalConfigList[self::$defaultLangId];
	}

	/*
	 * return config value
	 */	
	public static function getDefaultCountryId(){
		self::initGlobalConfig();
		return self::$globalConfigList[self::$defaultCountryId];
	}

	/*
	 * return config value
	 */
	public static function getLinkAttribute1() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkAttribute1];
	}

	/*
	 * return config value
	 */
	public static function getLinkAttribute2() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkAttribute2];
	}

	/*
	 * return config value
	 */
	public static function getLinkAttribute3() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkAttribute3];
	}

	/*
	 * return config value
	 */
	public static function getLinkAttribute4() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkAttribute4];
	}

	/*
	 * return config value
	 */
	public static function getLinkAttribute5() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkAttribute5];
	}

	/*
	 * return config value
	 */
	public static function getLinkAttribute6() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkAttribute6];
	}

	/*
	 * return config value
	 */
	public static function getLinkAttribute1_isfeature() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkAttribute1_isfeature];
	}

	/*
	 * return config value
	 */
	public static function getLinkAttribute2_isfeature() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkAttribute2_isfeature];
	}

	/*
	 * return config value
	 */
	public static function getLinkAttribute3_isfeature() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkAttribute3_isfeature];
	}

	/*
	 * return config value
	 */
	public static function getLinkAttribute4_isfeature() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkAttribute4_isfeature];
	}

	/*
	 * return config value
	 */
	public static function getLinkAttribute5_isfeature() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkAttribute5_isfeature];
	}

	/*
	 * return config value
	 */
	public static function getLinkAttribute6_isfeature() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkAttribute6_isfeature];
	}

	/*
	 * return config value
	 */
	public static function getLinkFeatureVolume() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkFeatureVolume];
	}

	/*
	 * return config value
	 */
	public static function getLinkFeatureHeight() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkFeatureHeight];
	}

	/*
	 * return config value
	 */
	public static function getLinkFeatureWidth() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkFeatureWidth];
	}

	/*
	 * return config value
	 */
	public static function getLinkFeatureLength() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$linkFeatureLength];
	}

	/*
	 * return config value
	 */
	public static function getDefaultPrice() {
		self::initGlobalConfig();
		return self::$globalConfigList[self::$defaultPrice];
	}

	/*
	 * return OpenSi version
	 */
	public static function checkOpenSiVersion($version) {
		libxml_set_streams_context(stream_context_create(array('http' => array('timeout' => 3))));
		if ($feed = @simplexml_load_file('http://www.opensi.fr/connect/version.xml') AND $version < $feed->version->num)
			return array('name' => $feed->version->name, 'link' => $feed->download->link);
		return false;
	}

}