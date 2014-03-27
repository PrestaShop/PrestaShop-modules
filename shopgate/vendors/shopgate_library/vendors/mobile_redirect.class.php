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

include_once dirname(__FILE__).'/../shopgate.php';

/**
 * Manage and create the JavaScript for redirect to mobile-site
 *
 * Sample:
 * <code>
 * &lt;!-- BEGIN SHOPGATE MOBILE HEADER CODE --&gt;
 * &lt;script&gt;
 * var _shopgate = {};
 * _shopgate.shop_number = "10334";
 * _shopgate.redirect = "item";
 * _shopgate.item_number = "ABC-123";
 * _shopgate.host = (("https:" == document.location.protocol) ? "https://static-ssl.shopgate.com" : "http://static.shopgate.com");
 * document.write(unescape("%3Cscript src='" + _shopgate.host + "/mobile_header/" + _shopgate.shop_number + ".js' type='text/javascript' %3E%3C/script%3E"));
 * &lt;/script&gt;
 * &lt;!-- END SHOPGATE MOBILE HEADER CODE --&gt;
 * </code>
 *
 * @author Martin Weber (Shopgate GmbH)
 * @version 1.0.0
 * @see http://www.shopgate.com/forum/showthread.php?280-Einbinden-der-Mobilen-Webseite
 */
class MobileRedirect {
	const DST_DEFAULT	= "";
	const DST_ITEM		= "item";
	const DST_CATEGORY	= "category";
	const DST_CMS		= "cms";

	const HTTP_HOST		= "http://static.shopgate.com";
	const HTTPS_HOST	= "https://static-ssl.shopgate.com";

	const HTTP_PG_HOST	= "http://static.shopgatepg.com";
	const HTTPS_PG_HOST	= "https://static-ssl.shopgatepg.com";

	/**
	 * Use Https
	 * @var boolean
	 */
	private $useHttps	= false;

	/**
	 * Options to be set as json-array
	 * @var array
	 */
	private $options	= array();

	/**
	 * The configuration of the Framework
	 * @var ShopgateConfig
	 */
	private $config		= array();

	/**
	 * The constructor load the Configuration and check the Request for using HTTPS
	 *
	 * The host and the shop_number will added options-array
	 */
	public function __construct($shopgateConfig = null) {
		try {
			if(is_null($shopgateConfig)){
				$shopgateConfig = new ShopgateConfig();
			}
			$this->config = $shopgateConfig;
			$this->useHttps = isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] === "on" || $_SERVER["HTTPS"] == "1");

			$this->addOption("host", $this->__getHostUrl());
			$this->addOption("shop_number", $this->config->getShopNumber());
		} catch (Exception $e) {

		}
	}

	/**
	 * Return a open script-tag for JavaScript
	 *
	 * @param string $src the source of an external Script
	 * @return string
	 */
	private function __jsOpen($src = "") {

		$jScript  = "<script type=\"text/javascript\"";
		if(!empty($src)) $jScript .= " src=\"$src\"";
		$jScript .= ">\n";

		return $jScript;
	}

	/**
	 * Close a script-tag
	 *
	 * @return string
	 */
	private function __jsClose() {
		return "</script>\n";
	}

	/**
	 * Returend the URL to the Hos, where the js-files hostet and switch between HTTP and HTTPS
	 *
	 * @return string
	 */
	private function __getHostUrl() {
		$url = $this->useHttps ? MobileRedirect::HTTPS_HOST : MobileRedirect::HTTP_HOST;

		if($this->config->getServer() == "pg") $url = $this->useHttps ? MobileRedirect::HTTPS_PG_HOST : MobileRedirect::HTTP_PG_HOST;

		return $url;
	}

	/**
	 * Returned the URL to the js-File
	 *
	 * @return string
	 */
	private function __getJsUrl() {
		return $this->__getHostUrl()
			. "/mobile_header/".$this->config->getShopNumber().".js";
	}

	/**
	 * Build a JavaScript-JSON-Object
	 *
	 * @param string $objName Name of the json-Object
	 * @param array $objValues Key-Value-Pairs for Parameters
	 *
	 *  @return string
	 */
	private function __buildJsonOptions($objName, $objValues = array()) {
		$json = "var $objName = {}\n";

		foreach($objValues as $key => $value) {
			$json .= "$objName.$key = '$value';\n";
		}

		return $json;
	}

	/**
	 * Build the whole Script-block for redirectiong to mobile website
	 *
	 * @return string
	 */
	private function __render() {
		$jScript  = $this->__jsOpen();
// 		$jScript .= "<!--\n";
		$jScript .= "//<![CDATA[\n";
		$jScript .= $this->__buildJsonOptions("_shopgate", $this->options);
		$jScript .= "//]]>\n";
// 		$jScript .= "-->\n";
		$jScript .= $this->__jsClose();

		$jScript .= $this->__jsOpen($this->__getJsUrl());
		$jScript .= $this->__jsClose();

		return $jScript;
	}

	/**
	 * Add Attribute to options. On rendering they will attach to the json-object
	 *
	 * <code>
	 * var obj = {};
	 * obj.key = 'value';
	 * </code>
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function addOption($key, $value) {
		$key = trim($key);
		$this->options[$key] = $value;
	}

	/**
	 * Remove a Attribute from Options
	 *
	 * @param string $key
	 */
	public function removeOption($key) {
		$key = trim($key);
		if(array_key_exists($key, $this->options))
			unset($this->options[$key]);
	}

	/**
	 * Return the rendered JavaScript for redirecting to mobile startpage
	 *
	 * If $directDraw ist set true the script will prompt with echo
	 *
	 * @param boolean $directDraw
	 */
	public function getScript($directDraw = false) {
		$jScript = $this->__render();
		if($directDraw) echo $jScript;
		return $jScript;
	}

	/**
	 * Return the rendered JavaScript for redirecting to mobile categorypage
	 *
	 * If $directDraw ist set true the script will prompt with echo
	 *
	 * @param string $categoryNumber
	 * @param boolean $directDraw
	 */
	public function getCategoryScript($categoryNumber, $directDraw = false) {
		$this->addOption("redirect", MobileRedirect::DST_CATEGORY);
		$this->addOption("category_number", $categoryNumber);

		$this->getScript($directDraw);
	}

	/**
	 * Return the rendered JavaScript for redirecting to mobile productpage
	 *
	 * If $directDraw ist set true the script will prompt with echo
	 *
	 * @param string $itemId
	 * @param boolean $directDraw
	 */
	public function getItemScript($itemId, $directDraw = false) {
		$this->addOption("redirect", MobileRedirect::DST_ITEM);
		$this->addOption("item_number", $itemId);

		$this->getScript($directDraw);
	}

	/**
	 * Return the rendered JavaScript for redirecting to mobile cmspage
	 *
	 * If $directDraw ist set true the script will prompt with echo
	 *
	 * @param string $categoryNumber
	 * @param boolean $directDraw
	 */
	public function getCmsScript($categoryNumber, $directDraw = false) {
		$this->addOption("redirect", MobileRedirect::DST_CMS);
		$this->addOption("cms_page", $this->objId);

		$this->getScript($directDraw);
	}
}