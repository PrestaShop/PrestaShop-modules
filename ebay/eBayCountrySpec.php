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
 *  @license	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!in_array('Ebay', get_declared_classes()))
		require_once(dirname(__FILE__).'/ebay.php');

class eBayCountrySpec
	{
	public $country;
	public $acceptedIso = array('it', 'fr', 'gb', 'es');

	function __construct($pCountry = null)
	{
		if($pCountry != null)
			$this->country = $pCountry;
		else
			$this->country = $this->getCountry();
	}

	public function getSiteID()
	{
		switch(strtolower($this->country->iso_code))
		{
			case 'it':
				return 101;
				break;
			case 'gb':
				return 3;
				break;
			case 'es':
				return 186;
				break;
			case 'fr':
			default:
				return 71;
				break;		
		}
	}

	public function getLanguage()
	{
		switch(strtolower($this->country->iso_code))
		{
			case 'it':
				return 'it_IT';
				break;
			case 'gb': 
				return 'en_GB';
				break;
			case 'es': 
				return 'es_ES';
				break;
			case 'fr':
			default:
				return 'fr_FR';
				break;
		}
	}

	public function getCurrency()
	{
		switch(strtolower($this->country->iso_code))
		{
			case 'it':
				return 'EUR';
				break;
			case 'gb': 
				return 'GBP';
				break;
			case 'es': 
				return 'EUR';
				break;
			case 'fr':
			default:
				return 'EUR';
				break;
		}
	}

	public function getSiteName()
	{
		switch(strtolower($this->country->iso_code))
		{
			case 'it':
				return 'Italy';
				break;
			case 'gb':
				return 'UK';
				break;
			case 'es':
				return 'Spain';
				break;
			case 'fr':
			default:
				return 'France';
				break;
		}
	}

	public function getSiteExtension()
	{
		switch(strtolower($this->country->iso_code))
		{
			case 'it':
				return 'it';
				break;
			case 'gb':
				return 'co.uk';
				break;
			case 'es':
				return 'es';
				break;
			case 'fr':
			default:
				return 'fr';
				break;
		}
	}

	public function getImgStats()
	{
		switch(strtolower($this->country->iso_code))
		{
			case 'it':
				return null;
				break;
			case 'gb':
				return null;
				break;
			case 'es':
				return null;
				break;
			case 'fr':
			default:
				return 'img/ebay_stats.png';
				break;
		}
	}

	public function loadShippingMethod() 
	{
		switch(strtolower($this->country->iso_code))
		{
				case 'it':
					return array(
					10101 => array('description' => 'Posta ordinaria', 'shippingService' => 'IT_RegularMail', 'shippingServiceID' => '10101'),
					60101 => array('description' => 'Spedizione internazionale standard a prezzo fisso', 'shippingService' => 'IT_StandardInternational', 'shippingServiceID' => '60101'),
					10102 => array('description' => 'Posta prioritaria', 'shippingService' => 'IT_PriorityMail', 'shippingServiceID' => '10102'),
					60102 => array('description' => 'Spedizione internazionale celere a prezzo fisso', 'shippingService' => 'IT_ExpeditedInternational', 'shippingServiceID' => '60102'),
					60103 => array('description' => 'Altre spedizioni internazionali (vedi descrizione)', 'shippingService' => 'IT_OtherInternational', 'shippingServiceID' => '60103'),
					10103 => array('description' => 'Posta raccomandata', 'shippingService' => 'IT_MailRegisteredLetter', 'shippingServiceID' => '10103'),
					10104 => array('description' => 'Posta raccomandata con contrassegno', 'shippingService' => 'IT_MailRegisteredLetterWithMark', 'shippingServiceID' => '10104'),
					10105 => array('description' => 'Posta assicurata', 'shippingService' => 'IT_InsuredMail', 'shippingServiceID' => '10105'),
					10106 => array('description' => 'Posta celere', 'shippingService' => 'IT_QuickMail', 'shippingServiceID' => '10106'),
					10107 => array('description' => 'Pacco ordinario', 'shippingService' => 'IT_RegularPackage', 'shippingServiceID' => '10107'),
					10108 => array('description' => 'Pacco celere 1', 'shippingService' => 'IT_QuickPackage1', 'shippingServiceID' => '10108'),
					10109 => array('description' => 'Pacco celere 3', 'shippingService' => 'IT_QuickPackage3', 'shippingServiceID' => '10109'),
					10111 => array('description' => 'Paccocelere Maxi', 'shippingService' => 'IT_ExpressPackageMaxi', 'shippingServiceID' => '10111'),
					10110 => array('description' => 'Corriere espresso', 'shippingService' => 'IT_ExpressCourier', 'shippingServiceID' => '10110'),
					10151 => array('description' => 'Ritiro in zona', 'shippingService' => 'IT_Pickup', 'shippingServiceID' => '10151'),
					10112 => array('description' => 'Spedizione economica dall’estero', 'shippingService' => 'IT_EconomyDeliveryFromAbroad', 'shippingServiceID' => '10112'),
					10113 => array('description' => 'Spedizione standard dall’estero', 'shippingService' => 'IT_StandardDeliveryFromAbroad', 'shippingServiceID' => '10113'),
					10114 => array('description' => 'Spedizione espressa dall’estero', 'shippingService' => 'IT_ExpressDeliveryFromAbroad', 'shippingServiceID' => '10114'),
				);
			break;
		case 'fr' :
		default :
				return array(
					7104 => array('description' => 'Colissimo', 'shippingService' => 'FR_ColiposteColissimo', 'shippingServiceID' => '7104'),
					7112 => array('description' => 'Ecopli', 'shippingService' => 'FR_Ecopli', 'shippingServiceID' => '7112'),
					57104 => array('description' => 'La Poste - Courrier International Prioritaire', 'shippingService' => 'FR_LaPosteInternationalPriorityCourier', 'shippingServiceID' => '57104'),
					7101 => array('description' => 'Lettre', 'shippingService' => 'FR_PostOfficeLetter', 'shippingServiceID' => '7101'),
					57105 => array('description' => 'La Poste - Courrier International Economique', 'shippingService' => 'FR_LaPosteInternationalEconomyCourier', 'shippingServiceID' => '57105'),
					57106 => array('description' => 'La Poste - Colissimo International', 'shippingService' => 'FR_LaPosteColissimoInternational', 'shippingServiceID' => '57106'),
					7102 => array('description' => 'Lettre avec suivi', 'shippingService' => 'FR_PostOfficeLetterFollowed', 'shippingServiceID' => '7102'),
					57107 => array('description' => 'La Poste - Colis Economique International', 'shippingService' => 'FR_LaPosteColisEconomiqueInternational', 'shippingServiceID' => '57107'),
					7103 => array('description' => 'Lettre recommand&eacute;e', 'shippingService' => 'FR_PostOfficeLetterRecommended', 'shippingServiceID' => '7103'),
					7121 => array('description' => 'Lettre Max', 'shippingService' => 'FR_LaPosteLetterMax', 'shippingServiceID' => '7121'),
					7113 => array('description' => 'Coli&eacute;co', 'shippingService' => 'FR_Colieco', 'shippingServiceID' => '7113'),
					57108 => array('description' => 'La Poste - Colissimo Emballage International', 'shippingService' => 'FR_LaPosteColissimoEmballageInternational', 'shippingServiceID' => '57108'),
					57114 => array('description' => 'Chronopost Express International', 'shippingService' => 'FR_ChronopostExpressInternational', 'shippingServiceID' => '57114'),
					7106 => array('description' => 'Colissimo Recommand&eacute;', 'shippingService' => 'FR_ColiposteColissimoRecommended', 'shippingServiceID' => '7106'),
					57109 => array('description' => 'Chronopost Classic International', 'shippingService' => 'FR_ChronopostClassicInternational', 'shippingServiceID' => '57109'),
					57110 => array('description' => 'Chronopost Premium International', 'shippingService' => 'FR_ChronopostPremiumInternational', 'shippingServiceID' => '57110'),
					7117 => array('description' => 'Chronopost - Chrono Relais', 'shippingService' => 'FR_ChronopostChronoRelais', 'shippingServiceID' => '7117'),
					57111 => array('description' => 'UPS Standard', 'shippingService' => 'FR_UPSStandardInternational', 'shippingServiceID' => '57111'),
					7111 => array('description' => 'Autre mode d\'envoi de courrier', 'shippingService' => 'FR_Autre', 'shippingServiceID' => '7111'),
					57112 => array('description' => 'UPS Express', 'shippingService' => 'FR_UPSExpressInternational', 'shippingServiceID' => '57112'),
					7114 => array('description' => 'Autre mode d\'envoi de colis', 'shippingService' => 'FR_AuteModeDenvoiDeColis', 'shippingServiceID' => '7114'),
					57113 => array('description' => 'DHL', 'shippingService' => 'FR_DHLInternational', 'shippingServiceID' => '57113'),
					57101 => array('description' => 'Frais de livraison internationale fixes', 'shippingService' => 'FR_StandardInternational', 'shippingServiceID' => '57101'),
					7116 => array('description' => 'Chronopost', 'shippingService' => 'FR_Chronopost', 'shippingServiceID' => '7116'),
					57102 => array('description' => 'Frais fixes pour livraison internationale express', 'shippingService' => 'FR_ExpeditedInternational', 'shippingServiceID' => '57102'),
					57103 => array('description' => 'Autres livraisons internationales (voir description)', 'shippingService' => 'FR_OtherInternational', 'shippingServiceID' => '57103'),
					7118 => array('description' => 'Chrono 10', 'shippingService' => 'FR_Chrono10', 'shippingServiceID' => '7118'),
					7119 => array('description' => 'Chrono 13', 'shippingService' => 'FR_Chrono13', 'shippingServiceID' => '7119'),
					7120 => array('description' => 'Chrono 18', 'shippingService' => 'FR_Chrono18', 'shippingServiceID' => '7120'),
					7105 => array('description' => 'Coliposte - Colissimo Direct', 'shippingService' => 'FR_ColiposteColissimoDirect', 'shippingServiceID' => '7105'),
					7107 => array('description' => 'Chronoposte - Chrono Classic International', 'shippingService' => 'FR_ChronoposteInternationalClassic', 'shippingServiceID' => '7107'),
					7108 => array('description' => 'DHL - Express Europack', 'shippingService' => 'FR_DHLExpressEuropack', 'shippingServiceID' => '7108'),
					7109 => array('description' => 'UPS - Standard', 'shippingService' => 'FR_UPSStandard', 'shippingServiceID' => '7109'),
				);
		}
	}

	/**
	* Tools Methods
	*
	* Sends back true or false
	**/
	public function checkCountry() 
	{
		if (in_array(strtolower($this->country->iso_code), $this->acceptedIso))
			return true;
		return false;
	}

	/**
	* Tools Methods
	*
	* Set country
	*
	**/
	public function getCountry() 
	{
		// If eBay module has already been called once, use the default country
		if (!is_object($this->country))
			$this->country = new Country((int) Configuration::get('EBAY_COUNTRY_DEFAULT'));

		// Else use PrestaShop Default country
		if (in_array(strtolower($this->country->iso_code), $this->acceptedIso))
			return $this->country;

		$this->country = new Country((int) Configuration::get('PS_COUNTRY_DEFAULT'));
		if (in_array(strtolower($this->country->iso_code), $this->acceptedIso))
		{
			
			Ebay::setConfigurationStatic('EBAY_COUNTRY_DEFAULT', $this->country->id);
			return $this->country;
		}
		return $this->country;
	}
}