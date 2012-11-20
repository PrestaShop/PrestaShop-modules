<?php
/**
 * GetTaxRequest.class.php
 */

/**
 * Data to pass to {@link TaxServiceSoap#getTax}.
 *
 * @see GetTaxResult
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */
class GetTaxRequest
{
	private $CompanyCode; // string
	private $DocCode;
	private $DocType;
	private $DocDate;				//date
	private $SalespersonCode;		//string
	private $CustomerCode;			//string
	private $CustomerUsageType;		//string   Entity Usage
	private $Discount;				//decimal
	private $PurchaseOrderNo;		//string
	private $ExemptionNo;			//string   if not using ECMS which keys on customer code
	private $OriginCode;			//string
	private $DestinationCode;		//string
	private $Addresses;				//array
	private $Lines;					//array
	private $DetailLevel;			//Summary or Document or Line or Tax or Diagnostic
	private $ReferenceCode; 		// string
	private $LocationCode;			//string
	private $Commit = false;			//boolean
	private $BatchCode;				//string
	private $OriginAddress;			//address
	private $DestinationAddress;		//address

	// @author: Swetal
	// Added new properties to upgrade to 5.3 interface
	private $TaxOverride;		//TaxOverride (object)
	private $CurrencyCode;		//string
	private $ServiceMode;		//type: ServiceMode
	private $PaymentDate;		//date
	private $ExchangeRate;		//decimal
	private $ExchangeRateEffDate;	//date

	public function __construct()
	{
		$this->DocDate = date('Y-m-d');
		$this->Commit = false;
		$this->HashCode = 0;
		$this->Discount = 0.0;
		$this->DocType = DocumentType::$SalesInvoice;
		$this->DetailLevel = DetailLevel::$Document;
		$this->DocCode = date("Y-m-d-H-i-s.u");
		$this->CustomerCode='CustomerCodeString';
		$this->Lines = array(new Line());

		$this->ServiceMode = ServiceMode::$Automatic;
		$this->ExchangeRate = 1.0;

		$dateTime = new DateTime();
		$dateTime->setDate(1900,01,01);
		$this->ExchangeRateEffDate=$dateTime->format('Y-m-d');
		$this->PaymentDate=$dateTime->format('Y-m-d');
	}

	public function prepare()
	{
		$this->Addresses = array();
		$this->OriginCode = $this->registerAddress($this->OriginAddress);
		$this->DestinationCode = $this->registerAddress($this->DestinationAddress);
		foreach($this->Lines as &$line)
			$line->registerAddressesIn($this);
		return $this;
	}

	public function registerAddress(&$address)
	{
		if ($address == null) { return null; }
		$index = sizeof($this->Addresses);
		foreach($this->Addresses as $index=>$a)
			if($address->equals($a))
				return $index;
		$index = sizeof($this->Addresses);
		$this->Addresses[] = $address;
		$address->setAddressCode($index);
		return $index;
	}

	public function postFetch()
	{
		$addresses = $this->getAddresses();
		$this->OriginAddress = (isset($addresses[$this->OriginCode]) ? $addresses[$this->OriginCode] : null);
		$this->DestinationAddress = (isset($addresses[$this->DestinationCode]) ? $addresses[$this->DestinationCode] : null);

		//@author: Swetal
		//Commenting following foreach loop
		//Reason is postFetch function is called after getTaxHistory to populate origin and destination address
		//but taxHistory does not return origin and destination code so with followign loop we can not retrive origin
		//and destination adress for line. This gives invalid index error if not commented
		/*foreach($this->getLines() as $line)
			{
			$line->postFetchWithAddresses($addresses);
			}*/

		return $this;
	}

	/**
	 * Enter description here...
	 *
	 * @param string $value
	 */
	public function setAddressCode($value) { $this->AddressCode = $value; return $this; }

	/**
	 * Enter description here...
	 *
	 * @param string $value
	 */
	public function setLine1($value) { $this->Line1 = $value; return $this; }

	/**
	 * Enter description here...
	 *
	 * @param string $value
	 */
	public function setLine2($value) { $this->Line2 = $value; return $this; }

	/**
	 * Enter description here...
	 *
	 * @param string $value
	 */
	public function setLine3($value) { $this->Line3 = $value; return $this; }

	/**
	 * Enter description here...
	 *
	 * @param string $value
	 */
	public function setCity($value) { $this->City = $value; return $this; }

	/**
	 * Enter description here...
	 *
	 * @param string $value
	 */
	public function setRegion($value) { $this->Region = $value; return $this; }

	/**
	 * Enter description here...
	 *
	 * @param string $value
	 */
	public function setPostalCode($value) { $this->PostalCode = $value; return $this; }

	/**
	 * Enter description here...
	 *
	 * @param string $value
	 */
	public function setCountry($value) { $this->Country = $value; return $this; }

/**
 * Mutator
 * @access public
 * @param integer
 */
	/**
	 * DocDate should be in the format yyyy-mm-dd
	 *
	 * @param date $value
	 */
	public function setDocDate($value) { $this->DocDate = $value; return $this; }				//date

	/**
	 * Sets the client application company reference code.
	 *
	 * @param string $value
	 *
	 */
	public function setCompanyCode($value) { $this->CompanyCode = $value; return $this; }			//string

	/**
	 * The document type specifies the category of the document and affects how the document is treated after a tax calculation; see DocumentType for more information about the specific document types.
	 *
	 * @param DocumentType $value
	 *
	 */
	public function setDocType($value) { DocumentType::Validate($value); $this->DocType = $value; return $this; }				//SalesOrder or SalesInvoice or PurchaseOrder or PurchaseInvoice or ReturnOrder or ReturnInvoice

	/**
	 * Specifies the level of detail to return.
	 *
	 * @param DetailLevel $value

	 */
	public function setDetailLevel($value) { DetailLevel::Validate($value); $this->DetailLevel = $value; return $this; }			//Summary or Document or Line or Tax or Diagnostic - enum

	/**
	 * Sets the Document Code, i.e. the internal reference code used by the client application.
	 *
	 * @param string $value

	 */
	public function setDocCode($value) { $this->DocCode = $value; return $this; }				//string   invoice number

	/**
	 * The client application salesperson reference code.
	 *
	 * @param string $value

	 */
	public function setSalespersonCode($value) { $this->SalespersonCode = $value; return $this; }		//string

	/**
	 * The client application customer reference code.
	 *
	 * @param string $value

	 */
	public function setCustomerCode($value) { $this->CustomerCode = $value; return $this; }			//string

	/**
	 * The client application customer or usage type.
	 * <p>
	 * This is used to determine the exempt status of the transaction based on the exemption tax rules for the
	 * jurisdictions involved.  This may also be set at the line level.
	 * </p>
	 * <p>
	 * The standard values for the CustomerUsageType (A-L).<br/>
	 A – Federal Government<br/>
	 B – State/Local Govt.<br/>
	 C – Tribal Government<br/>
	 D – Foreign Diplomat<br/>
	 E – Charitable Organization<br/>
	 F – Religious/Education<br/>
	 G – Resale<br/>
	 H – Agricultural Production<br/>
	 I – Industrial Prod/Mfg.<br/>
	 J – Direct Pay Permit<br/>
	 K – Direct Mail<br/>
	 L - Other<br/>
	 * </p>
	 * @param customerUsageType
	 */
	public function setCustomerUsageType($value) { $this->CustomerUsageType = $value; return $this; }		//string   Entity Usage

	/**
	 *Purchase Order Number for this document.
	 *
	 * @param string $value

	 */
	public function setPurchaseOrderNo($value) { $this->PurchaseOrderNo = $value; return $this; }		//string

	/**
	 * Exemption Number for this document
	 *
	 * @param string $value
	 */
	public function setExemptionNo($value) { $this->ExemptionNo = $value; return $this; }			//string   if not using ECMS which keys on customer code

	/**
	 * Also referred to as a Store Location, Outlet Id, or Outlet code is a number assigned by the State which identifies a Store location. Some state returns require taxes are broken out separatly for Store Locations.
	 *
	 * @param string $value
	 */
	public function setLocationCode($value) { $this->LocationCode = $value; return $this; }		//string

	public function setBatchCode($value) { $this->BatchCode = $value; return $this; }				//string

	/**
	 * The discount amount to apply to the document.
	 *
	 * @param decimal $value
	 */
	public function setDiscount($value) { $this->Discount = $value; return $this; }				//decimal
	//public function setTotalTaxOverride($value) { $this->TotalTaxOverride = $value; return $this; }		//decimal

	/**
	 * Set addresses
	 *
	 * @param array $value
	 */
	public function setAddresses($value) { $this->Addresses = $value; return $this; }		//array

	/**
	 * Set tax lines
	 *
	 * @param array $value
	 */
	public function setLines($value) { $this->Lines = $value; return $this; }				//array

	public function setHashCode($value) { $this->HashCode = $value; return $this; }				//int

	/**
	 * This has been defaulted to False ; invoice will be committed if this flag has been set to True.
	 *
	 * @param boolean $value
	 *
	 */
	public function setCommit($value) { $this->Commit = $value; return $this; }						//boolean

	//public function setIsTotalTaxOverriden($value) { $this->IsTotalTaxOverriden = ($value ? true : false); return $this; }	//boolean
	/**
	 * Set ship from address
	 *
	 * @param Address $value

	 */
	public function setOriginAddress($value) { $this->OriginAddress = $value; return $this; }				//address

	/**
	 * set ship to address
	 *
	 * @param Address $value

	 */
	public function setDestinationAddress($value) { $this->DestinationAddress = $value; return $this; }	//address

	//@author:swetal

	/**
	 * ExchangeRate indicates the currency exchange rate from the transaction currency (indicated by CurrencyCode) to the company base currency.
	 * This only needs to be set if the transaction currency is different than the company base currency. It defaults to 1.0.
	 *
	 * @param decimal $value

	 */
	public function setExchangeRate($value) { $this->ExchangeRate = $value; return $this; }				//decimal

	/**
	 * 3 character ISO 4217 currency code.
	 *
	 * @param string $value

	 */
	public function setCurrencyCode($value) { $this->CurrencyCode = $value; return $this; }		//string

	/**
	 * It provides the ability to controls whether tax is calculated locally or remotely when using an AvaLocal server.
	 * The default is Automatic which calculates locally unless remote is necessary for non-local addresses.
	 *
	 * @param ServiceMode $value
	 */
	public function setServiceMode($value)  { $this->ServiceMode = $value; return $this; }		//type: ServiceMode

	/**
	 * PaymentDate indicates the date when payment was received for the document. It is only applicable for cash-basis accounting and does not need to be set otherwise.
	 * It defaults to 1/1/1900 which indicates no payment. The new TaxSvc.ApplyPayment method may be used to apply a payment to an existing invoice.
	 *
	 * @param date $value

	 */
	public function setPaymentDate($value)  { $this->PaymentDate = $value; return $this; }		//date

	/**
	 * ExchangeRateEffDate indicates the effective date of the exchange rate.
	 * It should be set in conjunction with ExchangeRate. It will default to the DocDate if not set.
	 *
	 * @param date $value

	 */
	public function setExchangeRateEffDate($value){ $this->ExchangeRateEffDate = $value; return $this; }	//date

	/**
	 *TaxOverride for the document.
	 *
	 * @param TaxOverride $value

	 */
	public function setTaxOverride($value){ $this->TaxOverride=$value;}	//tax override

	public function setReferenceCode($value)
	{
		$this->ReferenceCode=$value;
	}

/**#@+
 * Accessor
 * @access public
 * @return string
 */

	public function getCompanyCode() { return $this->CompanyCode;}			//string
	public function getDocType() { return $this->DocType;}				//SalesOrder or SalesInvoice or PurchaseOrder or PurchaseInvoice or ReturnOrder or ReturnInvoice
	public function getDocCode() { return $this->DocCode;}				//string   invoice number
	public function getDocDate() { return $this->DocDate;}				//date
	public function salespersonCode() { return $this->SalespersonCode;}		//string
	public function getCustomerCode() { return $this->CustomerCode;}			//string
	public function getCustomerUsageType() { return $this->CustomerUsageType;}		//string   Entity Usage

	public function getDiscount() { return $this->Discount;}				//decimal
	//public function getTotalTaxOverride() { return $this->TotalTaxOverride;}		//decimal

	public function getPurchaseOrderNo() { return $this->PurchaseOrderNo;}		//string
	public function getExemptionNo() { return $this->ExemptionNo;}			//string   if not using ECMS which keys on customer code
	public function getAddresses()
	{
		// this is kind of icky
		// when we build one of these to send, it is an array of Address
		// however, when it is fetched, there is an extra std::Object stuck in place to represent the array
		// which contains the array in an ivar called Address.  Such are the vagaries of
		// the php SoapClient.

		//@swetal
		//Changed from $this->Addresses to $this->Addresses->BaseAddress

		if (is_array($this->Addresses))
			return $this->Addresses;
		else if (is_object($this->Addresses) && isset($this->Addresses->BaseAddress))
			return EnsureIsArray($this->Addresses->BaseAddress);
		return null;






	}				//array
	public function getLines()
	{
		return is_array($this->Lines) ? $this->Lines : EnsureIsArray($this->Lines->Line);
	}					//array
	public function getDetailLevel() { return $this->DetailLevel;}			//Summary or Document or Line or Tax or Diagnostic  ********************************** make class

	public function getHashCode() { return $this->HashCode;}				//int
	public function getLocationCode() { return $this->LocationCode;}			//string
	public function getBatchCode() { return $this->BatchCode;}				//string

	public function getCommit() { return $this->Commit;}			//boolean
	//public function getIsTotalTaxOverriden() { return $this->IsTotalTaxOverriden;}	//boolean


	public function getOriginAddress() { return $this->OriginAddress;}			//address
	public function getDestinationAddress() { return $this->DestinationAddress;}		//address

	//@author:swetal
	public function getExchangeRate() { return $this->ExchangeRate; }				//decimal
	public function getCurrencyCode() { return $this->CurrencyCode; }		//string
	public function getServiceMode()  { return $this->ServiceMode; }		//type: ServiceMode
	public function getPaymentDate()  { return $this->PaymentDate; }		//date
	public function getExchangeRateEffDate(){ return $this->ExchangeRateEffDate; }	//date
	public function getTaxOverride(){ return $this->TaxOverride;}

	public function getReferenceCode()
	{
		return $this->ReferenceCode;
	}

	//@author:swetal
	//Adding getLine function which returns line based on line number
	public function getLine($lineNo)
	{
		if($this->Lines != null)
		{
			foreach($this->getLines() as $line)
			{
				if($lineNo == $line->getNo())
				{
					return $line;
				}

			}
		}
	}


/**#@-*/



}

?>