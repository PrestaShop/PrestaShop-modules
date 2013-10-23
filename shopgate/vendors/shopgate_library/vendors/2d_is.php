<?php

class sg_2d_is {
	const LONG_URL = 0;
	const CMS = 1;
	const COUPON = 2;
	const CATALOG = 3;
	const BROCHURE = 4;
	const SHOP = 5;
	const CATEGORY = 6;
	
	const SHOP_ITEM = 7;
	const SHOP_ITEM_CHECKOUT = 8;
	const SHOP_ITEM_WITH_COUPON = 9;
	
	public static function getUrl($option, $data1 = null, $data2 = null, $data3 = null) {
		
		$url  = "http://2d.is";
		$url .= "/$option";
		
		if(!empty($data1)) {
			$url .= "/$data1";
			if(!empty($data2)) {
				$url .= "/$data2";
			}
			if(!empty($data3)) {
				$url .= "/$data3";
			}
		}
		
		return $url;
	}
	
	public static function getLongUrl($longUrl) {
		return sg_2d_is::getUrl(sg_2d_is::LONG_URL, $longUrl);
	}
	
	public static function getCmsUrl($shopNumber, $cmsKey) {
		return sg_2d_is::getUrl(sg_2d_is::CMS, $shopNumber, $cmsKey);
	}
	
	public static function getCouponUrl($couponCode) {
		return sg_2d_is::getUrl(sg_2d_is::COUPON, $couponCode);
	}
	
	public static function getCatalogUrl($catalogId, $catalogPage) {
		return sg_2d_is::getUrl(sg_2d_is::CATALOG, $catalogId, $catalogPage);
	}
	
	public static function getShopUrl($shopNumber) {
		return sg_2d_is::getUrl(sg_2d_is::SHOP, $shopNumber);
	}
	
	public static function getCategoryUrl($categoryId) {
		return sg_2d_is::getUrl(sg_2d_is::CATEGORY, $categoryId);
	}
	
	public static function getShopItemUrl($shopNumber, $itemNumber) {
		return sg_2d_is::getUrl(sg_2d_is::SHOP_ITEM, $shopNumber, $itemNumber);
	}
	
	public static function getShopItemCheckoutUrl($shopNumber, $itemNumber) {
		return sg_2d_is::getUrl(sg_2d_is::SHOP_ITEM_CHECKOUT, $shopNumber, $itemNumber);
	}
	
	public static function getShopItemWithCouponUrl($shopNumber, $itemNumber, $couponCode) {
		return sg_2d_is::getUrl(sg_2d_is::SHOP_ITEM_WITH_COUPON, $shopNumber, $itemNumber, $couponCode);
	}

}