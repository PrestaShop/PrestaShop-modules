<?php
/**
 * Simplify Commerce module to start accepting payments now. It's that simple.
 *
 * Redistribution and use in source and binary forms, with or without modification, are 
 * permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above copyright notice, this list of 
 * conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of 
 * conditions and the following disclaimer in the documentation and/or other materials 
 * provided with the distribution.
 * Neither the name of the MasterCard International Incorporated nor the names of its 
 * contributors may be used to endorse or promote products derived from this software 
 * without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS' AND ANY 
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES 
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT 
 * SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
 * TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; 
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER 
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING 
 * IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF 
 * SUCH DAMAGE.
 *
 *  @author    MasterCard (support@simplify.com)
 *  @version   Release: 1.0.1
 *  @copyright 2014, MasterCard International Incorporated. All rights reserved. 
 *  @license   See licence.txt
 */

/**
 *
 * Base class for all API exceptions.
 *
 */
class SimplifyApiException extends Exception {
	protected $error_data;
	protected $status;
	protected $error_code;
	protected $reference;

	/**
	 * @ignore
	 */
	public function __construct($message, $status = null, $error_data = null)
	{
		parent::__construct($message);

		$this->status = $status;
		$this->error_code = null;
		$this->reference = null;

		if ($error_data != null)
		{
			$this->reference = $error_data['reference'];
			$this->error_data = $error_data;

			$error = $error_data['error'];
			if ($error != null)
			{
				$m = $error['message'];
				if ($m != null)
					$this->message = $m;

				$this->error_code = $error['code'];
			}
		}
	}

	/**
	 * Returns a map of all error data returned by the API.
	 * @return array a map containing API error data.
	 */
	public function getErrorData()
	{
		return $this->error_data;
	}

	/**
	 * Returns the HTTP status for the request.
	 * @return string HTTP status code (or null if there is no status).
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Returns unique reference for the API error.
	 * @return string a reference (or null if there is no reference).
	 */
	public function getReference()
	{
		return $this->reference;
	}

	/**
	 * Returns an code for the API error.
	 * @return string the error code.
	 */
	public function getErrorCode()
	{
		return $this->error_code;
	}

	/**
	 * Returns a description of the error.
	 * @return string Description of the error.
	 */
	public function describe()
	{
		return get_class($this).': \''
			.$this->getMessage().'\' (status: '
			.$this->getStatus().', error code: '
			.$this->getErrorCode().', reference: '
			.$this->getReference().')';
	}

}


/**
 * Exception raised when there are communication problems contacting the API.
 */
class SimplifyApiConnectionException extends SimplifyApiException {

}

/**
 * Exception raised where there are problems authenticating a request.
 */
class SimplifyAuthenticationException extends SimplifyApiException {

}

/**
 * Exception raised when the API request contains errors.
 */
class SimplifyBadRequestException extends SimplifyApiException {

	protected $field_errors;

	/**
	 * @ignore
	 */
	public function __construct($message, $status = null, $error_data = null)
	{
		parent::__construct($message, $status, $error_data);

		$field_errors = array();

		if ($error_data != null)
		{
			$error = $error_data['error'];
			if ($error != null)
			{
				$field_errors = $error['fieldErrors'];
				if ($field_errors != null)
				{
					$this->field_errors = array();
					foreach ($field_errors as $field_error)
						array_push($this->field_errors, new SimplifyFieldError($field_error));
				}
			}
		}
	}

	/**
	 * Returns a boolean indicating whether there are any field errors.
	 * @return boolean true if there are field errors; false otherwise.
	 */
	public function hasFieldErrors()
	{
		return count($this->field_errors) > 0;
	}

	/**
	 * Returns a list containing all field errors.
	 * @return array list of field errors.
	 */
	public function getFieldErrors()
	{
		return $this->field_errors;
	}

	/**
	 * Returns a description of the error.
	 * @return string description of the error.
	 */
	public function describe()
	{
		$s = parent::describe();
		foreach ($this->getFieldErrors() as $field_error)
			$s = $s.'\n'.(string)$field_error;

		return $s.'\n';
	}

}

/**
 * Represents a single error in a field of a request sent to the API.
 */
class SimplifyFieldError {

	protected $field;
	protected $code;
	protected $message;

	/**
	 * @ignore
	 */
	public function __construct($error_data)
	{
		$this->field = $error_data['field'];
		$this->code = $error_data['code'];
		$this->message = $error_data['message'];
	}

	/**
	 * Returns the name of the field with the error.
	 * @return string the field name.
	 */
	public function getFieldName()
	{
		return $this->field;
	}

	/**
	 * Returns the code for the error.
	 * @return string the error code.
	 */
	public function getErrorCode()
	{
		return $this->code;
	}

	/**
	 * Returns a description of the error.
	 * @return string description of the error.
	 */
	public function getMessage()
	{
		return $this->message;
	}

	public function __toString()
	{
		return 'Field error: '.$this->getFieldName().'\''.$this->getMessage().'\' ('.$this->getErrorCode().')';
	}

}

/**
 * Exception when a requested object cannot be found.
 */
class SimplifyObjectNotFoundException extends SimplifyApiException {

}

/**
 * Exception when a request was not allowed.
 */
class SimplifyNotAllowedException extends SimplifyApiException {

}

/**
 * Exception when there was a system error processing a request.
 */
class SimplifySystemException extends SimplifyApiException {

}
