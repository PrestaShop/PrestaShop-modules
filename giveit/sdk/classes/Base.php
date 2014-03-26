<?php
/**
 * @copyright    give.it 2013
 * @author       David Kelly
 *
 * required:
 * - PHP > 5.3.0
 * - libmcrypt >= 2.4.x
 */

class GiveItSdkBase {
	protected $errors = array();

	protected function addError($message)
	{
		if (is_array($message))
		{
			foreach ($message as $value)
				$this->addError($value);

			return true;
		}

		$this->errors[] = $message;

		//  trigger_error('Give.it SDK: ' . $message);

		return true;
	}

	public function hasErrors()
	{
		return !empty($this->errors);
	}

	public function errors()
	{
		return $this->errors;
	}

	/**
	 * convert data from a multimensional array to an array in key value format, as in:
	 *
	 * report.recipients.0  => 'someone@give.it'
	 *
	 * @param array $data
	 * @param string $prefix
	 * @param array $flat                           passed by the method for recursion, do not specify this when calling
	 */
	public function flatten($data, $prefix = null, &$flat = array())
	{
		if ($prefix != null)
			$prefix = $prefix.':';

		foreach ($data as $key => $val)
		{
			if (is_array($val))
			{
				$this->flatten($val, $prefix.$key, $flat);

			} else {

				$flat[$prefix.$key] = $val;
			}
		}

		return $flat;
	}

}
