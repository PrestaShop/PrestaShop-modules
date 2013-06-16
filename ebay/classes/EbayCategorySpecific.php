<?php

if (file_exists(dirname(__FILE__).'/EbayRequest.php'))
	require_once(dirname(__FILE__).'/EbayRequest.php');

class EbayCategorySpecific
{
	const SELECTION_MODE_FREE_TEXT 			= 0;
	const SELECTION_MODE_SELECTION_ONLY = 1;
	
	private static $prefix_to_field_names = array(
		'attr' => 'id_attribute_group',
		'feat' => 'id_feature',
		'spec' => 'id_ebay_category_specific_value'		
	);
	
	/**
	 * 
	 * Returns an array containing the correspondance between the form select fields prefix and the table field name
	 *
	 */
	public static function getPrefixToFieldNames()
	{
		return EbayCategorySpecific::$prefix_to_field_names;
	}
	
	/**
	 *
	 * Parse the data returned by the API and enter them in the table
	 *
	 */
	public static function loadCategorySpecifics()
	{
		$request = new EbayRequest();
		
		$ebay_category_ids = EbayCategoryConfiguration::getEbayCategoryIds();
		foreach($ebay_category_ids as $ebay_category_id)
		{
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
				$db->execute('INSERT INTO `'._DB_PREFIX_.'ebay_category_specific` (`id_category_ref`, `name`, `required`, `can_variation`, `selection_mode`) VALUES
					('.$ebay_category_id.', \''.pSQL((string)$recommendation->Name).'\', '.($required ? 1 : 0).', '.($can_variation ? 1 : 0).', '.($selection_mode ? 1 : 0).')
					ON DUPLICATE KEY UPDATE `required` = '.($required ? 1 : 0).', `can_variation` = '.($can_variation ? 1 : 0).', `selection_mode` = '.($selection_mode ? 1 : 0));
			
				$ebay_category_specific_id = $db->Insert_ID();
				if (!$ebay_category_specific_id)
					$ebay_category_specific_id = $db->getValue('SELECT `id_ebay_category_specific`
						FROM `'._DB_PREFIX_.'ebay_category_specific`
						WHERE `id_category_ref` = '.$ebay_category_id.'
						AND `name` = \''.pSQL((string)$recommendation->Name).'\'');
					
				foreach ($values as $value)
					$db->insert('ebay_category_specific_value', array(
							'id_ebay_category_specific' => $ebay_category_specific_id,
							'value'											=> pSQL($value),
						), false, true, Db::INSERT_IGNORE);
			}
			
		}
	}

}