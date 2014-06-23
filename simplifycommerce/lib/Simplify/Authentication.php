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
 * SimplifyAuthentication - encapsulates the credentials needed to make a request to the Simplify API.
 *
 * <p>$public_key - this is your API public key
 * <p>$private_key - this is your API private key
 * <p>$access_token - Oauth access token that is needed to make API requests on behalf of another user
 * <p>
 * <p>
 * <code>new SimplifyAuthentication($access_token)</code>
 *
 * <p>
 * <code>new SimplifyAuthentication($public_key, $private_key)</code>
 *
 * <p>
 * <code>new SimplifyAuthentication($public_key, $private_key, $access_token)</code>
 */
class SimplifyAuthentication {

	public $private_key;
	public $public_key;
	public $access_token;

	public function __construct()
	{
		$args = func_get_args();
		switch (func_num_args())
		{
			case 1:
				self::construct1( $args[0] );
				break;
			case 2:
				self::construct2( $args[0], $args[1] );
				break;
			case 3:
				self::construct3( $args[0], $args[1], $args[2] );
		}
	}

	private function construct1($access_token)
	{
		$this->access_token = $access_token;
	}

	private function construct2($public_key, $private_key)
	{
		$this->public_key = $public_key;
		$this->private_key = $private_key;
	}

	private function construct3($public_key, $private_key, $access_token)
	{
		$this->public_key = $public_key;
		$this->private_key = $private_key;
		$this->access_token = $access_token;
	}
}