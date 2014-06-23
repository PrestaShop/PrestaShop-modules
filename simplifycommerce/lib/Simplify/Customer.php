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

class SimplifyCustomer extends SimplifyObject {
	/**
	 * Creates an SimplifyCustomer object
	 * @param     array $hash a map of parameters; valid keys are:<dl style='padding-left:10px;'>
	 *     <dt><tt>card.addressCity</tt></dt>    <dd>City of the cardholder. <strong>required </strong></dd>
	 *     <dt><tt>card.addressCountry</tt></dt>    <dd>Country code (ISO-3166-1-alpha-2 code) of residence of the cardholder.
	 *  <strong>required </strong></dd>
	 *     <dt><tt>card.addressLine1</tt></dt>    <dd>Address of the cardholder <strong>required </strong></dd>
	 *     <dt><tt>card.addressLine2</tt></dt>    <dd>Address of the cardholder if needed. <strong>required </strong></dd>
	 *     <dt><tt>card.addressState</tt></dt>    <dd>State code (USPS code) of residence of the cardholder. <strong>required </strong></dd>
	 *     <dt><tt>card.addressZip</tt></dt>    <dd>Postal code of the cardholder. <strong>required </strong></dd>
	 *     <dt><tt>card.cvc</tt></dt>    <dd>CVC security code of the card. This is the code on the back of the card. Example: 123
	 *  <strong>required </strong></dd>
	 *     <dt><tt>card.expMonth</tt></dt>    <dd>Expiration month of the card. Format is MM. Example: January = 01 <strong>required </strong></dd>
	 *     <dt><tt>card.expYear</tt></dt>    <dd>Expiration year of the card. Format is YY. Example: 2013 = 13 <strong>required </strong></dd>
	 *     <dt><tt>card.id</tt></dt>    <dd>ID of card. Unused during customer create. </dd>
	 *     <dt><tt>card.name</tt></dt>    <dd>Name as appears on the card. <strong>required </strong></dd>
	 *     <dt><tt>card.number</tt></dt>    <dd>Card number as it appears on the card. </dd>
	 *     <dt><tt>email</tt></dt>    <dd>Email address of the customer <strong>required </strong></dd>
	 *     <dt><tt>name</tt></dt>    <dd>Customer name <strong>required </strong></dd>
	 *     <dt><tt>reference</tt></dt>    <dd>Reference field for external applications use. </dd>
	 *     <dt><tt>subscriptions.amount</tt></dt>    <dd>Amount of payment in minor units. Example: 1000 = 10.00 </dd>
	 *     <dt><tt>subscriptions.coupon</tt></dt>    <dd>Coupon associated with the subscription for the customer. </dd>
	 *     <dt><tt>subscriptions.currency</tt></dt>    <dd>Currency code (ISO-4217). Must match the currency associated with your
	 *  account. <strong>default:USD</strong></dd>
	 *     <dt><tt>subscriptions.customer</tt></dt>    <dd>The customer ID to create the subscription for. Do not supply this
	 *  when creating a customer. </dd>
	 *     <dt><tt>subscriptions.frequency</tt></dt>    <dd>Frequency of payment for the plan. Example: Monthly </dd>
	 *     <dt><tt>subscriptions.name</tt></dt>    <dd>Name describing subscription </dd>
	 *     <dt><tt>subscriptions.plan</tt></dt>    <dd>The plan ID that the subscription should be created from. </dd>
	 *     <dt><tt>subscriptions.quantity</tt></dt>    <dd>Quantity of the plan for the subscription. </dd>
	 *     <dt><tt>token</tt></dt>    <dd>If specified, card associated with card token will be used </dd></dl>
	 * @param     $authentication -  information used for the API call.  If no value is passed the global keys Simplify::public_key and
	 *  Simplify::private_key are used.  <i>For backwards compatibility the public and
	 * private keys may be passed instead of the authentication object.<i/>
	 * @return    Customer a Customer object.
	 */
	public static function createCustomer($hash, $authentication = null)
	{
		$args = func_get_args();
		$authentication = SimplifyPaymentsApi::buildAuthenticationObject($authentication, $args, 2);

		$instance = new SimplifyCustomer();
		$instance->setAll($hash);

		$object = SimplifyPaymentsApi::createObject($instance, $authentication);
		return $object;
	}

	/**
	* Deletes an SimplifyCustomer object.
	*
	* @param     $authentication -  information used for the API call.  If no value is passed the global keys Simplify::public_key
	* and Simplify::private_key are used.  <i>For backwards compatibility the public and private keys may be passed
	* instead of the authentication object.</i>
	*/
	public function deleteCustomer($authentication = null)
	{
		$args = func_get_args();
		$authentication = SimplifyPaymentsApi::buildAuthenticationObject($authentication, $args, 1);

		SimplifyPaymentsApi::deleteObject($this, $authentication);
		$this->properties = null;
		return true;
	}


	/**
	* Retrieve SimplifyCustomer objects.
	* @param     array criteria a map of parameters; valid keys are:<dl style='padding-left:10px;'>
	*     <dt><tt>filter</tt></dt>    <dd>Filters to apply to the list.  </dd>
	*     <dt><tt>max</tt></dt>    <dd>Allows up to a max of 50 list items to return.  <strong>default:20</strong></dd>
	*     <dt><tt>offset</tt></dt>    <dd>Used in paging of the list.  This is the start offset of the page.  <strong>default:0</strong></dd>
	*     <dt><tt>sorting</tt></dt>    <dd>Allows for ascending or descending sorting of the list.  The value maps properties to the sort direction
	* (either <tt>asc</tt> for ascending or <tt>desc</tt> for descending).  Sortable properties are: <tt> dateCreated</tt><tt> id</tt><tt> name</tt>
	* <tt> email</tt><tt> reference</tt>.</dd></dl>
	* @param     $authentication -  information used for the API call.  If no value is passed the global keys Simplify::public_key and
	* Simplify::private_key are used.  <i>For backwards compatibility the public and private keys may be passed instead of the authentication object.</i>
	* @return    ResourceList a ResourceList object that holds the list of Customer objects and the total
	*            number of Customer objects available for the given criteria.
	* @see       ResourceList
	*/
	public static function listCustomer($criteria = null, $authentication = null)
	{
		$args = func_get_args();
		$authentication = SimplifyPaymentsApi::buildAuthenticationObject($authentication, $args, 2);

		$val = new SimplifyCustomer();
		$list = SimplifyPaymentsApi::listObject($val, $criteria, $authentication);

		return $list;
	}


	/**
	 * Retrieve a SimplifyCustomer object from the API
	 *
	 * @param     string id  the id of the Customer object to retrieve
	 * @param     $authentication -  information used for the API call. If no value is passed the global keys Simplify::public_key and
	 * Simplify::private_key are used.  <i>For backwards compatibility the public and
	 * private keys may be passed instead of the authentication object.</i>
	 * @return    Customer a Customer object
	 */
	public static function findCustomer($id, $authentication = null)
	{
		$args = func_get_args();
		$authentication = SimplifyPaymentsApi::buildAuthenticationObject($authentication, $args, 2);

		$val = new SimplifyCustomer();
		$val->id = $id;

		$obj = SimplifyPaymentsApi::findObject($val, $authentication);

		return $obj;
	}


	/**
	 * Updates an SimplifyCustomer object.
	 *
	 * The properties that can be updated:
	 * <ul>
	 * <li>card.addressCity <strong>(required)</strong></li>
	 * <li>card.addressCountry <strong>(required)</strong></li>
	 * <li>card.addressLine1 <strong>(required)</strong></li>
	 * <li>card.addressLine2 <strong>(required)</strong></li>
	 * <li>card.addressState <strong>(required)</strong></li>
	 * <li>card.addressZip <strong>(required)</strong></li>
	 * <li>card.cvc <strong>(required)</strong></li>
	 * <li>card.expMonth <strong>(required)</strong></li>
	 * <li>card.expYear <strong>(required)</strong></li> 
	 * <li>card.id </li> 
	 * <li>card.name <strong>(required)</strong></li>
	 * <li>card.number </li>
	 * <li>email <strong>(required)</strong></li>
	 * <li>name <strong>(required)</strong></li>
	 * <li>reference </li> 
	 * <li>token </li>
	 * </ul>
	 * @param     $authentication -  information used for the API call. If no value is passed the global keys Simplify::public_key and
	 * Simplify::private_key are used.  <i>For backwards compatibility the public and
	 * private keys may be passed instead of the authentication object.</i>
	 * @return    Customer a Customer object.
	 */
	public function updateCustomer($authentication = null)
	{
		$args = func_get_args();
		$authentication = SimplifyPaymentsApi::buildAuthenticationObject($authentication, $args, 1);

		$object = SimplifyPaymentsApi::updateObject($this, $authentication);
		return $object;
	}

	/**
	 * @ignore
	 */
	public function getClazz()
	{
		return 'Customer';
	}
}