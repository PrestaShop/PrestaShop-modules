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

class GiveItSdkChoice extends GiveItSdkOption {

	public function valid()
	{
		if ($this->id === null)
			return false;

		if ($this->name == null)
			return false;

		if (isset($this->price) && !is_int($this->price))
		{
			$this->addError("price for choice $this->id must be an integer");
			return false;
		}

		if (isset($this->tax_percent))
		{
			if (!is_int($this->tax_percent))
				return false;

			if ($this->tax_percent < 0 || $this->tax_percent > 100)
				return false;
		}

		return true;

	}

}
