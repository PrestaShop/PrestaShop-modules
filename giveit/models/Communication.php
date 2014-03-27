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

class GiveItDataWithCookiesManager
{
	const GIVE_IT_WARNING_MESSAGE 		= 'give_it_warning_message';
	const GIVE_IT_SUCCESS_MESSAGE 		= 'give_it_success_message';
	const GIVE_IT_ERROR_MESSAGE 		= 'give_it_error_message';

	private $context;

	public function __construct()
	{
		$this->context = Context::getContext();
	}

	public function setWarningMessage($message)
	{
		if (!is_array($message))
			$this->context->cookie->__set(self::GIVE_IT_WARNING_MESSAGE, $message);
	}

	public function setSuccessMessage($message)
	{
		if (!is_array($message))
			$this->context->cookie->__set(self::GIVE_IT_SUCCESS_MESSAGE, $message);
	}

	public function setErrorMessage($message)
	{
		if (!is_array($message))
			$this->context->cookie->__set(self::GIVE_IT_ERROR_MESSAGE, $message);
	}

	public function getWarningMessage()
	{
		$message = $this->context->cookie->{self::GIVE_IT_WARNING_MESSAGE};
		$this->context->cookie->__unset(self::GIVE_IT_WARNING_MESSAGE);
		return $message ? array($message) : '';
	}

	public function getSuccessMessage()
	{
		$message = $this->context->cookie->{self::GIVE_IT_SUCCESS_MESSAGE};
		$this->context->cookie->__unset(self::GIVE_IT_SUCCESS_MESSAGE);
		return $message ? $message : '';
	}

	public function getErrorMessage()
	{
		$message = $this->context->cookie->{self::GIVE_IT_ERROR_MESSAGE};
		$this->context->cookie->__unset(self::GIVE_IT_ERROR_MESSAGE);
		return $message ? $message : '';
	}
}
