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
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY 
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

require_once(dirname(__FILE__).'/Simplify/Constants.php');

class Simplify
{
	/**
	* @var string $public_key public API key used to authenticate requests.
	*/
	public static $public_key;

	/**
	* @var string $private_key private API key used to authenticate requests.
	*/
	public static $private_key;


	/**
	* @var string $api_base_live_url URL of the live API endpoint
	*/
	public static $api_base_live_url = SimplifyConstants::API_BASE_LIVE_URL;

	/**
	* @var string $api_base_sandbox_url URL of the sandbox API endpoint
	*/
	public static $api_base_sandbox_url = SimplifyConstants::API_BASE_SANDBOX_URL;

	/**
	* @var string $user_agent User-agent string send with requests.
	*/
	public static $user_agent = null;

}

require_once(dirname(__FILE__).'/Simplify/Object.php');
require_once(dirname(__FILE__).'/Simplify/Authentication.php');
require_once(dirname(__FILE__).'/Simplify/PaymentsApi.php');
require_once(dirname(__FILE__).'/Simplify/Exceptions.php');
require_once(dirname(__FILE__).'/Simplify/Http.php');
require_once(dirname(__FILE__).'/Simplify/ResourceList.php');
require_once(dirname(__FILE__).'/Simplify/Customer.php');
require_once(dirname(__FILE__).'/Simplify/Payment.php');