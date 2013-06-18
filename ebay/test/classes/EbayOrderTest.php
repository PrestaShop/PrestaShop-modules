<?php

/*
 * 2007-2013 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
 
define('_PS_ADMIN_DIR_', getcwd());

include(dirname(__FILE__).'/../../../../config/config.inc.php');
require_once __DIR__.'/../../classes/EbayCountrySpec.php';


class EbayOrderTest extends PHPUnit_Framework_TestCase
{
	private $ebay_order;
	
  public function setUp() 
	{
		$xml = new SimpleXMLElement('
    <Order>
      <OrderID>865826</OrderID>
      <OrderStatus>Active</OrderStatus>
      <AdjustmentAmount currencyID="USD">0.0</AdjustmentAmount>
      <AmountSaved currencyID="USD">0.0</AmountSaved>
      <CheckoutStatus>
        <eBayPaymentStatus>NoPaymentFailure</eBayPaymentStatus>
        <LastModifiedTime>2007-12-10T16:09:47.000Z</LastModifiedTime>
        <PaymentMethod>None</PaymentMethod>
        <Status>Incomplete</Status>
      </CheckoutStatus>
      <ShippingDetails>
        <SalesTax>
          <SalesTaxPercent>0.0</SalesTaxPercent>
          <SalesTaxState/>
          <ShippingIncludedInTax>false</ShippingIncludedInTax>
          <SalesTaxAmount currencyID="USD">0.0</SalesTaxAmount>
        </SalesTax>
        <ShippingServiceOptions>
          <ShippingService>ShippingMethodStandard</ShippingService>
          <ShippingServicePriority>1</ShippingServicePriority>
          <ExpeditedService>false</ExpeditedService>
        </ShippingServiceOptions>
        <SellingManagerSalesRecordNumber>111</SellingManagerSalesRecordNumber>
        <GetItFast>false</GetItFast>
      </ShippingDetails>
      <CreatingUserRole>Seller</CreatingUserRole>
      <CreatedTime>2007-12-10T16:09:47.000Z</CreatedTime>
      <PaymentMethods>PayPal</PaymentMethods>
      <ShippingAddress>
        <Name>Test User</Name>
        <Street1>address</Street1>
        <Street2/>
        <CityName>city</CityName>
        <StateOrProvince>WA</StateOrProvince>
        <Country>it</Country><!-- changed by Rarbuz-->
        <CountryName/>
        <Phone>1-800-111-1111</Phone>
        <PostalCode>98102</PostalCode>
        <AddressID>3839387</AddressID>
        <AddressOwner>eBay</AddressOwner>
        <ExternalAddressID/>
      </ShippingAddress>
      <Subtotal currencyID="USD">36.0</Subtotal>
      <Total currencyID="USD">36.0</Total>
      <DigitalDelivery>false</DigitalDelivery>
      <TransactionArray>
        <Transaction>
          <Buyer>
            <Email>magicalbookseller@yahoo.com</Email>
          </Buyer>
          <ShippingDetails>
            <SellingManagerSalesRecordNumber>104</SellingManagerSalesRecordNumber>
          </ShippingDetails>
          <Item>
            <ItemID>110025788368</ItemID>
          </Item>
          <QuantityPurchased>1</QuantityPurchased>
          <TransactionID>0</TransactionID>
          <TransactionPrice currencyID="USD">18.0</TransactionPrice>
        </Transaction>
        <Transaction>
          <Buyer>
            <Email>magicalbookseller@yahoo.com</Email>
          </Buyer>
          <ShippingDetails>
            <SellingManagerSalesRecordNumber>103</SellingManagerSalesRecordNumber>
          </ShippingDetails>
          <Item>
            <ItemID>110025788765</ItemID>
          </Item>
          <QuantityPurchased>1</QuantityPurchased>
          <TransactionID>0</TransactionID>
          <TransactionPrice currencyID="USD">18.0</TransactionPrice>
        </Transaction>
      </TransactionArray>
      <BuyerUserID>magicalbookseller</BuyerUserID>
    </Order>
  ');
		
		$this->ebay_order = new EbayOrder($xml);
  }
	
  public function testIsCompleted() 
	{
		$this->assertTrue(is_bool($this->ebay_order->isCompleted()));
  }

  public function testExists() 
	{
		$this->assertTrue(is_bool($this->ebay_order->exists()));
  }

  public function testHasValidContact() 
	{
		$this->assertTrue($this->ebay_order->hasValidContact());
  }

  public function testGetOrAddCustomer()
	{
		$res = $this->ebay_order->getOrAddCustomer();
		$this->assertTrue(is_int($res));
		$this->assertTrue($res > 0);
  }
	
	public function testUpdateOrAddAddress()
	{
		$this->ebay_order->getOrAddCustomer();
		$res = $this->ebay_order->updateOrAddAddress();
		$this->assertTrue(is_int($res));
		$this->assertTrue($res > 0);		
	}
	
	public function testHasAllProductsWithAttributes()
	{
		$res = $this->ebay_order->hasAllProductsWithAttributes();
		$this->assertTrue(is_bool($res));
	}

	public function testAddCart()
	{
		$res = $this->ebay_order->addCart(new EbayCountrySpec());
		$this->assertEquals(get_class($res), 'Cart');
	}

	public function testDeleteCart()
	{
		$this->ebay_order->addCart(new EbayCountrySpec());
		$res = $this->ebay_order->deleteCart();
	}
	
	public function testUpdateCartQuantities()
	{
		$this->ebay_order->addCart(new EbayCountrySpec());
		$res = $this->ebay_order->updateCartQuantities();
		$this->assertTrue(is_bool($res));		
	}
	
	public function testValidate()
	{
		$this->ebay_order->getOrAddCustomer();
		$this->ebay_order->updateOrAddAddress();
		$this->ebay_order->addCart(new EbayCountrySpec());		
		//$res = $this->ebay_order->validate(); RArbuz: not working because of delivery options not properly set
	}
	
	public function testUpdatePrice()
	{
		$this->ebay_order->getOrAddCustomer();
		$this->ebay_order->addCart(new EbayCountrySpec());		
		//$this->ebay_order->validate(); RArbuz: not working because of delivery options not properly set
		$this->ebay_order->updatePrice();
	}
	
	public function testAdd()
	{
		$this->ebay_order->add();
	}
	
}
