<?php

/*
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
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
 *  @copyright  2007-2014 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (file_exists(dirname(__FILE__).'/EbayRequest.php'))
	require_once(dirname(__FILE__).'/EbayRequest.php');

class EbayCategoryCondition
{
	/**
	 *
	 * Parse the data returned by the API for the eBay Category Conditions
	 **/
	public static function loadCategoryConditions()
	{
		$request = new EbayRequest();
		$ebay_category_ids = EbayCategoryConfiguration::getEbayCategoryIds();
		$conditions = array();
		
		foreach ($ebay_category_ids as $category_id)
		{
			$xml_data = $request->GetCategoryFeatures($category_id);

			if (isset($xml_data->Category->ConditionEnabled))
				$condition_enabled = $xml_data->Category->ConditionEnabled;
			else
				$condition_enabled = $xml_data->SiteDefaults->ConditionEnabled;

			if (!$condition_enabled)
				return;

			if (isset($xml_data->Category->ConditionValues->Condition))
				$xml_conditions = $xml_data->Category->ConditionValues->Condition;
			else
				$xml_conditions = $xml_data->SiteDefaults->ConditionValues->Condition;

			foreach ($xml_conditions as $xml_condition)
				$conditions[] = array(
					'id_category_ref' => (int)$category_id,
					'id_condition_ref' => (int)$xml_condition->ID,
					'name' => pSQL((string)$xml_condition->DisplayName)
				);

			//
			Db::getInstance()->ExecuteS("SELECT 1");
		}

		if ($conditions) // security to make sure there are values to enter befor truncating the table
		{
			$db = Db::getInstance();
			$db->Execute('TRUNCATE '._DB_PREFIX_.'ebay_category_condition');

			if (version_compare(_PS_VERSION_, '1.5', '>'))
				$db->insert('ebay_category_condition', $conditions);
			else
				foreach ($conditions as $condition)
					$db->autoExecute(_DB_PREFIX_.'ebay_category_condition', $condition, 'INSERT');

			return true;
		}

		return false;
	}

}