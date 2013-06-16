<?php

class EbayCategoryConditionConfiguration 
{
	const PS_CONDITION_NEW 				 = 1,
				PS_CONDITION_USED 			 = 2,
				PS_CONDITION_REFURBISHED = 3;
	
	public static function getPSConditions($condition_type = null)
	{
		$condition_types = array(
			EbayCategoryConditionConfiguration::PS_CONDITION_NEW 				 => 'new',
			EbayCategoryConditionConfiguration::PS_CONDITION_USED 			 => 'used',
			EbayCategoryConditionConfiguration::PS_CONDITION_REFURBISHED => 'refurbished'
		);
		
		if ($condition_type)
			return $condition_types[$condition_type];
		return $condition_types;
	}
}