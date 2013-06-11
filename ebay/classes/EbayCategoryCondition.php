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
		$xml_data = $request->GetCategoryFeatures();

		$category_conditions = array();
		$missing_data_category_ids = array();
		foreach($xml_data->Category as $category)
		{
			$category_id = (int)$category->CategoryID;
			
			$category_data = array();
			
			if (isset($category->ConditionValues->Condition))
				foreach($category->ConditionValues->Condition as $condition)
					$category_data['conditions'][] = array(
						'id' 		=> (int)$condition->ID,
						'name'	=> pSQL((string)$condition->DisplayName)
					);
							
			if (isset($category->ConditionEnabled))
				$category_data['condition_enabled'] = ($category->ConditionEnabled == 'Enabled');
			
			if (!isset($category_data['condition_enabled']) && !isset($category_data['conditions']))
				$missing_data_category_ids[] = $category_id;
			elseif (isset($category_data['condition_enabled']) && $category_data['condition_enabled'] && !isset($category_data['conditions']))
				$missing_data_category_ids[] = $category_id;
			
			$category_conditions[$category_id] = $category_data;
		}
		
		echo count($missing_data_category_ids).'/'.count($category_conditions)."\n";
		
		$db = DB::getInstance();
		$categories = $db->executeS('SELECT id_category_ref, id_category_ref_parent, level
			FROM '._DB_PREFIX_.'ebay_category
			WHERE id_category_ref in ('.implode(',', $missing_data_category_ids).') 
			ORDER BY level ASC');

		foreach ($categories as $category)
		{
			$category_id = $category['id_category_ref'];
			$parent_id = $category['id_category_ref_parent'];
		
			if (!isset($category_conditions[$category_id]['condition_enabled'])
				&& isset($category_conditions[$parent_id]['condition_enabled']))
			{
				$category_conditions[$category_id]['condition_enabled'] = $category_conditions[$parent_id]['condition_enabled'];
				if ($category_conditions[$category_id]['condition_enabled'])
				{
					if (!isset($category_conditions[$category_id]['conditions'])
						&& isset($category_conditions[$parent_id]['conditions']))
					{
						$category_conditions[$category_id]['conditions'] = $category_conditions[$parent_id]['conditions'];
						unset($missing_data_category_ids[array_search($category_id, $missing_data_category_ids)]);
					}
				}
				else
					unset($missing_data_category_ids[array_search($category_id, $missing_data_category_ids)]);
			}
			elseif (!isset($category_conditions[$category_id]['conditions'])
				&& isset($category_conditions[$parent_id]['conditions']))
			{
				$category_conditions[$category_id]['condition_enabled'] = true;
				$category_conditions[$category_id]['conditions'] = $category_conditions[$parent_id]['conditions'];
				unset($missing_data_category_ids[array_search($category_id, $missing_data_category_ids)]);
			}
		}
		
		echo count($missing_data_category_ids);
		foreach($missing_data_category_ids as $id)
		{
			foreach ($categories as $category)
			{
				if ($id == $category['id_category_ref'])
					echo $category['level'].'-'.$category['id_category_ref']."\n";
			}
		}
				
//		}
		

//		$db = Db::getInstance();
//		$db->insert('ebay_category_condition', $insert_data);

		/*
					$insert_data[] = array(
						'id_category_ref'  => $category_id,
						'id_condition_ref' => (int)$condition->ID,
						'name'						 => pSQL((string)$condition->DisplayName)
					);
		*/
	}
	
}