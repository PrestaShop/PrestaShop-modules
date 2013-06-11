<?php

if (file_exists(dirname(__FILE__).'/EbayRequest.php'))
	require_once(dirname(__FILE__).'/EbayRequest.php');

class EbayCategorySpecific
{
	const SELECTION_MODE_FREE_TEXT 			= 0;
	const SELECTION_MODE_SELECTION_ONLY = 1;
	
	/**
	 *
	 * Parse the data returned by the API and enter them in the table
	 *
	 */
	public static function loadCategorySpecifics($ebay_category_id)
	{
		$request = new EbayRequest();
		$xml_data = $request->GetCategorySpecifics($ebay_category_id);
		
		foreach($xml_data->Recommendations->NameRecommendation as $recommendation)
		{
			
			$required = isset($recommendation->ValidationRules->MinValues) 
				&& ((int)$recommendation->ValidationRules->MinValues >= 1);

			// if true can be used either in Item Specifics or VariationSpecifics
			$can_variation = !(isset($recommendation->ValidationRules->VariationSpecifics) 
				&& ((string)$recommendation->ValidationRules->VariationSpecifics == 'Disabled'));

			if (isset($recommendation->ValidationRules->SelectionMode))
				if ((string)$recommendation->ValidationRules->SelectionMode == 'Prefilled')
					continue;
				elseif ((string)$recommendation->ValidationRules->SelectionMode == 'SelectionOnly')
					$selection_mode = EbayCategorySpecific::SELECTION_MODE_SELECTION_ONLY;
				else
					$selection_mode = EbayCategorySpecific::SELECTION_MODE_FREE_TEXT;
			else
				$selection_mode = EbayCategorySpecific::SELECTION_MODE_FREE_TEXT;
			
			$values = array();
			if (isset($recommendation->ValueRecommendation->Value))
				foreach($recommendation->ValueRecommendation as $value_recommendation)
					$values[] = (string)$value_recommendation->Value;
			
			$db = Db::getInstance();
			$db->insert('ebay_category_specific', array(
				'id_category_ref'	  => $ebay_category_id,
				'name' 		 				  => pSQL((string)$recommendation->Name),
				'required' 				  => $required,
				'can_variation'			=> $can_variation,
				'selection_mode'  	=> $selection_mode				
			));
			$ebay_category_specific_id = $db->Insert_ID();
			
			foreach ($values as $value)
				$db->insert('ebay_category_specific_value', array(
					'id_ebay_category_specific' => $ebay_category_specific_id,
					'value'											=> pSQL($value),
				));
		}
	}

}