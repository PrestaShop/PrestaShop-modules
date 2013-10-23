<?php
/*
* Shopgate GmbH
*
* URHEBERRECHTSHINWEIS
*
* Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
* zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
* Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
* öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
* schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
*
* COPYRIGHT NOTICE
*
* This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
* for the purpose of facilitating communication between the IT system of the customer and the IT system
* of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
* transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
* of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
*
*  @author Shopgate GmbH <interfaces@shopgate.com>
*/

class ShopgateMobileRedirect extends ShopgateObject implements ShopgateMobileRedirectInterface {
	/**
	 * @var string alias name of shop at Shopgate, e.g. 'yourshop' to redirect to 'http://yourshop.shopgate.com'
	 */
	protected $alias = '';
	
	/**
	 * @var string your shops cname entry to redirect to
	 */
	protected $cname = '';
	
	/**
	 * @var ShopgateMerchantApiInterface
	 */
	protected $merchantApi;
	
	/**
	 * @var ShopgateConfig
	 */
	protected $config;

	/**
	 * @var string[] list of strings that cause redirection if they occur in the client's user agent
	 */
	protected $redirectKeywords = array();

	/**
	 * @var string[] list of strings that deny redirection if they occur in the client's user agent; overrides $this->redirectKeywords
	 */
	protected $skipRedirectKeywords = array();

	/**
	 * @var bool
	 */
	protected $updateRedirectKeywords;

	/**
	 * @var int (hours)
	 */
	protected $redirectKeywordCacheTime;

	/**
	 * @var bool true in case the website is delivered via HTTPS (this will load the Shopgate javascript via HTTPS as well to avoid browser warnings)
	 */
	protected $useSecureConnection;

	/**
	 * @var string
	 */
	protected $mobileHeaderTemplatePath;
	
	/**
	 * @var string path to the shopgate javascript template
	 */
	protected $jsHeaderTemplatePath;

	/**
	 * @var string expiration date of the cookie as defined in http://www.ietf.org/rfc/rfc2109.txt
	 */
	protected $cookieLife;

	/**
	 * @var string url to the image for the "switched on" button
	 */
	protected $buttonOnImageSource;

	/**
	 * @var string url to the image for the "switched off" button
	 */
	protected $buttonOffImageSource;

	/**
	 * @var string description to be displayed to the left of the button
	 */
	protected $buttonDescription;
	
	/**
	 * @var string identifier CSS style identifier for the parent element of the Mobile Header
	 */
	protected $buttonParent;
	
	/**
	 * @var bool true to add the Mobile Header as first child of the parent element, false to append it
	 */
	protected $buttonPrepend;
	
	
	/**
	 * @var string redirectCode used for creating a mobile product url
	 */
	protected $redirectType;
	
	/**
	 * @var bool true if redirecting unknown pages should be enabled
	 */
	protected $enableDefaultRedirect;
	
	/**
	 * @var string itemNumber used for creating a mobile product url
	 */
	protected $itemNumber;
	
	/**
	 * @var string itemNumberPublic used for creating a mobile product url with a item number public
	 */
	protected $itemNumberPublic;
	
	/**
	 * @var string categoryNumber used for creating a mobile category url / mobile head js
	 */
	protected $categoryNumber;
	
	/**
	 * @var string cmsPage used for creating a mobile cms url / mobile head js
	 */
	protected $cmsPage;
	
	/**
	 * @var string manufacturerName used for creating a mobile brand url  / mobile head js
	 */
	protected $manufacturerName;
	
	/**
	 * @var string searchQuery used for creating a mobile search url  / mobile head js
	 */
	protected $searchQuery;

	
	
	/**
	 * Instantiates the Shopgate mobile redirector.
	 *
	 * @param string $shopgateConfig An instance of the ShopgateConfig
	 * @param ShopgateMerchantApiInterface $merchantApi An instance of the ShopgateMerchantApi required for keyword updates or null.
	 */
	public function __construct(ShopgateConfig $shopgateConfig, ShopgateMerchantApiInterface $merchantApi = null) {
		$this->merchantApi = $merchantApi;
		$this->config = $shopgateConfig;
		$this->setAlias($shopgateConfig->getAlias());
		$this->setCustomMobileUrl($shopgateConfig->getCname());

		if($this->config->getEnableRedirectKeywordUpdate()){
			$this->enableKeywordUpdate();
		} else {
			$this->disableKeywordUpdate();
		}
		
		$this->redirectKeywordCacheTime = ShopgateMobileRedirectInterface::DEFAULT_CACHE_TIME;
		$this->buttonParent = 'body';
		$this->buttonPrepend = true;
		
		$this->useSecureConnection = isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] === "on" || $_SERVER["HTTPS"] == "1") || $this->config->getAlwaysUseSsl();
		
		// mobile header options
		$this->mobileHeaderTemplatePath = dirname(__FILE__).'/../assets/mobile_header.html';
		$this->jsHeaderTemplatePath = dirname(__FILE__).'/../assets/js_header.html';
		$this->cookieLife = gmdate('D, d-M-Y H:i:s T', time());
		$this->buttonDescription = 'Mobile Webseite aktivieren';
	}

	####################
	# general settings #
	####################

	public function setButtonDescription($description) {
		if (!empty($description)) $this->buttonDescription = $description;
	}

	public function setAlias($alias){
		$this->alias = $alias;
	}

	public function setCustomMobileUrl($cname){
		if(!preg_match("/^(https?:\/\/\S+)?$/i", $cname)) {
			$cname = "http://"  . $cname;
		}
		
		$this->cname = $cname;
	}
	
	public function setParentElement($identifier, $prepend = true) {
		$this->buttonParent = $identifier;
		$this->buttonPrepend = $prepend;
	}
	
	public function enableKeywordUpdate($cacheTime = ShopgateMobileRedirectInterface::DEFAULT_CACHE_TIME) {
		$this->updateRedirectKeywords = true;
		$this->redirectKeywordCacheTime = ($cacheTime >= ShopgateMobileRedirectInterface::MIN_CACHE_TIME) ? $cacheTime : ShopgateMobileRedirectInterface::MIN_CACHE_TIME;
		// try loading keywords
		$this->updateRedirectKeywords();
	}

	public function disableKeywordUpdate() {
		$this->updateRedirectKeywords = false;
	}

	public function addRedirectKeyword($keyword){
		if(is_array($keyword)){
			$this->redirectKeywords = array_merge($this->redirectKeywords, $keyword);
		} else {
			$this->redirectKeywords[] = $keyword;
		}
	}

	public function removeRedirectKeyword($keyword){
		if(is_array($keyword)){
			foreach($keyword as $word){
				foreach($this->redirectKeywords as $key => $mobileKeyword){
					if(strtolower($word) == strtolower($mobileKeyword)){
						unset($this->redirectKeywords[$key]);
					}
				}
			}
		} else {
			foreach($this->redirectKeywords as $key => $mobileKeyword){
				if(strtolower($keyword) == strtolower($mobileKeyword)){
					unset($this->redirectKeywords[$key]);
				}
			}
		}
	}

	public function setRedirectKeywords(array $redirectKeywords){
		$this->redirectKeywords = $redirectKeywords;
	}

	public function setSkipRedirectKeywords(array $skipRedirectKeywords){
		$this->skipRedirectKeywords = $skipRedirectKeywords;
	}

	public function setAlwaysUseSSL() {
		$this->useSecureConnection = true;
	}

	public function isMobileRequest() {
		// find user agent
		$userAgent = '';
		if(!empty($_SERVER['HTTP_USER_AGENT'])){
			$userAgent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			return false;
		}
		
		// check user agent for redirection keywords and skip redirection keywords and return the result
		return
			(!empty($this->redirectKeywords)     ?  preg_match('/'.implode('|', $this->redirectKeywords).'/i', $userAgent)     : false) &&
			(!empty($this->skipRedirectKeywords) ? !preg_match('/'.implode('|', $this->skipRedirectKeywords).'/i', $userAgent) : true);
	}

	public function isRedirectAllowed() {
		// if GET parameter is set create cookie and do not redirect
		if (!empty($_GET['shopgate_redirect'])) {
			setcookie(ShopgateMobileRedirectInterface::COOKIE_NAME, 1, time() + 604800, '/'); // expires after 7 days
			return false;
		}
		
		return empty($_COOKIE[ShopgateMobileRedirectInterface::COOKIE_NAME]) ? true : false;
	}

	public function redirect($url, $autoRedirect = true) {
		if (!$this->config->getShopNumber()) {
			return '';
		}

		if(!$this->isRedirectAllowed() || !$this->isMobileRequest() || !$autoRedirect || (($this->redirectType == 'default') && !$this->enableDefaultRedirect)) {
			return $this->getJsHeader($url);
		}
		
		// validate url
		if (!preg_match('#^(http|https)\://#', $url)) {
			return $this->getJsHeader();
		}
		
		// perform redirect
		header("Location: ". $url, true, 301);
		exit;
	}
	
	/**
	 * @deprecated
	 */
	public function getMobileHeader() {
		if(!$this->isMobileRequest() || !$this->isRedirectAllowed()){
			return '';
		}
		
		if (!file_exists($this->mobileHeaderTemplatePath)) {
			return '';
		}
		
		$html = @file_get_contents($this->mobileHeaderTemplatePath);
		if (empty($html)) {
			return '';
		}
		
		// set parameters
		$this->buttonOnImageSource = (($this->useSecureConnection) ? ShopgateMobileRedirectInterface::SHOPGATE_STATIC_SSL : ShopgateMobileRedirectInterface::SHOPGATE_STATIC).'/api/mobile_header/button_on.png';
		$this->buttonOffImageSource = (($this->useSecureConnection) ? ShopgateMobileRedirectInterface::SHOPGATE_STATIC_SSL : ShopgateMobileRedirectInterface::SHOPGATE_STATIC).'/api/mobile_header/button_off.png';
		$html = str_replace('{$cookieName}', ShopgateMobileRedirectInterface::COOKIE_NAME, $html);
		$html = str_replace('{$buttonOnImageSource}',  $this->buttonOnImageSource,  $html);
		$html = str_replace('{$buttonOffImageSource}', $this->buttonOffImageSource, $html);
		$html = str_replace('{$buttonDescription}', $this->buttonDescription, $html);
		$html = str_replace('{$buttonParent}', $this->buttonParent, $html);
		$html = str_replace('{$buttonPrepend}', (($this->buttonPrepend) ? 'true' : 'false'), $html);
		
		return $html;
	}

	protected function getJsHeader($mobileRedirectUrl = null) {
		if (!file_exists($this->jsHeaderTemplatePath)) {
			return '';
		}
		
		$html = @file_get_contents($this->jsHeaderTemplatePath);
		if (empty($html)) {
			return '';
		}
		
		if (!$this->config->getShopNumber()) {
			return '';
		}
		
		if (empty($mobileRedirectUrl)) {
			$mobileRedirectUrl = $this->getShopUrl();
		}
		
		$additionalParameters = '';
		$redirectCode = '';
		switch($this->redirectType){
			case 'item':
				if(!isset($this->itemNumber) || $this->itemNumber == ''){
					$redirectCode = 'default';
					break;
				}
				$redirectCode = 'item';
				$additionalParameters .= '_shopgate.item_number = "'.$this->itemNumber.'";';
				break;
			case 'itempublic':
				if(!isset($this->itemNumberPublic) || $this->itemNumberPublic == ''){
					$redirectCode = 'default';
					break;
				}
				$redirectCode = 'item';
				$additionalParameters .= '_shopgate.item_number_public = "'.$this->itemNumberPublic.'";';
				break;
			case 'category':
				if(!isset($this->categoryNumber) || $this->categoryNumber == ''){
					$redirectCode = 'default';
					break;
				}
				$redirectCode = 'category';
				$additionalParameters .= '_shopgate.category_number = "'.$this->categoryNumber.'";';
				break;
			case 'cms':
				if(!isset($this->cmsPage) || $this->cmsPage == ''){
					$redirectCode = 'default';
					break;
				}
				$redirectCode = 'cms';
				$additionalParameters .= '_shopgate.cms_page = "'.$this->cmsPage .'";';
				break;
			case 'brand':
				if(!isset($this->manufacturerName) || $this->manufacturerName == ''){
					$redirectCode = 'default';
					break;
				}
				$redirectCode = 'brand';
				$additionalParameters .= '_shopgate.brand_name = "'.$this->manufacturerName.'";';
				break;
			case 'search':
				if(!isset($this->searchQuery) || $this->searchQuery == ''){
					$redirectCode = 'default';
					break;
				}
				$redirectCode = 'search';
				$additionalParameters .= '_shopgate.search_query = "'.$this->searchQuery.'";';
				break;
			case 'start':
				$redirectCode = 'start';
				break;
			default:
				$redirectCode = 'default';
		}
		
		if($redirectCode == 'default') {
			$additionalParameters .= '_shopgate.is_default_redirect_disabled = '.((!$this->enableDefaultRedirect) ? 'true' : 'false').';';
		}
		
		switch($this->config->getServer()){
			default: // fall through to 'live'
			case 'live':
				$sslUrl = ShopgateMobileRedirectInterface::SHOPGATE_STATIC_SSL;
				$nonSslUrl = ShopgateMobileRedirectInterface::SHOPGATE_STATIC;
			break;
			case 'sl':
				$sslUrl = ShopgateMobileRedirectInterface::SHOPGATE_SL_STATIC_SSL;
				$nonSslUrl = ShopgateMobileRedirectInterface::SHOPGATE_SL_STATIC;
			break;
			case 'pg':
				$sslUrl = ShopgateMobileRedirectInterface::SHOPGATE_PG_STATIC_SSL;
				$nonSslUrl = ShopgateMobileRedirectInterface::SHOPGATE_PG_STATIC;
			break;
			case 'custom':
				$sslUrl = 'https://shopgatedev-public.s3.amazonaws.com';
				$nonSslUrl = 'http://shopgatedev-public.s3.amazonaws.com';
			break;
		}
		
		// set parameters
		$html = str_replace('{$mobile_url}', $mobileRedirectUrl, $html);
		$html = str_replace('{$shop_number}', $this->config->getShopNumber(), $html);
		$html = str_replace('{$redirect_code}', $redirectCode, $html);
		$html = str_replace('{$additional_parameters}', $additionalParameters, $html);
		$html = str_replace('{$ssl_url}', $sslUrl, $html);
		$html = str_replace('{$non_ssl_url}', $nonSslUrl, $html);
		
		return $html;
	}

	###############
	### helpers ###
	###############
	
	/**
	 * Generates the root mobile Url for the redirect
	 */
	protected function getMobileUrl(){
		if(!empty($this->cname)){
			return $this->cname;
		} elseif(!empty($this->alias)){
			return 'http://'.$this->alias.$this->getShopgateUrl();
		}
	}

	/**
	 * Returns the URL to be appended to the alias of a shop.
	 *
	 * The method determines this by the "server" setting in ShopgateConfig. If it's set to
	 * "custom", localdev.cc will be used for Shopgate local development and testing.
	 *
	 * @return string The URL that can be appended to the alias, e.g. ".shopgate.com"
	 */
	protected function getShopgateUrl() {
		switch ($this->config->getServer()) {
			default: // fall through to "live"
			case 'live':	return ShopgateMobileRedirectInterface::SHOPGATE_LIVE_ALIAS;
			case 'sl':		return ShopgateMobileRedirectInterface::SHOPGATE_SL_ALIAS;
			case 'pg':		return ShopgateMobileRedirectInterface::SHOPGATE_PG_ALIAS;
			case 'custom':	return '.localdev.cc/php/shopgate/index.php'; // for Shopgate development & testing
		}
	}

	/**
	 * Updates the (skip) keywords array from cache file or Shopgate Merchant API if enabled.
	 */
	protected function updateRedirectKeywords() {
		// load the keywords
		try {
			$redirectKeywordsFromFile = $this->loadKeywordsFromFile($this->config->getRedirectKeywordCachePath());
			$skipRedirectKeywordsFromFile = $this->loadKeywordsFromFile($this->config->getRedirectSkipKeywordCachePath());
		} catch (ShopgateLibraryException $e) {
			// if reading the files fails DO NOT UPDATE
			return;
		}
		
		// conditions for updating keywords
		$updateDesired = (
			$this->updateRedirectKeywords &&
			(!empty($this->merchantApi)) && (
				(time() - ($redirectKeywordsFromFile['timestamp'] + ($this->redirectKeywordCacheTime * 3600)) > 0) ||
				(time() - ($skipRedirectKeywordsFromFile['timestamp'] + ($this->redirectKeywordCacheTime * 3600)) > 0)
			)
		);
		
		// strip timestamp, it's not needed anymore
		$redirectKeywords = $redirectKeywordsFromFile['keywords'];
		$skipRedirectKeywords = $skipRedirectKeywordsFromFile['keywords'];
		
		// perform update
		if ($updateDesired) {
			try {
				// fetch keywords from Shopgate Merchant API
				$keywordsFromApi = $this->merchantApi->getMobileRedirectUserAgents();
				$redirectKeywords = $keywordsFromApi['keywords'];
				$skipRedirectKeywords = $keywordsFromApi['skip_keywords'];
				
				// save keywords to their files
				$this->saveKeywordsToFile($redirectKeywords, $this->config->getRedirectKeywordCachePath());
				$this->saveKeywordsToFile($skipRedirectKeywords, $this->config->getRedirectSkipKeywordCachePath());
			} catch (Exception $e) {
				/* do not abort */
				$newTimestamp = (time() - ($this->redirectKeywordCacheTime * 3600)) + 300;
				// save old keywords
				$this->saveKeywordsToFile($redirectKeywords, $this->config->getRedirectKeywordCachePath(), $newTimestamp);
				$this->saveKeywordsToFile($skipRedirectKeywords, $this->config->getRedirectSkipKeywordCachePath(), $newTimestamp);
			}
		}
		
		// set keywords
		$this->setRedirectKeywords($redirectKeywords);
		$this->setSkipRedirectKeywords($skipRedirectKeywords);
	}
	
	/**
	 * Saves redirect keywords to file.
	 *
	 * @param string[] $keywords The list of keywords to write to the file.
	 * @param string $file The path to the file.
	 */
	protected function saveKeywordsToFile($keywords, $file, $timestamp = null) {
		if(is_null($timestamp)){
			$timestamp = time();
		}
		array_unshift($keywords, $timestamp); // add timestamp to first line
		if (!@file_put_contents($file, implode("\n", $keywords))) {
			// no logging - this could end up in spamming the logs
			// $this->log(ShopgateLibraryException::buildLogMessageFor(ShopgateLibraryException::FILE_READ_WRITE_ERROR, 'Could not write to "'.$file.'".'));
		}
	}
	
	/**
	 * Reads redirect keywords from file.
	 *
	 * @param string $file The file to read the keywords from.
	 * @return array<'timestamp' => int, 'keywords' => string[])
	 * 			An array with the 'timestamp' of the last update and the list of 'keywords'.
	 * @throws ShopgateLibraryException in case the file cannot be opened.
	 */
	protected function loadKeywordsFromFile($file) {
		$defaultReturn = array(
			'timestamp' => 0,
			'keywords' => array()
		);
		
		$cacheFile = @fopen($file, 'a+');
		if (empty($cacheFile)) {
			// exception without logging
			throw new ShopgateLibraryException(ShopgateLibraryException::FILE_READ_WRITE_ERROR, 'Could not read file "'.$file.'".', false, false);
		}
		
		$keywordsFromFile = explode("\n", @fread($cacheFile, filesize($file)));
		@fclose($cacheFile);
		
		return (empty($keywordsFromFile))
			? $defaultReturn
			: array(
				'timestamp' => (int) array_shift($keywordsFromFile), // strip timestamp in first line
				'keywords' => $keywordsFromFile,
			);
	}

	#############################
	### mobile url generation ###
	#############################

	
	public function buildScriptDefault($autoRedirect = true) {
		$this->redirectType = 'default';
		$this->enableDefaultRedirect = $this->config->getEnableDefaultRedirect();
		return $this->redirect($this->getShopUrl(), $autoRedirect);
	}
	
	public function buildScriptShop($autoRedirect = true){
		$this->redirectType = 'start';
		return $this->redirect($this->getShopUrl(), $autoRedirect);
	}
	
	public function buildScriptItem($itemNumber, $autoRedirect = true){
		$this->itemNumber = $itemNumber;
		$this->redirectType = 'item';
		return $this->redirect($this->getItemUrl($itemNumber), $autoRedirect);
	}
	
	public function buildScriptItemPublic($itemNumberPublic, $autoRedirect = true){
		$this->itemNumberPublic = $itemNumberPublic;
		$this->redirectType = 'itempublic';
		return $this->redirect($this->getItemPublicUrl($itemNumberPublic), $autoRedirect);
	}
	
	public function buildScriptCategory($categoryNumber, $autoRedirect = true){
		$this->categoryNumber = $categoryNumber;
		$this->redirectType = 'category';
		return $this->redirect($this->getCategoryUrl($categoryNumber), $autoRedirect);
	}
	
	public function buildScriptCms($cmsPage, $autoRedirect = true){
		$this->cmsPage = $cmsPage;
		$this->redirectType = 'cms';
		return $this->redirect($this->getCmsUrl($cmsPage), $autoRedirect);
	}
	
	public function buildScriptBrand($manufacturerName, $autoRedirect = true){
		$this->manufacturerName = $manufacturerName;
		$this->redirectType = 'brand';
		return $this->redirect($this->getBrandUrl($manufacturerName), $autoRedirect);
	}
	
	public function buildScriptSearch($searchQuery, $autoRedirect = true){
		$this->searchQuery = $searchQuery;
		$this->redirectType = 'search';
		return $this->redirect($this->getSearchUrl($searchQuery), $autoRedirect);
	}
	
	public function getShopUrl(){
		return $this->getMobileUrl();
	}

	public function getItemUrl($itemNumber){
		return $this->getMobileUrl().'/item/'.bin2hex($itemNumber);
	}

	public function getItemPublicUrl($itemNumberPublic){
		return $this->getMobileUrl().'/itempublic/'.bin2hex($itemNumberPublic);
	}

	public function getCategoryUrl($categoryNumber){
		return $this->getMobileUrl().'/category/'.bin2hex($categoryNumber);
	}

	public function getCmsUrl($cmsPage){
		return $this->getMobileUrl().'/cms/'.$cmsPage;
	}

	public function getBrandUrl($manufacturerName){
		return $this->getMobileUrl().'/brand/?q='.urlencode($manufacturerName);
	}

	public function getSearchUrl($searchQuery){
		return $this->getMobileUrl().'/search/?s='.urlencode($searchQuery);
	}
}


/**
 * Helper class for redirection from shop system to mobile webpage.
 *
 * Provides analyzation of the client's user agent, creation of redirection links for
 * different redirects (e.g. product, category, search), keyword updating and caching,
 * javascript for the "on/off" switch and sending the redirect headers to the client's
 * browser.
 *
 * @author Shopgate GmbH, 35510 Butzbach, DE
 *
 */
interface ShopgateMobileRedirectInterface {
	const SHOPGATE_STATIC = 'http://static.shopgate.com';
	const SHOPGATE_STATIC_SSL = 'https://static-ssl.shopgate.com';
	
	const SHOPGATE_PG_STATIC = 'http://static.shopgatepg.com';
	const SHOPGATE_PG_STATIC_SSL = 'https://static-ssl.shopgatepg.com';
	
	const SHOPGATE_SL_STATIC = 'http://static.shopgatesl.com';
	const SHOPGATE_SL_STATIC_SSL = 'https://static-ssl.shopgatesl.com';
	
	
	/**
	 * @var string the URL that is appended to the end of a shop alias (aka subdomain) if the shop is live
	 */
	const SHOPGATE_LIVE_ALIAS = '.shopgate.com';
	
	/**
	 * @var string the URL that is appended to the end of a shop alias (aka subdomain) if the shop is on spotlight
	 */
	const SHOPGATE_SL_ALIAS = '.shopgatesl.com';

	/**
	 * @var string the URL that is appended to the end of a shop alias (aka subdomain) if the shop is on playground
	 */
	const SHOPGATE_PG_ALIAS = '.shopgatepg.com';

	/**
	 * @var string name of the cookie to set in case a customer turns of mobile redirect
	 */
	const COOKIE_NAME = 'SHOPGATE_MOBILE_WEBPAGE';

	/**
	 * @var int (hours) the minimum time that can be set for updating of the cache
	 */
	const MIN_CACHE_TIME = 1;

	/**
	 * @var int (hours) the default time to be set for updating the cache
	 */
	const DEFAULT_CACHE_TIME = 24;

	/**
	 * Sets the description to be displayed to the left of the button.
	 *
	 * @deprecated
	 * @param string $description
	 */
	public function setButtonDescription($description);
	
	/**
	 * Sets the alias of the Shopgate shop
	 *
	 * @deprecated
	 * @param string $alias
	 */
	public function setAlias($alias);
	
	/**
	 * Sets the cname of the shop
	 *
	 * @deprecated
	 * @param string $cname
	 */
	public function setCustomMobileUrl($cname);
	
	/**
	 * Sets the parent element the Mobile Header should be attached to.
	 *
	 * @deprecated
	 * @param string $identifier CSS style identifier for the parent element.
	 * @param bool $prepend True to add the Mobile Header as first child of the parent element, false to append it.
	 */
	public function setParentElement($identifier, $prepend = false);
	
	/**
	 * Enables updating of the keywords that identify mobile devices from Shopgate Merchant API.
	 *
	 * @deprecated
	 * @param int $cacheTime Time the keywords are cached in hours. Will be set to at least ShopgateMobileRedirectInterface::MIN_CACHE_TIME.
	 */
	public function enableKeywordUpdate($cacheTime = ShopgateMobileRedirectInterface::DEFAULT_CACHE_TIME);
	
	/**
	 * Disables updating of the keywords that identify mobile devices from Shopgate Merchant API.
	 *
	 * @deprecated
	 */
	public function disableKeywordUpdate();
	
	/**
	 * Appends a new keyword to the redirect keywords list.
	 *
	 * @deprecated
	 * @param string $keyword The redirect keyword to append.
	 */
	public function addRedirectKeyword($keyword);
	
	/**
	 * Removes a keyword or an array of redirect keywords from the keywords list.
	 *
	 * @deprecated
	 * @param string|string[] $keyword The redirect keyword or keywords to remove.
	 */
	public function removeRedirectKeyword($keyword);
	
	/**
	 * Replaces the current list of redirect keywords with a given list.
	 *
	 * @deprecated
	 * @param string[] $redirectKeywords The new list of redirect keywords.
	 */
	public function setRedirectKeywords(array $redirectKeywords);
	
	/**
	 * Replaces the current list of skiüp redirect keywords with a given list.
	 *
	 * @deprecated
	 * @param string[] $skipRedirectKeywords The new list of skip redirect keywords.
	 */
	public function setSkipRedirectKeywords(array $skipRedirectKeywords);
	
	/**
	 * Switches to secure connection instead of checking server-side.
	 *
	 * This will cause slower download of nonsensitive material (the mobile header button images) from Shopgate.
	 * Activate only if the secure connection is determined incorrectly (e.g. because of third-party components).
	 *
	 * @deprecated
	 */
	public function setAlwaysUseSSL();

	/**
	 * Detects by redirect keywords (and skip redirect keywords) if a request was sent by a mobile device.
	 *
	 * @deprecated
	 * @return bool true if a mobile device could be detected, false otherwise.
	 */
	public function isMobileRequest();

	/**
	 * Detects whether the customer wants to be redirected.
	 *
	 * @deprecated
	 * @return bool true if the customer wants to be redirected, false otherwise.
	 */
	public function isRedirectAllowed();

	/**
	 * Redirects to a given (valid) URL.
	 *
	 * If the $url parameter is no valid URL the method will simply return false and do nothing else.
	 * Otherwise it will output the necessary redirection headers and stop script execution.
	 *
	 * @deprecated
	 * @param string $url the URL to redirect to
	 * @param bool $setCookie true to set the redirection cookie and activate redirection
	 * @return false if the passed $url parameter is no valid URL
	 */
	public function redirect($url);

	/**
	 * Returns the javascript and HTML for the mobile redirect button
	 *
	 * @deprecated
	 * @return string
	 */
	public function getMobileHeader();
	
	
	/**
	 * Generates a redirect to a item, if its a mobile request and parameter autoRedirectr is set to true. Otherweise the html snippet is returned
	 *
	 * @param string $itemNumber the product item number
	 * @param boolean $autoRedirect if its set to true a redirect will attempt
	 *
	 * @return $jsHeader - returns a html snippet for the <head></head> tag
	 */
	public function buildScriptItem($itemNumber, $autoRedirect = true);
	
	/**
	 * Generates a redirect to a item (with item number public), if its a mobile request and parameter autoRedirectr is set to true. Otherweise the html snippet is returned
	 *
	 * @param string $itemNumberPublic the product item number public
	 * @param boolean $autoRedirect if its set to true a redirect will attempt
	 *
	 * @return $jsHeader - returns a html snippet for the <head></head> tag
	 */
	public function buildScriptItemPublic($itemNumberPublic, $autoRedirect = true);
	
	/**
	 * Generates a redirect to a category, if its a mobile request and parameter autoRedirectr is set to true. Otherweise the html snippet is returned
	 *
	 * @param string $categoryNumber the category number
	 * @param boolean $autoRedirect if its set to true a redirect will attempt
	 *
	 * @return $jsHeader - returns a html snippet for the <head></head> tag
	 */
	public function buildScriptCategory($categoryNumber, $autoRedirect = true);
	
	/**
	 * Generates a redirect to startmenu, if its a mobile request and parameter autoRedirectr is set to true. Otherweise the html snippet is returned
	 *
	 * @param boolean $autoRedirect if its set to true a redirect will attempt
	 *
	 * @return $jsHeader - returns a html snippet for the <head></head> tag
	 */
	public function buildScriptShop($autoRedirect = true);
	
	/**
	 * Generates a redirect to cms page, if its a mobile request and parameter autoRedirectr is set to true. Otherweise the html snippet is returned
	 *
	 * @param string $cmsPage the cms page key
	 * @param boolean $autoRedirect if its set to true a redirect will attempt
	 *
	 * @return $jsHeader - returns a html snippet for the <head></head> tag
	 */
	public function buildScriptCms($cmsPage, $autoRedirect = true);
	
	/**
	 * Generates a redirect to manufacterer page, if its a mobile request and parameter autoRedirectr is set to true. Otherweise the html snippet is returned
	 *
	 * @param string $manufacturerName the manufacterer name
	 * @param boolean $autoRedirect if its set to true a redirect will attempt
	 *
	 * @return $jsHeader - returns a html snippet for the <head></head> tag
	 */
	public function buildScriptBrand($manufacturerName, $autoRedirect = true);
	
	/**
	 * Generates a redirect to a mobile search, if its a mobile request and parameter autoRedirectr is set to true. Otherweise the html snippet is returned
	 *
	 * @param string $searchString the search string
	 * @param boolean $autoRedirect if its set to true a redirect will attempt
	 *
	 * @return $jsHeader - returns a html snippet for the <head></head> tag
	 */
	public function buildScriptSearch($searchString, $autoRedirect = true);
	
	/**
	 * Create a mobile-shop-url to the startmenu
	 *
	 * @deprecated
	 */
	public function getShopUrl();

	/**
	 * Create a mobile-product-url to a item
	 *
	 * @deprecated
	 * @param string $itemNumber
	 */
	public function getItemUrl($itemNumber);

	/**
	 * Create a mobile-product-url to a item with item_number_public
	 *
	 * @deprecated
	 * @param string $itemNumberPublic
	 */
	public function getItemPublicUrl($itemNumberPublic);

	/**
	 * Create a mobile-category-url to a category
	 *
	 * @deprecated
	 * @param string $categoryNumber
	 */
	public function getCategoryUrl($categoryNumber);

	/**
	 * Create a mobile-cms-url to a cms-page
	 *
	 * @deprecated
	 * @param string $cmsPage
	 */
	public function getCmsUrl($cmsPage);

	/**
	 * Create a mobile-brand-url to a page with results for a specific manufacturer
	 *
	 * @deprecated
	 * @param string $manufacturerName
	 */
	public function getBrandUrl($manufacturerName);

	/**
	 * Create a mobile-search-url to a page with search results
	 *
	 * @deprecated
	 * @param string $searchQuery
	 */
	public function getSearchUrl($searchQuery);
}