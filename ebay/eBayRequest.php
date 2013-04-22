<?php

/*
* 2007-2011 PrestaShop 
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
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/


class eBayRequest
{
	public $response;
	public $token;
	public $expiration;

	public $runame;
	public $username;
	public $session;

	public $itemID;
	public $fees;
	public $error;
	public $errorCode;

	private $devID;
	private $appID;
	private $certID;

	private $siteID;
	private $apiUrl;
	private $apiCall;

	private $loginUrl;

	private $findingUrl;
	private $findingVersion;

	private $compatibilityLevel;

	private $debug = false;
	private $dev = false;

	
	private $country;
	private $language;
	private $siteName;
	private $siteExtension;

	/******************************************************************/
	/** Constructor And Request Methods *******************************/
	/******************************************************************/


	public function __construct($apiCall = '')
	{
		$this->country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));
	
		if(strtolower($this->country->iso_code) == 'it')
			{
			$this->siteID = 101;
			$this->language = 'it_IT';
			$this->siteName = 'Italy';
			$this->siteExtension = 'it';
			}
		else
			{
			$this->siteID = 71;
			$this->language = 'fr_FR';
			$this->siteName = 'France';
			$this->siteExtension = 'fr';
			}
		
		/*** SAND BOX PARAMS ***/
		
		if($this->dev)
		{
			$this->devID = '1db92af1-2824-4c45-8343-dfe68faa0280';
			$this->appID = 'Prestash-2629-4880-ba43-368352aecc86';
			$this->certID = '6bd3f4bd-3e21-41e8-8164-7ac733218122';

			$this->apiUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
			$this->apiCall = $apiCall;
			$this->compatibilityLevel = 719;

			$this->runame = 'Prestashop-Prestash-2629-4-hpehxegu';

			$this->loginURL = 'https://signin.sandbox.ebay.'.$this->siteExtension.'/ws/eBayISAPI.dll';
		}
		else
		{
			$this->devID = '1db92af1-2824-4c45-8343-dfe68faa0280';
			$this->appID = 'Prestash-70a5-419b-ae96-f03295c4581d';
			$this->certID = '71d26dc9-b36b-4568-9bdb-7cb8af16ac9b';

			$this->apiUrl = 'https://api.ebay.com/ws/api.dll';
			$this->apiCall = $apiCall;
			$this->compatibilityLevel = 741;

			$this->runame = 'Prestashop-Prestash-70a5-4-pepwa';
			$this->loginURL = 'https://signin.ebay.'.$this->siteExtension.'/ws/eBayISAPI.dll';
		}

		
	}


	public function makeRequest($request, $shoppingEndPoint = false)
	{
		// Init
		$connection = curl_init();
		curl_setopt($connection, CURLOPT_URL, $this->apiUrl);

		curl_setopt($connection, CURLINFO_HEADER_OUT, true);
		// Stop CURL from verifying the peer's certificate
		curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
		
		// Set the headers (Different headers depending on the api call !)
		if($shoppingEndPoint)
		{
			curl_setopt($connection, CURLOPT_HTTPHEADER, $this->buildHeadersShopping());
		}
		else
		{
			curl_setopt($connection, CURLOPT_HTTPHEADER, $this->buildHeaders());
		}
		curl_setopt($connection, CURLOPT_POST, 1);	
		
		// Set the XML body of the request
		curl_setopt($connection, CURLOPT_POSTFIELDS, $request);
		
		// Set it to return the transfer as a string from curl_exec
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        
		// Send the Request
		$response = curl_exec($connection);

		// Close the connection
		curl_close($connection);
		
		// Debug
		if ($this->debug == true)
		{
			if (!file_exists(dirname(__FILE__).'/log/request.php'))
				file_put_contents(dirname(__FILE__).'/log/request.php', "<?php\n\n", FILE_APPEND | LOCK_EX);
			file_put_contents(dirname(__FILE__).'/log/request.php', date('d/m/Y H:i:s')."\n\n".$request."\n\n".$response."\n\n-------------------\n\n", FILE_APPEND | LOCK_EX); 
		}

		// Return the response
		return $response;
	}


	private function buildHeadersShopping()
	{
		$headers = array (
			'X-EBAY-API-APP-ID:'.$this->appID,
			'X-EBAY-API-VERSION:'.$this->compatibilityLevel,
			'X-EBAY-API-SITE-ID:'.$this->siteID,
			'X-EBAY-API-CALL-NAME:'.$this->apiCall,
			'X-EBAY-API-REQUEST-ENCODING:XML', 

			//For api call on a different endpoint we need to add the content type 
			'Content-type:text/xml;charset=utf-8'
		);

		return $headers;
	}

	private function buildHeaders()
	{
		$headers = array (
			// Regulates versioning of the XML interface for the API
			'X-EBAY-API-COMPATIBILITY-LEVEL: '.$this->compatibilityLevel,
			
			// Set the keys
			'X-EBAY-API-DEV-NAME: '.$this->devID,
			'X-EBAY-API-APP-NAME: '.$this->appID,
			'X-EBAY-API-CERT-NAME: '.$this->certID,
			
			// The name of the call we are requesting
			'X-EBAY-API-CALL-NAME: '.$this->apiCall,
			
			//SiteID must also be set in the Request's XML
			//SiteID = 0  (US) - UK = 3, Canada = 2, Australia = 15, ....
			//SiteID Indicates the eBay site to associate the call with
			'X-EBAY-API-SITEID: '.$this->siteID,
		);

		return $headers;
	}


	/******************************************************************/
	/** Authentication Methods ****************************************/
	/******************************************************************/


	function fetchToken()
	{
		// Set Api Call
        	$this->apiCall = 'FetchToken';

		$requestXml = '<?xml version="1.0" encoding="utf-8" ?>';
		$requestXml .= '<FetchTokenRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
		$requestXml .= '<RequesterCredentials><Username>'.$this->username.'</Username></RequesterCredentials>';
		$requestXml .= '<SessionID>'.$this->session.'</SessionID>';
		$requestXml .= '</FetchTokenRequest>';

		// Send the request and get response
		$responseXml = $this->makeRequest($requestXml);
		if (stristr($responseXml, 'HTTP 404') || $responseXml == '')
		{
			$this->error = 'Error sending '.$this->apiCall.' request';
			return false;
		}

		// Saving Datas // Need to cast token var to string (not SimpleXML element) to persist in SESSION
	        $this->response = simplexml_load_string($responseXml);
        	$this->token = (string)$this->response->eBayAuthToken;
	        $this->expiration = $this->response->HardExpirationTime;
	}

	function getLoginUrl()
	{
		return $this->loginURL;
	}

	function login()
	{
		// Set Api Call
		$this->apiCall = 'GetSessionID';

		///Build the request Xml string
		$requestXml = '<?xml version="1.0" encoding="utf-8" ?>';
		$requestXml .= '<GetSessionIDRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
		$requestXml .= '<Version>'.$this->compatibilityLevel.'</Version>';
		$requestXml .= '<RuName>'.$this->runame.'</RuName>';
		$requestXml .= '</GetSessionIDRequest>';

		// Send the request and get response
		$responseXml = $this->makeRequest($requestXml);
		if (stristr($responseXml, 'HTTP 404') || $responseXml == '')
		{
			$this->error = 'Error sending '.$this->apiCall.' request';
			return false;
		}

	        $this->response = simplexml_load_string($responseXml);
		$this->session = (string)$this->response->SessionID;
	}

	/******************************************************************/
	/**** Get User Informations ***************************************/
	/******************************************************************/


	function getUserProfile()
	{

		// Set Api Call
		$this->apiCall = 'GetUserProfile';
		//Change API URL
		$apiUrl = $this->apiUrl;
		$this->apiUrl = ($this->dev) ? 'http://open.api.sandbox.ebay.com/shopping?' : 'http://open.api.ebay.com/shopping?';



		///Build the request Xml string
		$requestXml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$requestXml .= '<GetUserProfileRequest xmlns="urn:ebay:apis:eBLBaseComponents">'."\n";
		$requestXml .= '  <UserID>'.$this->username.'</UserID>'."\n";
		$requestXml .= '  <IncludeSelector>Details</IncludeSelector>'."\n";
		$requestXml .= '  <ErrorLanguage>'.$this->language.'</ErrorLanguage>'."\n";
		$requestXml .= '  <Version>719</Version>'."\n";
		$requestXml .= '  <WarningLevel>High</WarningLevel>'."\n";

		$requestXml .= '</GetUserProfileRequest>'."\n";


		// Send the request and get response
		$responseXml = $this->makeRequest($requestXml, true);
		if (stristr($responseXml, 'HTTP 404') || $responseXml == '')
		{
			$this->error = 'Error sending '.$this->apiCall.' request';
			return false;
		}


		// Load xml in array
		
		$response = simplexml_load_string($responseXml);


		$userProfile[] = array(
			'StoreUrl' => $response->User->StoreURL, 
			'StoreName' => $response->User->StoreName, 
			'SellerBusinessType' => $response->User->SellerBusinessType
		);


		$this->apiUrl = $apiUrl;
		return $userProfile;
	}
	/******************************************************************/
	/** Retrieve Categories Methods ***********************************/
	/******************************************************************/


	function saveCategories()
	{
		// Set Api Call
		$this->apiCall = 'GetCategories';

		///Build the request Xml string
		$requestXml = '<?xml version="1.0" encoding="utf-8"?>';
		$requestXml .= '<GetCategories xmlns="urn:ebay:apis:eBLBaseComponents">';
		$requestXml .= '<Version>'.$this->compatibilityLevel.'</Version>';
		$requestXml .= '<RequesterCredentials>';
		$requestXml .= '<eBayAuthToken>'.Configuration::get('EBAY_API_TOKEN').'</eBayAuthToken>';
		$requestXml .= '</RequesterCredentials>';
		$requestXml .= '<CategorySiteID>'.$this->siteID.'</CategorySiteID>';
		$requestXml .= '<DetailLevel>ReturnAll</DetailLevel>';
		$requestXml .= '<LevelLimit>5</LevelLimit>';
		$requestXml .= '<ViewAllNodes>true</ViewAllNodes>';
		$requestXml .= '</GetCategories>';

		// Send the request and get response
		$responseXml = $this->makeRequest($requestXml);
		if (stristr($responseXml, 'HTTP 404') || $responseXml == '')
		{
			$this->error = 'Error sending '.$this->apiCall.' request';
			return false;
		}

		// Load xml in array
	        $this->response = simplexml_load_string($responseXml);

		// Load categories multi sku compliant
		$categoriesMultiSkuCompliant = $this->GetCategoryFeatures('VariationsEnabled');

		// Save categories
		if (count($this->response->CategoryArray->Category) > 0)
		{
			foreach ($this->response->CategoryArray->Category as $cat)
			{
				$category = array();
				foreach ($cat as $key => $value)
					$category[(string)$key] = (string)$value;
				$category['IsMultiSku'] = 0;
				if (isset($categoriesMultiSkuCompliant[$category['CategoryID']]))
					$category['IsMultiSku'] = 1;

				Db::getInstance()->autoExecute(_DB_PREFIX_.'ebay_category', array('id_category_ref' => pSQL($category['CategoryID']), 'id_category_ref_parent' => pSQL($category['CategoryParentID']), 'id_country' => '8', 'level' => pSQL($category['CategoryLevel']), 'is_multi_sku' => pSQL($category['IsMultiSku']), 'name' => pSQL($category['CategoryName'])), 'INSERT');
			}
		}

		// Return
		return true;
	}

	function GetCategoryFeatures($featureID)
	{
		// Set Api Call
		$this->apiCall = 'GetCategoryFeatures';

		///Build the request Xml string
		$requestXml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$requestXml .= '<GetCategoryFeatures xmlns="urn:ebay:apis:eBLBaseComponents">'."\n";
		$requestXml .= '  <RequesterCredentials>'."\n";
		$requestXml .= '    <eBayAuthToken>'.Configuration::get('EBAY_API_TOKEN').'</eBayAuthToken>'."\n";
		$requestXml .= '  </RequesterCredentials>'."\n";
		$requestXml .= '  <DetailLevel>ReturnAll</DetailLevel>'."\n";
		$requestXml .= '  <FeatureID>'.$featureID.'</FeatureID>'."\n";
		$requestXml .= '  <ErrorLanguage>'.$this->language.'</ErrorLanguage>'."\n";
		$requestXml .= '  <Version>'.$this->compatibilityLevel.'</Version>'."\n";
		$requestXml .= '  <WarningLevel>High</WarningLevel>'."\n";
		$requestXml .= '  <ViewAllNodes>true</ViewAllNodes>'."\n";
		$requestXml .= '</GetCategoryFeatures>'."\n";


		// Send the request and get response
		$responseXml = $this->makeRequest($requestXml);
		if (stristr($responseXml, 'HTTP 404') || $responseXml == '')
		{
			$this->error = 'Error sending '.$this->apiCall.' request';
			return false;
		}


		// Load xml in array
		$categoriesFeatures = array();
		$response = simplexml_load_string($responseXml);

		if ($featureID == 'VariationsEnabled')
		{
			foreach ($response->Category as $cat)
				if ($cat->VariationsEnabled == true)
					$categoriesFeatures[(string)$cat->CategoryID] = true;
		}
		else
			return array();

		return $categoriesFeatures;
	}

	function getSuggestedCategories($query)
	{
		// Set Api Call
		$this->apiCall = 'GetSuggestedCategories';

		///Build the request Xml string
		$requestXml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$requestXml .= '<GetSuggestedCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">'."\n";
		$requestXml .= '  <RequesterCredentials>'."\n";
		$requestXml .= '    <eBayAuthToken>'.Configuration::get('EBAY_API_TOKEN').'</eBayAuthToken>'."\n";
		$requestXml .= '  </RequesterCredentials>'."\n";
		$requestXml .= '  <ErrorLanguage>'.$this->language.'</ErrorLanguage>'."\n";
		$requestXml .= '  <Version>'.$this->compatibilityLevel.'</Version>'."\n";
		$requestXml .= '  <WarningLevel>High</WarningLevel>'."\n";
		$requestXml .= '  <Query>'.substr(strtolower($query), 0, 350).'</Query>'."\n";
		$requestXml .= '</GetSuggestedCategoriesRequest>'."\n";

		// Send the request and get response
		$responseXml = $this->makeRequest($requestXml);
		if (stristr($responseXml, 'HTTP 404') || $responseXml == '')
		{
			$this->error = 'Error sending '.$this->apiCall.' request';
			return false;
		}

		// Load xml in array
	        $response = simplexml_load_string($responseXml);
		if (stristr($responseXml, 'HTTP 404') || $responseXml == '')
		{
			$this->error = 'Error sending '.$this->apiCall.' request';
			return false;
		}

		if (isset($response->SuggestedCategoryArray->SuggestedCategory[0]->Category->CategoryID))
			return (int)$response->SuggestedCategoryArray->SuggestedCategory[0]->Category->CategoryID;
		return 0;
	}




	/******************************************************************/
	/** Add / Update / End Product Methods ****************************/
	/******************************************************************/


	function addFixedPriceItem($datas = array())
	{
		// Check data
		if (!$datas)
			return false;

		// Set Api Call
		$this->apiCall = 'AddFixedPriceItem';

		// Without variations
		$requestXml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$requestXml .= '<AddFixedPriceItem xmlns="urn:ebay:apis:eBLBaseComponents">'."\n";
		$requestXml .= '  <ErrorLanguage>'.$this->language.'</ErrorLanguage>'."\n";
		$requestXml .= '  <WarningLevel>High</WarningLevel>'."\n";
		$requestXml .= '  <Item>'."\n";
		$requestXml .= '    <SKU>prestashop-'.$datas['id_product'].'</SKU>';
		$requestXml .= '    <Title>'.substr($datas['name'], 0, 55).'</Title>'."\n";
		if (isset($datas['pictures']))
		{	
			$requestXml .= '    <PictureDetails>'."\n";
			$requestXml .= '      <GalleryType>Gallery</GalleryType>'."\n";
			foreach ($datas['pictures'] as $picture)
			{
					$requestXml .= '      <PictureURL>'.$picture.'</PictureURL>'."\n";
			}
				
			$requestXml .= '    </PictureDetails>'."\n";
		}
		$requestXml .= '    <Description><![CDATA['.$datas['description'].']]></Description>'."\n";
		$requestXml .= '    <PrimaryCategory>'."\n";
		$requestXml .= '      <CategoryID>'.$datas['categoryId'].'</CategoryID>'."\n";
		$requestXml .= '    </PrimaryCategory>'."\n";
		$requestXml .= '    <ConditionID>1000</ConditionID>'."\n";
		if (!isset($datas['noPriceUpdate']))
		$requestXml .= '    <StartPrice>'.$datas['price'].'</StartPrice>'."\n";
		$requestXml .= '    <CategoryMappingAllowed>true</CategoryMappingAllowed>'."\n";
		$requestXml .= '    <Country>'.$this->country->iso_code.'</Country>'."\n";
		$requestXml .= '    <Currency>EUR</Currency>'."\n";
		$requestXml .= '    <DispatchTimeMax>3</DispatchTimeMax>'."\n";
		$requestXml .= '    <ListingDuration>GTC</ListingDuration>'."\n";
		$requestXml .= '    <ListingType>FixedPriceItem</ListingType>'."\n";
		$requestXml .= '    <PaymentMethods>PayPal</PaymentMethods>'."\n";
		$requestXml .= '    <PayPalEmailAddress>'.Configuration::get('EBAY_PAYPAL_EMAIL').'</PayPalEmailAddress>'."\n";
		$requestXml .= '    <PostalCode>'.Configuration::get('EBAY_SHOP_POSTALCODE').'</PostalCode>'."\n";
		$requestXml .= '    <Quantity>'.$datas['quantity'].'</Quantity>'."\n";
		$requestXml .= '    <ItemSpecifics>'."\n";
		$requestXml .= '      <NameValueList>'."\n";
		$requestXml .= '        <Name>Etat</Name>'."\n";
		$requestXml .= '        <Value>Neuf</Value>'."\n";
		$requestXml .= '      </NameValueList>'."\n";
		$requestXml .= '      <NameValueList>'."\n";
		$requestXml .= '        <Name>Marque</Name>'."\n";
		$requestXml .= '        <Value>'.$datas['brand'].'</Value>'."\n";
		$requestXml .= '      </NameValueList>'."\n";
		if (isset($datas['attributes']))
			foreach ($datas['attributes'] as $name => $value)
			{
				$requestXml .= '      <NameValueList>'."\n";
				$requestXml .= '        <Name>'.$name.'</Name>'."\n";
				$requestXml .= '        <Value>'.$value.'</Value>'."\n";
				$requestXml .= '      </NameValueList>'."\n";
			}
		$requestXml .= '    </ItemSpecifics>'."\n";
		if($this->dev)
		{
			$requestXml .= '	<ReturnPolicy>'."\n";
			$requestXml .= ' 		<ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>'."\n";
			$requestXml .= ' 		<RefundOption>MoneyBack</RefundOption>'."\n";
			$requestXml .= '		<ReturnsWithinOption>Days_30</ReturnsWithinOption>'."\n";
			$requestXml .= ' 		<Description>If you are not satisfied, return the item for refund.</Description>'."\n";
			$requestXml .= ' 		<ShippingCostPaidByOption>Buyer</ShippingCostPaidByOption>'."\n";
			$requestXml .= ' 	</ReturnPolicy>'."\n";
		}
		$requestXml .= '    <ShippingDetails>'."\n";
		$requestXml .= '      <ShippingServiceOptions>'."\n";
		$requestXml .= '        <ShippingServicePriority>1</ShippingServicePriority>'."\n";
		$requestXml .= '        <ShippingService>'.$datas['shippingService'].'</ShippingService>'."\n";
		$requestXml .= '        <FreeShipping>false</FreeShipping>'."\n";
		$requestXml .= '        <ShippingServiceCost currencyID="EUR">'.$datas['shippingCost'].'</ShippingServiceCost>'."\n";
		$requestXml .= '      </ShippingServiceOptions>'."\n";
		$requestXml .= '    </ShippingDetails>'."\n";
		$requestXml .= '    <Site>'.$this->siteName.'</Site>'."\n";
		$requestXml .= '  </Item>'."\n";
		$requestXml .= '  <RequesterCredentials>'."\n";
		$requestXml .= '    <eBayAuthToken>'.Configuration::get('EBAY_API_TOKEN').'</eBayAuthToken>'."\n";
		$requestXml .= '  </RequesterCredentials>'."\n";
		$requestXml .= '</AddFixedPriceItem>'."\n";


		// Send the request and get response
		$responseXml = $this->makeRequest($requestXml);
		if (stristr($responseXml, 'HTTP 404') || $responseXml == '')
		{
			$this->error = 'Error sending '.$this->apiCall.' request';
			return false;
		}

		// Loading XML tree in array
		$this->response = simplexml_load_string($responseXml);


		// Checking Errors
		$this->error = '';
		$this->errorCode = '';
		if (isset($this->response->Errors) && isset($this->response->Ack) && (string)$this->response->Ack != 'Success' && (string)$this->response->Ack != 'Warning')
			foreach ($this->response->Errors as $e)
			{
				// if product no longer on eBay, we log the error code
				if ((int)$e->ErrorCode == 291)
					$this->errorCode = (int)$e->ErrorCode;

				// We log error message
				if ($e->SeverityCode == 'Error')
				{
					if ($this->error != '')
						$this->error .= '<br />';
					$this->error .= (string)$e->LongMessage;
					if (isset($e->ErrorParameters->Value))
						$this->error .= '<br />'.(string)$e->ErrorParameters->Value;
				}
			}

		// Checking Success
		$this->itemID = 0;
		if (isset($this->response->Ack) && ((string)$this->response->Ack == 'Success' || (string)$this->response->Ack == 'Warning'))
		{
			$this->fees = 0;
			$this->itemID = (string)$this->response->ItemID;
			if (isset($this->response->Fees->Fee))
				foreach ($this->response->Fees->Fee as $f)
					$this->fees += (float)$f->Fee;
		}
		else if ($this->error == '')
			$this->error = 'Sorry, technical problem, try again later.';

		if (!empty($this->error))
			return false;
		return true;
	}


	function reviseFixedPriceItem($datas = array())
	{
		// Check data
		if (!$datas)
			return false;

		// Set Api Call
		$this->apiCall = 'ReviseFixedPriceItem';
		// Build the request Xml string
		$requestXml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$requestXml .= '<ReviseFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">'."\n";
		$requestXml .= '  <ErrorLanguage>'.$this->language.'</ErrorLanguage>'."\n";
		$requestXml .= '  <WarningLevel>High</WarningLevel>'."\n";
		$requestXml .= '  <Item>'."\n";
		$requestXml .= '    <ItemID>'.$datas['itemID'].'</ItemID>'."\n";
		if (isset($datas['pictures']))
		{	
			$requestXml .= '    <PictureDetails>'."\n";
			$requestXml .= '      <GalleryType>Gallery</GalleryType>'."\n";
			foreach ($datas['pictures'] as $picture)
			{
					$requestXml .= '      <PictureURL>'.$picture.'</PictureURL>'."\n";
			}
				
			$requestXml .= '    </PictureDetails>'."\n";
		}
		$requestXml .= '    <SKU>prestashop-'.$datas['id_product'].'</SKU>';
		$requestXml .= '    <Quantity>'.$datas['quantity'].'</Quantity>'."\n";
		if (!isset($datas['noPriceUpdate']))
			$requestXml .= '    <StartPrice>'.$datas['price'].'</StartPrice>'."\n";
		if (Configuration::get('EBAY_SYNC_OPTION_RESYNC') != 1)
		{
			$requestXml .= '    <Title>'.substr($datas['name'], 0, 55).'</Title>'."\n";
			$requestXml .= '    <Description><![CDATA['.$datas['description'].']]></Description>'."\n";
			$requestXml .= '    <ShippingDetails>'."\n";
			$requestXml .= '      <ShippingServiceOptions>'."\n";
			$requestXml .= '        <ShippingServicePriority>1</ShippingServicePriority>'."\n";
			$requestXml .= '        <ShippingService>'.$datas['shippingService'].'</ShippingService>'."\n";
			$requestXml .= '        <FreeShipping>false</FreeShipping>'."\n";
			$requestXml .= '        <ShippingServiceCost currencyID="EUR">'.$datas['shippingCost'].'</ShippingServiceCost>'."\n";
			$requestXml .= '      </ShippingServiceOptions>'."\n";
			$requestXml .= '    </ShippingDetails>'."\n";
		}
		$requestXml .= '  </Item>'."\n";
		$requestXml .= '  <RequesterCredentials>'."\n";
		$requestXml .= '    <eBayAuthToken>'.Configuration::get('EBAY_API_TOKEN').'</eBayAuthToken>'."\n";
		$requestXml .= '  </RequesterCredentials>'."\n";
		$requestXml .= '  <WarningLevel>High</WarningLevel>'."\n";
		$requestXml .= '</ReviseFixedPriceItemRequest>'."\n";


		// Send the request and get response
		$responseXml = $this->makeRequest($requestXml);
		if (stristr($responseXml, 'HTTP 404') || $responseXml == '')
		{
			$this->error = 'Error sending '.$this->apiCall.' request';
			return false;
		}

		// Loading XML tree in array
		$this->response = simplexml_load_string($responseXml);

		// Checking Errors
		$this->error = '';
		$this->errorCode = '';
		if (isset($this->response->Errors) && isset($this->response->Ack) && (string)$this->response->Ack != 'Success' && (string)$this->response->Ack != 'Warning')
			foreach ($this->response->Errors as $e)
			{
				// if product no longer on eBay, we log the error code
				if ((int)$e->ErrorCode == 291)
					$this->errorCode = (int)$e->ErrorCode;

				// We log error message
				if ($e->SeverityCode == 'Error')
				{
					if ($this->error != '')
						$this->error .= '<br />';
					$this->error .= (string)$e->LongMessage;
					if (isset($e->ErrorParameters->Value))
						$this->error .= '<br />'.(string)$e->ErrorParameters->Value;
				}
			}

		// Checking Success
		$this->itemID = 0;
		if (isset($this->response->Ack) && ((string)$this->response->Ack == 'Success' || (string)$this->response->Ack == 'Warning'))
		{
			$this->fees = 0;
			$this->itemID = (string)$this->response->ItemID;
			if (isset($this->response->Fees->Fee))
				foreach ($this->response->Fees->Fee as $f)
					$this->fees += (float)$f->Fee;
		}
		else if ($this->error == '')
			$this->error = 'Sorry, technical problem, try again later.';

		if (!empty($this->error))
			return false;
		return true;
	}



	function endFixedPriceItem($datas = array())
	{
		// Check data
		if (!$datas)
			return false;

		// Set Api Call
		$this->apiCall = 'EndFixedPriceItem';

		// Build the request Xml string
		$requestXml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$requestXml .= '<EndFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">'."\n";
		$requestXml .= '  <ErrorLanguage>'.$this->language.'</ErrorLanguage>'."\n";
		$requestXml .= '  <WarningLevel>High</WarningLevel>'."\n";
		$requestXml .= '  <ItemID>'.$datas['itemID'].'</ItemID>'."\n";
		$requestXml .= '  <SKU>prestashop-'.$datas['id_product'].'</SKU>';
		$requestXml .= '  <EndingReason>NotAvailable</EndingReason>'."\n";
		$requestXml .= '  <RequesterCredentials>'."\n";
		$requestXml .= '    <eBayAuthToken>'.Configuration::get('EBAY_API_TOKEN').'</eBayAuthToken>'."\n";
		$requestXml .= '  </RequesterCredentials>'."\n";
		$requestXml .= '  <WarningLevel>High</WarningLevel>'."\n";
		$requestXml .= '</EndFixedPriceItemRequest>'."\n";


		// Send the request and get response
		$responseXml = $this->makeRequest($requestXml);
		if (stristr($responseXml, 'HTTP 404') || $responseXml == '')
		{
			$this->error = 'Error sending '.$this->apiCall.' request';
			return false;
		}

		// Loading XML tree in array
		$this->response = simplexml_load_string($responseXml);


		// Checking Errors
		$this->error = '';
		$this->errorCode = '';
		if (isset($this->response->Errors) && isset($this->response->Ack) && (string)$this->response->Ack != 'Success' && (string)$this->response->Ack != 'Warning')
			foreach ($this->response->Errors as $e)
			{
				// if product no longer on eBay, we log the error code
				if ((int)$e->ErrorCode == 291)
					$this->errorCode = (int)$e->ErrorCode;

				// We log error message
				if ($e->SeverityCode == 'Error')
				{
					if ($this->error != '')
						$this->error .= '<br />';
					$this->error .= (string)$e->LongMessage;
					if (isset($e->ErrorParameters->Value))
						$this->error .= '<br />'.(string)$e->ErrorParameters->Value;
				}
			}

		// Checking Success
		$this->itemID = 0;
		if (isset($this->response->Ack) && ((string)$this->response->Ack == 'Success' || (string)$this->response->Ack == 'Warning'))
		{
			$this->fees = 0;
			$this->itemID = (string)$this->response->ItemID;
			if (isset($this->response->Fees->Fee))
				foreach ($this->response->Fees->Fee as $f)
					$this->fees += (float)$f->Fee;
		}
		elseif ($this->error == '')
			$this->error = 'Sorry, technical problem, try again later.';

		if (!empty($this->error))
			return false;

		return true;
	}












	function addFixedPriceItemMultiSku($datas = array())
	{
		// Check data
		if (!$datas)
			return false;

		// Set Api Call
		$this->apiCall = 'AddFixedPriceItem';

		// Build the request Xml string
		$requestXml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$requestXml .= '<AddFixedPriceItem xmlns="urn:ebay:apis:eBLBaseComponents">'."\n";
		$requestXml .= '  <ErrorLanguage>'.$this->language.'</ErrorLanguage>'."\n";
		$requestXml .= '  <WarningLevel>High</WarningLevel>'."\n";
		$requestXml .= '  <Item>'."\n";
		$requestXml .= '    <Country>'.$this->country->iso_code.'</Country>'."\n";
		$requestXml .= '    <Currency>EUR</Currency>'."\n";
		$requestXml .= '    <Description>'."\n";
		$requestXml .= '      <![CDATA['.$datas['description'].']]>'."\n";
		$requestXml .= '    </Description>'."\n";
		$requestXml .= '    <ConditionID>1000</ConditionID>'."\n";
		$requestXml .= '    <DispatchTimeMax>3</DispatchTimeMax>'."\n";
		$requestXml .= '    <ListingDuration>GTC</ListingDuration>'."\n";
		$requestXml .= '    <ListingType>FixedPriceItem</ListingType>'."\n";
		$requestXml .= '    <PaymentMethods>PayPal</PaymentMethods>'."\n";
		$requestXml .= '    <PayPalEmailAddress>'.Configuration::get('EBAY_PAYPAL_EMAIL').'</PayPalEmailAddress>'."\n";
		$requestXml .= '    <PostalCode>'.Configuration::get('EBAY_SHOP_POSTALCODE').'</PostalCode>'."\n";
		$requestXml .= '    <PrimaryCategory>'."\n";
		$requestXml .= '      <CategoryID>'.$datas['categoryId'].'</CategoryID>'."\n";
		$requestXml .= '    </PrimaryCategory>'."\n";
		$requestXml .= '    <Title>'.substr($datas['name'], 0, 55).'</Title>'."\n";
		if (isset($datas['pictures']))
		{	
			$requestXml .= '    <PictureDetails>'."\n";
			$requestXml .= '      <GalleryType>Gallery</GalleryType>'."\n";
			foreach ($datas['pictures'] as $picture){
				$requestXml .= '      <PictureURL>'.$picture.'</PictureURL>'."\n";
			}				
			$requestXml .= '    </PictureDetails>'."\n";
		}
		$requestXml .= '    <ItemSpecifics>'."\n";
		$requestXml .= '      <NameValueList>'."\n";
		$requestXml .= '        <Name>Etat</Name>'."\n";
		$requestXml .= '        <Value>Neuf</Value>'."\n";
		$requestXml .= '      </NameValueList>'."\n";
		$requestXml .= '      <NameValueList>'."\n";
		$requestXml .= '        <Name>Marque</Name>'."\n";
		$requestXml .= '        <Value>'.$datas['brand'].'</Value>'."\n";
		$requestXml .= '      </NameValueList>'."\n";
		$requestXml .= '    </ItemSpecifics>'."\n";
		$requestXml .= '    <Variations>'."\n";
		if (isset($datas['variations']))
		{
			// Generate Variations Set
			$requestXml .= '      <VariationSpecificsSet>'."\n";
			foreach ($datas['variationsList'] as $group => $v)
			{
				$requestXml .= '        <NameValueList>'."\n";
				$requestXml .= '          <Name>'.$group.'</Name>'."\n";
				foreach ($v as $attr => $val)
					$requestXml .= '          <Value>'.$attr.'</Value>'."\n";
				$requestXml .= '        </NameValueList>'."\n";
			}
			$requestXml .= '        </VariationSpecificsSet>'."\n";

			// Generate Variations
			foreach ($datas['variations'] as $key => $variation)
			{
				$requestXml .= '      <Variation>'."\n";
				$requestXml .= '        <SKU>prestashop-'.$key.'</SKU>'."\n";
				if (!isset($datas['noPriceUpdate']))
					$requestXml .= '        <StartPrice>'.$variation['price'].'</StartPrice>'."\n";
				$requestXml .= '        <Quantity>'.$variation['quantity'].'</Quantity>'."\n";
				$requestXml .= '        <VariationSpecifics>'."\n";
				foreach ($variation['variations'] as $v)
				{
					$requestXml .= '          <NameValueList>'."\n";
					$requestXml .= '            <Name>'.$v['name'].'</Name>'."\n";
					$requestXml .= '            <Value>'.$v['value'].'</Value>'."\n";
					$requestXml .= '          </NameValueList>'."\n";
				}
				$requestXml .= '        </VariationSpecifics>'."\n";
				$requestXml .= '      </Variation>'."\n";
			}

			// Generate Pictures Variations
			$lastSpecificName = '';
			$attributeUsed = array();
			$requestXml .= '      <Pictures>'."\n";
			foreach ($datas['variations'] as $key => $variation)
				foreach ($variation['variations'] as $kv => $v)
					if (!isset($attributeUsed[md5($v['name'].$v['value'])]) && isset($variation['pictures'][$kv]))
					{
						if ($lastSpecificName != $v['name'])
							$requestXml .= '        <VariationSpecificName>'.$v['name'].'</VariationSpecificName>'."\n";
						$requestXml .= '        <VariationSpecificPictureSet>'."\n";
						$requestXml .= '          <VariationSpecificValue>'.$v['value'].'</VariationSpecificValue>'."\n";
						$requestXml .= '          <PictureURL>'.$variation['pictures'][$kv].'</PictureURL>'."\n";
						$requestXml .= '        </VariationSpecificPictureSet>'."\n";
						$attributeUsed[md5($v['name'].$v['value'])] = true;
						$lastSpecificName = $v['name'];
					}
			$requestXml .= '      </Pictures>'."\n";
		}
		$requestXml .= '    </Variations>'."\n";
		$requestXml .= '    <ShippingDetails>'."\n";
		$requestXml .= '      <ShippingServiceOptions>'."\n";
		$requestXml .= '        <ShippingServicePriority>1</ShippingServicePriority>'."\n";
		$requestXml .= '        <ShippingService>'.$datas['shippingService'].'</ShippingService>'."\n";
		$requestXml .= '        <FreeShipping>false</FreeShipping>'."\n";
		$requestXml .= '        <ShippingServiceCost currencyID="EUR">'.$datas['shippingCost'].'</ShippingServiceCost>'."\n";
		$requestXml .= '      </ShippingServiceOptions>'."\n";
		$requestXml .= '    </ShippingDetails>'."\n";
		$requestXml .= '    <Site>'.$this->siteName.'</Site>'."\n";
		$requestXml .= '  </Item>'."\n";
		$requestXml .= '  <RequesterCredentials>'."\n";
		$requestXml .= '    <eBayAuthToken>'.Configuration::get('EBAY_API_TOKEN').'</eBayAuthToken>'."\n";
		$requestXml .= '  </RequesterCredentials>'."\n";
		$requestXml .= '  <WarningLevel>High</WarningLevel>'."\n";
		$requestXml .= '</AddFixedPriceItem>'."\n";

		// Send the request and get response
		$responseXml = $this->makeRequest($requestXml);
		if (stristr($responseXml, 'HTTP 404') || $responseXml == '')
		{
			$this->error = 'Error sending '.$this->apiCall.' request';
			return false;
		}

		// Loading XML tree in array
		$this->response = simplexml_load_string($responseXml);

		// Checking Errors
		$this->error = '';
		$this->errorCode = '';
		if (isset($this->response->Errors) && isset($this->response->Ack) && (string)$this->response->Ack != 'Success' && (string)$this->response->Ack != 'Warning')
			foreach ($this->response->Errors as $e)
			{
				// if product no longer on eBay, we log the error code
				if ((int)$e->ErrorCode == 291)
					$this->errorCode = (int)$e->ErrorCode;

				// We log error message
				if ($e->SeverityCode == 'Error')
				{
					if ($this->error != '')
						$this->error .= '<br />';
					$this->error .= (string)$e->LongMessage;
					if (isset($e->ErrorParameters->Value))
						$this->error .= '<br />'.(string)$e->ErrorParameters->Value;
				}
			}

		// Checking Success
		$this->itemID = 0;
		if (isset($this->response->Ack) && ((string)$this->response->Ack == 'Success' || (string)$this->response->Ack == 'Warning'))
		{
			$this->fees = 0;
			$this->itemID = (string)$this->response->ItemID;
			if (isset($this->response->Fees->Fee))
				foreach ($this->response->Fees->Fee as $f)
					$this->fees += (float)$f->Fee;
		}
		else if ($this->error == '')
			$this->error = 'Sorry, technical problem, try again later.';

		if (!empty($this->error))
			return false;
		return true;
	}


	function reviseFixedPriceItemMultiSku($datas = array())
	{
		// Check data
		if (!$datas)
			return false;
		
		// Set Api Call
		$this->apiCall = 'ReviseFixedPriceItem';

		// Build the request Xml string
		$requestXml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$requestXml .= '<ReviseFixedPriceItem xmlns="urn:ebay:apis:eBLBaseComponents">'."\n";
		$requestXml .= '  <ErrorLanguage>'.$this->language.'</ErrorLanguage>'."\n";
		$requestXml .= '  <WarningLevel>High</WarningLevel>'."\n";
		$requestXml .= '  <Item>'."\n";
		$requestXml .= '    <ItemID>'.$datas['itemID'].'</ItemID>'."\n";
		$requestXml .= '    <Country>'.$this->country->iso_code.'</Country>'."\n";
		$requestXml .= '    <Currency>EUR</Currency>'."\n";
		$requestXml .= '    <ConditionID>1000</ConditionID>'."\n";
		$requestXml .= '    <DispatchTimeMax>3</DispatchTimeMax>'."\n";
		$requestXml .= '    <ListingDuration>GTC</ListingDuration>'."\n";
		$requestXml .= '    <ListingType>FixedPriceItem</ListingType>'."\n";
		$requestXml .= '    <PaymentMethods>PayPal</PaymentMethods>'."\n";
		$requestXml .= '    <PayPalEmailAddress>'.Configuration::get('EBAY_PAYPAL_EMAIL').'</PayPalEmailAddress>'."\n";
		$requestXml .= '    <PostalCode>'.Configuration::get('EBAY_SHOP_POSTALCODE').'</PostalCode>'."\n";
		$requestXml .= '    <PrimaryCategory>'."\n";
		$requestXml .= '      <CategoryID>'.$datas['categoryId'].'</CategoryID>'."\n";
		$requestXml .= '    </PrimaryCategory>'."\n";
		if (isset($datas['pictures']))
		{	
			
			$requestXml .= '    <PictureDetails>'."\n";
			$requestXml .= '      <GalleryType>Gallery</GalleryType>'."\n";
			foreach ($datas['pictures'] as $picture){
				$requestXml .= '      <PictureURL>'.$picture.'</PictureURL>'."\n";
			}
				
			$requestXml .= '    </PictureDetails>'."\n";
		}
		$requestXml .= '    <ItemSpecifics>'."\n";
		$requestXml .= '      <NameValueList>'."\n";
		$requestXml .= '        <Name>Etat</Name>'."\n";
		$requestXml .= '        <Value>Neuf</Value>'."\n";
		$requestXml .= '      </NameValueList>'."\n";
		$requestXml .= '      <NameValueList>'."\n";
		$requestXml .= '        <Name>Marque</Name>'."\n";
		$requestXml .= '        <Value>'.$datas['brand'].'</Value>'."\n";
		$requestXml .= '      </NameValueList>'."\n";
		$requestXml .= '    </ItemSpecifics>'."\n";
		$requestXml .= '    <Variations>'."\n";
		if (isset($datas['variations']))
		{
			// Generate Variations Set
			$requestXml .= '      <VariationSpecificsSet>'."\n";
			foreach ($datas['variationsList'] as $group => $v)
				if (isset($group) && !empty($group))
				{
					$requestXml .= '        <NameValueList>'."\n";
					$requestXml .= '          <Name>'.$group.'</Name>'."\n";
					foreach ($v as $attr => $val)
						$requestXml .= '          <Value>'.$attr.'</Value>'."\n";
					$requestXml .= '        </NameValueList>'."\n";
				}
			$requestXml .= '        </VariationSpecificsSet>'."\n";

			// Generate Variations
			foreach ($datas['variations'] as $key => $variation)
			{
				$requestXml .= '      <Variation>'."\n";
				$requestXml .= '        <SKU>prestashop-'.$key.'</SKU>'."\n";
				if (!isset($datas['noPriceUpdate']))
				$requestXml .= '        <StartPrice>'.$variation['price'].'</StartPrice>'."\n";
				$requestXml .= '        <Quantity>'.$variation['quantity'].'</Quantity>'."\n";
				$requestXml .= '        <VariationSpecifics>'."\n";
				foreach ($variation['variations'] as $v)
				{
					$requestXml .= '          <NameValueList>'."\n";
					$requestXml .= '            <Name>'.$v['name'].'</Name>'."\n";
					$requestXml .= '            <Value>'.$v['value'].'</Value>'."\n";
					$requestXml .= '          </NameValueList>'."\n";
				}
				$requestXml .= '        </VariationSpecifics>'."\n";
				$requestXml .= '      </Variation>'."\n";
			}

			// Generate Pictures Variations
			$lastSpecificName = '';
			$attributeUsed = array();
			$requestXml .= '      <Pictures>'."\n";
			foreach ($datas['variations'] as $key => $variation)
				foreach ($variation['variations'] as $kv => $v){
					if (!isset($attributeUsed[md5($v['name'].$v['value'])]) && (isset($variation['pictures'][$kv])))
					{
						if ($lastSpecificName != $v['name'])
							$requestXml .= '        <VariationSpecificName>'.$v['name'].'</VariationSpecificName>'."\n";
						$requestXml .= '        <VariationSpecificPictureSet>'."\n";
						$requestXml .= '          <VariationSpecificValue>'.$v['value'].'</VariationSpecificValue>'."\n";
						$requestXml .= '          <PictureURL>'.$variation['pictures'][$kv].'</PictureURL>'."\n";
						$requestXml .= '        </VariationSpecificPictureSet>'."\n";
						$attributeUsed[md5($v['name'].$v['value'])] = true;
						$lastSpecificName = $v['name'];
					}
				}	
			$requestXml .= '      </Pictures>'."\n";
		}

		$requestXml .= '    </Variations>'."\n";
		if (Configuration::get('EBAY_SYNC_OPTION_RESYNC') != 1)
		{
			$requestXml .= '    <Title>'.substr($datas['name'], 0, 55).'</Title>'."\n";
			$requestXml .= '    <Description>'."\n";
			$requestXml .= '      <![CDATA['.$datas['description'].']]>'."\n";
			$requestXml .= '    </Description>'."\n";
			$requestXml .= '    <ShippingDetails>'."\n";
			$requestXml .= '      <ShippingServiceOptions>'."\n";
			$requestXml .= '        <ShippingServicePriority>1</ShippingServicePriority>'."\n";
			$requestXml .= '        <ShippingService>'.$datas['shippingService'].'</ShippingService>'."\n";
			$requestXml .= '        <FreeShipping>false</FreeShipping>'."\n";
			$requestXml .= '        <ShippingServiceCost currencyID="EUR">'.$datas['shippingCost'].'</ShippingServiceCost>'."\n";
			$requestXml .= '      </ShippingServiceOptions>'."\n";
			$requestXml .= '    </ShippingDetails>'."\n";
		}
		$requestXml .= '    <Site>'.$this->siteName.'</Site>'."\n";
		$requestXml .= '  </Item>'."\n";
		$requestXml .= '  <RequesterCredentials>'."\n";
		$requestXml .= '    <eBayAuthToken>'.Configuration::get('EBAY_API_TOKEN').'</eBayAuthToken>'."\n";
		$requestXml .= '  </RequesterCredentials>'."\n";
		$requestXml .= '  <WarningLevel>High</WarningLevel>'."\n";
		$requestXml .= '</ReviseFixedPriceItem>'."\n";


		// Send the request and get response
		$responseXml = $this->makeRequest($requestXml);
		if (stristr($responseXml, 'HTTP 404') || $responseXml == '')
		{
			$this->error = 'Error sending '.$this->apiCall.' request';
			return false;
		}

		// Loading XML tree in array
		$this->response = simplexml_load_string($responseXml);

		// Checking Errors
		$this->error = '';
		$this->errorCode = '';
		if (isset($this->response->Errors) && isset($this->response->Ack) && (string)$this->response->Ack != 'Success' && (string)$this->response->Ack != 'Warning')
			foreach ($this->response->Errors as $e)
			{
				// if product no longer on eBay, we log the error code
				if ((int)$e->ErrorCode == 291)
					$this->errorCode = (int)$e->ErrorCode;

				// We log error message
				if ($e->SeverityCode == 'Error')
				{
					if ($this->error != '')
						$this->error .= '<br />';
					$this->error .= (string)$e->LongMessage;
					if (isset($e->ErrorParameters->Value))
						$this->error .= '<br />'.(string)$e->ErrorParameters->Value;
				}
			}

		// Checking Success
		$this->itemID = 0;
		if (isset($this->response->Ack) && ((string)$this->response->Ack == 'Success' || (string)$this->response->Ack == 'Warning'))
		{
			$this->fees = 0;
			$this->itemID = (string)$this->response->ItemID;
			if (isset($this->response->Fees->Fee))
				foreach ($this->response->Fees->Fee as $f)
					$this->fees += (float)$f->Fee;
		}
		else if ($this->error == '')
			$this->error = 'Sorry, technical problem, try again later.';

		if (!empty($this->error))
			return false;
		return true;
	}














	/******************************************************************/
	/** Order Methods *************************************************/
	/******************************************************************/



	function getOrders($CreateTimeFrom, $CreateTimeTo, $page)
	{
		// Check data
		if (!$CreateTimeFrom || !$CreateTimeTo)
			return false;

		// Set Api Call
		$this->apiCall = 'GetOrders';

		// Without variations
		$requestXml = '<?xml version="1.0" encoding="utf-8"?>'."\n";
		$requestXml .= '<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">'."\n";
		$requestXml .= '  <DetailLevel>ReturnAll</DetailLevel>'."\n";
		$requestXml .= '  <ErrorLanguage>'.$this->language.'</ErrorLanguage>'."\n";
		$requestXml .= '  <WarningLevel>High</WarningLevel>'."\n";
		$requestXml .= '  <CreateTimeFrom>'.$CreateTimeFrom.'</CreateTimeFrom>'."\n";
		$requestXml .= '  <CreateTimeTo>'.$CreateTimeTo.'</CreateTimeTo>'."\n";
		$requestXml .= '  <OrderRole>Seller</OrderRole>'."\n";
		//$requestXml .= '  <OrderStatus>Completed</OrderStatus>'."\n";
		$requestXml .= '  <Pagination>'."\n";
		$requestXml .= '    <EntriesPerPage>100</EntriesPerPage>'."\n";
		$requestXml .= '    <PageNumber>'.$page.'</PageNumber>'."\n";
		$requestXml .= '  </Pagination>'."\n";
		$requestXml .= '  <RequesterCredentials>'."\n";
		$requestXml .= '    <eBayAuthToken>'.Configuration::get('EBAY_API_TOKEN').'</eBayAuthToken>'."\n";
		$requestXml .= '  </RequesterCredentials>'."\n";
		$requestXml .= '</GetOrdersRequest>'."\n";

		// Send the request and get response
		$responseXml = $this->makeRequest($requestXml);
		if (stristr($responseXml, 'HTTP 404') || $responseXml == '')
		{
			$this->error = 'Error sending '.$this->apiCall.' request';
			return false;
		}

		// Loading XML tree in array
		$this->response = simplexml_load_string($responseXml);


		// Checking Errors
		$this->error = '';
		if (isset($this->response->Errors) && isset($this->response->Ack) && (string)$this->response->Ack != 'Success' && (string)$this->response->Ack != 'Warning')
			foreach ($this->response->Errors as $e)
			{
				if ($this->error != '')
					$this->error .= '<br />';
				$this->error .= (string)$e->LongMessage;
			}

		// Checking Success
		$orderList = array();
		if (isset($this->response->OrderArray))
			foreach ($this->response->OrderArray->Order as $order)
			{
				$name = str_replace(array('_', ',', '  '), array('', '', ' '), (string)$order->ShippingAddress->Name); 
				$name = preg_replace('/\-?\d+/', '', $name);
				$name = explode(' ', $name, 2);
				$itemList = array();
				for ($i = 0; isset($order->TransactionArray->Transaction[$i]); $i++)
				{
					$transaction = $order->TransactionArray->Transaction[$i];

					$id_product = 0;
					$id_product_attribute = 0;
					$quantity = (string)$transaction->QuantityPurchased;
					if (isset($transaction->Item->SKU))
					{
						$tmp = explode('-', (string)$transaction->Item->SKU);
						if (isset($tmp[1]))
						$id_product = $tmp[1];
						if (isset($tmp[2]))
							$id_product_attribute = $tmp[2];
					}
					if (isset($transaction->Variation->SKU))
					{
						$tmp = explode('-', (string)$transaction->Variation->SKU);
						if (isset($tmp[1]))
						$id_product = $tmp[1];
						if (isset($tmp[2]))
						$id_product_attribute = $tmp[2];
					}

					$id_product = (int)Db::getInstance()->getValue('SELECT `id_product` FROM `'._DB_PREFIX_.'product` WHERE `id_product` = '.(int)$id_product);
					$id_product_attribute = (int)Db::getInstance()->getValue('SELECT `id_product_attribute` FROM `'._DB_PREFIX_.'product_attribute` WHERE `id_product` = '.(int)$id_product.' AND `id_product_attribute` = '.(int)$id_product_attribute);
					if ($id_product > 0)
						$itemList[] = array('id_product' => $id_product, 'id_product_attribute' => $id_product_attribute, 'quantity' => $quantity, 'price' => (string)$transaction->TransactionPrice);
					else
					{
						$reference = '-----------------------';
						$skuItem = (string)$transaction->Item->SKU;
						$skuVariation = (string)$transaction->Variation->SKU;
						$customLabel = (string)$transaction->SellingManagerProductDetails->CustomLabel;
						if ($customLabel != '') $reference = $customLabel;
						else
						{
							if ($skuVariation != '') $reference = $skuVariation;
							else $reference = $skuItem;
						}
						
						$reference = trim($reference);
						if (!empty($reference))
						{
							$id_product = Db::getInstance()->getValue('
							SELECT `id_product` FROM `'._DB_PREFIX_.'product`
							WHERE `reference` = \''.pSQL($reference).'\'');
							if ((int)$id_product > 0)
								$itemList[] = array('id_product' => $id_product, 'id_product_attribute' => 0, 'quantity' => $quantity, 'price' => (string)$transaction->TransactionPrice);
							else
							{
								$row = Db::getInstance()->getValue('
								SELECT `id_product`, `id_product_attribute` FROM `'._DB_PREFIX_.'product_attribute`
								WHERE `reference` = \''.pSQL($reference).'\'');
								if ((int)$row['id_product'] > 0)
									$itemList[] = array('id_product' => $row['id_product'], 'id_product_attribute' => $row['id_product_attribute'], 'quantity' => $quantity, 'price' => (string)$transaction->TransactionPrice);
							}
						}
					}
				}

				$orderList[] = array(
					'id_order_ref' => (string)$order->OrderID,
					'amount' => (string)$order->AmountPaid,
					'status' => (string)$order->CheckoutStatus->Status,
					'date' => substr((string)$order->CreatedTime, 0, 10).' '.substr((string)$order->CreatedTime, 11, 8),
					'name' => (string)$order->ShippingAddress->Name,
					'firstname' => substr(trim($name[0]), 0, 32),
					'familyname' => (isset($name[1]) ? substr(trim($name[1]), 0, 32) : substr(trim($name[0]), 0, 32)),
					'address1' => (string)$order->ShippingAddress->Street1,
					'address2' => (string)$order->ShippingAddress->Street2,
					'city' => (string)$order->ShippingAddress->CityName,
					'state' => (string)$order->ShippingAddress->StateOrProvince,
					'country_iso_code' => (string)$order->ShippingAddress->Country,
					'country_name' => (string)$order->ShippingAddress->CountryName,
					'phone' => (string)$order->ShippingAddress->Phone,
					'postalcode' => (string)$order->ShippingAddress->PostalCode,
					'shippingService' => (string)$order->ShippingServiceSelected->ShippingService,
					'shippingServiceCost' => (string)$order->ShippingServiceSelected->ShippingServiceCost,
					'email' => (string)$order->TransactionArray->Transaction[0]->Buyer->Email,
					'product_list' => $itemList,
					'payment_method' => (string)$order->CheckoutStatus->PaymentMethod,
					'id_order_seller' => (string)$order->ShippingDetails->SellingManagerSalesRecordNumber,
					'date_add' => substr((string)$order->CreatedTime, 0, 10).' '.substr((string)$order->CreatedTime, 11, 8),
					//'object' => $order
				);
			}

		return $orderList;
	}


}



class eBayPayment extends PaymentModule
{
	function __construct()
	{
		$this->name = 'ebay';
		parent::__construct();
	}
}


