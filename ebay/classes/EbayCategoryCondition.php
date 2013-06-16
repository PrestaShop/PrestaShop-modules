<?php

if (file_exists(dirname(__FILE__).'/EbayRequest.php'))
	require_once(dirname(__FILE__).'/EbayRequest.php');

class EbayCategoryCondition
{
	/**
	 *
	 * Parse the data returned by the API for the eBay Category Conditions
	 *
	 */
	public static function loadCategoryConditions()
	{
		$request = new EbayRequest();

		$ebay_category_ids = EbayCategoryConfiguration::getEbayCategoryIds();
		$conditions = array();		
		foreach($ebay_category_ids as $category_id)
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
			
			foreach($xml_conditions as $xml_condition)
				$conditions[] = array(
					'id_category_ref' 		=> (int)$category_id,
					'id_condition_ref' 		=> (int)$xml_condition->ID,
					'name'								=> pSQL((string)$xml_condition->DisplayName)
				);
		}
		
		if ($conditions) // security to make sure there are values to enter befor truncating the table
		{
			$db = Db::getInstance();
			$db->Execute('TRUNCATE '._DB_PREFIX_.'ebay_category_condition');
			$db->insert('ebay_category_condition', $conditions);			
		}		
		
	}
	
}