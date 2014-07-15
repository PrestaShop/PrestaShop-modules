<?php
/**
 * 2013 Give.it
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@give.it so we can send you a copy immediately.
 *
 * @author    JSC INVERTUS www.invertus.lt <help@invertus.lt>
 * @copyright 2013 Give.it
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of Give.it
 */

class GiveItSdkOption extends GiveItSdkObject {

	private $types = array('single_choice', 'multiple_choice', 'delivery', 'layered', 'layered_delivery');

	public function valid()
	{
		if (!in_array($this->type, $this->types))
			return false;

		if ($this->id == null)
			return false;

		if ($this->name == null)
			return false;

		if (isset($this->price) && !is_int($this->price))
			return false;

		if (isset($this->tax_percent))
		{
			if (!is_int($this->tax_percent))
				return false;

			if ($this->tax_percent < 0 || $this->tax_percent > 100)
				return false;
		}

		return true;
	}

	public function addChoice(GiveItSdkChoice $choice)
	{
		if (!$choice->valid())
			return false;

		$this->choices[$choice->id] = $choice;

		return $this;
	}

	public function addChoices($choices)
	{
		if (empty($choices))
			return $this;

		foreach ($choices as $choice)
			$this->addChoice($choice);

		return $this;
	}

}
