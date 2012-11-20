<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 12823 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class FrontController extends FrontControllerCore
{
	/** @var _isActive Flag to know if the module is active or note */
	public static $_isActive = -1;

	public function addCSS($css_uri, $css_media_type = 'all')
	{
		if (self::_isActive())
		{
			if (!is_array($css_uri))
				$css_uri = array($css_uri);

			$new_uri = array();
			foreach ($css_uri as $uri)
				if ($uri && !preg_match('/^http(s?):\/\//', $uri) && preg_match('#.css$#', $uri))
				{
					$proto = 'http://';
					$uri = Tools::getMediaServer($uri, $proto).$uri; // Pass as reference, do not move $proto
					$new_uri[] = $proto.$uri;
				}
				else
					$new_uri[] = $uri;

			return parent::addCSS($new_uri, $css_media_type);
		}
		return parent::addCSS($css_uri, $css_media_type);
	}

	public function addJS($js_uri)
	{
		if (self::_isActive())
		{
			if (!is_array($js_uri))
				$js_uri = array($js_uri);

			foreach ($js_uri as &$uri)
				if ($uri && !preg_match('/^http(s?):\/\//', $uri))
				{
					$proto = 'http://';
					$uri = Tools::getMediaServer($uri, $proto).$uri;
					$uri = $proto.$uri;
				}
		}
		return parent::addJS($js_uri);
	}

	private static function _isActive()
	{
		if (self::$_isActive == -1)
		{
			// This override is part of the cloudcache module, so the cloudcache.php file exists
			require_once(dirname(__FILE__).'/../../../modules/cloudcache/cloudcache.php');
			$module = new CloudCache();
			self::$_isActive = $module->active;
		}

		return self::$_isActive && Configuration::get('CLOUDCACHE_API_ACTIVE');
	}

	// Override for 1.5
	public function init()
	{
		if (parent::$initialized)
			return;
		$ret = parent::init();

		if (!self::_isActive())
			return $ret;

		$assign_array = array(
			'img_ps_dir' => _PS_IMG_,
			'img_cat_dir' => _THEME_CAT_DIR_,
			'img_lang_dir' => _THEME_LANG_DIR_,
			'img_prod_dir' => _THEME_PROD_DIR_,
			'img_manu_dir' => _THEME_MANU_DIR_,
			'img_sup_dir' => _THEME_SUP_DIR_,
			'img_ship_dir' => _THEME_SHIP_DIR_,
			'img_store_dir' => _THEME_STORE_DIR_,
			'img_col_dir' => _THEME_COL_DIR_,
			'img_dir' => _THEME_IMG_DIR_,
			'css_dir' => _THEME_CSS_DIR_,
			'js_dir' => _THEME_JS_DIR_,
			'pic_dir' => _THEME_PROD_PIC_DIR_
		);

		/* // Add the images directory for mobile */
		/* if ($this->context->getMobileDevice() != false) */
		/* 	$assign_array['img_mobile_dir'] = _THEME_MOBILE_IMG_DIR_; */

		/* // Add the CSS directory for mobile */
		/* if ($this->context->getMobileDevice() != false) */
		/* 	$assign_array['css_mobile_dir'] = _THEME_MOBILE_CSS_DIR_; */

		$httHost = Tools::getHttpHost();
		$protocol_content = ((isset($this->ssl) && $this->ssl && Configuration::get('PS_SSL_ENABLED')) || Tools::usingSecureMode()) ? 'https://' : 'http://';

		foreach ($assign_array as $assignKey => $assignValue)
			if (substr($assignValue, 0, 1) == '/' || $protocol_content == 'https://')
				$this->context->smarty->assign($assignKey, $protocol_content.Tools::getMediaServer($assignValue).$assignValue);
			else
				$this->context->smarty->assign($assignKey, $assignValue);

		return $ret;
	}
}
