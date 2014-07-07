<?php
	/**
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
	 * @author    boxdrop Group AG
	 * @copyright boxdrop Group AG
	 * @license   http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
	 * International Registered Trademark & Property of boxdrop Group AG
	 */

	/**
	 * Handler for modifying Carriers
	 *
	 * @author  sweber <sw@boxdrop.com>
	 * @package BoxdropShipment
	 */
	class BoxdropCarrier
	{
		/**
		 * @var $default_ranges array with default pricings for shippings.
		 *
		 * ex:
		 * array(SHIPMENT_MODE => array(max_weight => price,
		 *                              ....))
		 */
		private static $default_ranges = array(
			BoxdropShipment::CONF_MODE_DIRECT_ECONOMY => array(
				5 => 9.50,
				10 => 10.50,
				20 => 12.00,
				30 => 14.50,
				50 => 19.50
			),
			BoxdropShipment::CONF_MODE_DIRECT_EXPRESS => array(
				5 => 12.50,
				10 => 13.50,
				20 => 15.00,
				30 => 17.50,
				50 => 22.50
			)
		);

		/**
		 * Creates a new courier entry and stores the ID in our config variables
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @param  string $mode
		 * @param  string $name
		 * @return void
		 */
		public static function setupCourier($mode, $name)
		{
			$id_lang = Configuration::get('PS_LANG_DEFAULT');
			/*
			 * differ between express and economy
			 */
			if ($mode == BoxdropShipment::CONF_MODE_DIRECT_EXPRESS)
			{
				$grade = 9;
				$delay = array(
					$id_lang => 'Delivery using DHL Express services',
					'en' => 'Delivery using DHL Express services',
					'it' => 'Consegna con servizio DHL EXPRESS'
				);
			}
			else
			{
				$grade = 7;
				$delay = array(
					$id_lang => 'Delivery using DHL Economy services',
					'en' => 'Delivery using DHL Economy services',
					'it' => 'Consegna con servizio DHL ECONOMY'
				);
			}

			$carrier = new Carrier();
			$carrier->name = $name;
			// Captain obvious says: "This is my name!"
			$carrier->active = true;
			// We want our new carrier to be active right from the start.
			$carrier->delay = $delay;
			// Description in the FrontOffice
			$carrier->deleted = 0;
			// We would be very sad if we'd be disabled right now.
			$carrier->shipping_handling = false;
			// Flag for toggling handling costs (this will add a fixed price to the actual shipment price for packaging, etc)
			$carrier->range_behavior = 1;
			// Behavior if the shipment weight exceeds any defined price-weight ranges: 0: use highest, 1: disable.
			$carrier->grade = $grade;
			// Grade of the shipping delay (0 for longest, 9 for shortest)
			$carrier->shipping_external = false;
			// No real documentation found. We are just on the carrier list if we are setting to false here. Heh?
			$carrier->external_module_name = 'boxdropshipment';
			// We need to specify our module folder name here.
			$carrier->is_module = true;
			// Setup this carrier as a module, what we are. This way, we can call ourselves even in the backend (displayInfoByCart())
			// :> undocumented, but works except for AJAX calls.
			$carrier->shipping_method = Carrier::SHIPPING_METHOD_WEIGHT;
			// Price determination mode. In our case, its the weight
			$carrier->need_range = true;
			// If true, boxdropshipment::getOrderShippingCost will be called in the frontend order time instead of
			// boxdropshipment::getOrderShippingCostExternal. Params seems to be the same though?
			$carrier->max_weight = 50;
			// Maximum weight in kg this carrier can handle
			$carrier->max_width = 90;
			// Maximum package width managed by the transporter
			$carrier->max_height = 90;
			// Maximum package height managed by the transporter
			$carrier->max_depth = 90;
			// Maximum package deep managed by the transporter
			$carrier->url = 'http://www.dhl.it/content/it/it/express/ricerca.shtml?brand=DHL&AWB=@%0D%0A';
			// Tracking link, the @ is the placeholder for the AWB number
			if ($carrier->add())
			{
				$groups = Group::getGroups(true);
				foreach ($groups as $group)
				{
					Db::getInstance()->autoExecute(_DB_PREFIX_.'carrier_group', array(
						'id_carrier' => $carrier->id,
						'id_group' => $group['id_group']
					), 'INSERT');
				}
				$country_zone = self::createCountryZone($mode, $id_lang);
				self::createPricingPresetDefault($mode, $carrier, $country_zone);
				copy(dirname(__FILE__).'/../logo.png', _PS_SHIP_IMG_DIR_.'/'.$carrier->id.'.jpg');
				BoxdropHelper::getCarrierId($mode, $carrier->id);
				return true;
			}
			return false;
		}

		/**
		 * As DHL is not offering Economy services for domestic shipments,
		 * we need to disable the delivery in that zone.
		 * Get the zone thats holding the sender country. If there are other
		 * countries inside, create a separate zone with the sender country
		 * only, and name it accordingly.
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  string  $mode
		 * @param  integer $id_lang
		 * @return Zone
		 */
		public static function createCountryZone($mode, $id_lang)
		{
			if ($mode == BoxdropShipment::CONF_MODE_DIRECT_ECONOMY)
			{
				$sender_country_id = Configuration::get('PS_COUNTRY_DEFAULT');
				$sender_country = new Country($sender_country_id, $id_lang);
				$sender_country_zone_id = Country::getIdZone($sender_country_id);
				$countries_in_sender_zone = Country::getCountriesByZoneId($sender_country_zone_id, $id_lang);
				if (count($countries_in_sender_zone) > 1)
				{
					$country_zone = new Zone();
					$country_zone->name = $sender_country->name;
					$country_zone->active = true;
					$country_zone->add();
					$sender_country->id_zone = $country_zone->id;
					$sender_country->save();
					/*
					 * Because we have changed the countries zone, we need to do so as well for the assigned states.
					 * Otherwise, the FrontOffice will display our economy carrier, as the determination is done via the id_state instead of country.
					 */
					$state_ids = array();
					$states = State::getStatesByIdCountry($sender_country->id);
					if (count($states) != 0)
					{
						foreach ($states as $state)
							array_push($state_ids, $state['id_state']);

						/*
						 * dummy object creation, as State::affectZoneToSelection() is not declared static. Oh my.
						 */
						$state = new State();
						$state->affectZoneToSelection($state_ids, $country_zone->id);
					}
					return $country_zone;
				}
				else
				{
					/*
					 * Our home country already offers a single zone.
					 */
					return new Zone($sender_country_zone_id);
				}
			}
			return new Zone();
		}

		/**
		 * Creates the default pricing preset (pass shipping costs to customer) for the given carrier
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  string  $mode
		 * @param  Carrier $carrier
		 * @param  Zone    $country_zone
		 */
		public static function createPricingPresetDefault($mode, $carrier, $country_zone)
		{
			/*
			 * insert default range costs foreach price range all zones.
			 * These may be adjusted afterwards by the shop.
			 */
			$price_ranges = array();
			$start_weight = 0;
			$weight_ranges = array();
			$zones = Zone::getZones(true);
			$carrier->shipping_method = Carrier::SHIPPING_METHOD_WEIGHT;
			$carrier->save();
			self::deleteRangesByCarrier($carrier->id);
			self::deleteDeliveriesByCarrier($carrier->id);
			self::deleteCarrierZonesByCarrier($carrier->id);
			foreach (self::$default_ranges[$mode] as $max_weight => $price)
			{
				$range_weight = new RangeWeight();
				$range_weight->id_carrier = $carrier->id;
				$range_weight->delimiter1 = $start_weight;
				$range_weight->delimiter2 = $max_weight;
				$range_weight->add();
				$range_price = new RangePrice();
				$range_price->id_carrier = $carrier->id;
				$range_price->delimiter1 = $start_weight;
				$range_price->delimiter2 = $max_weight;
				$range_price->add();
				$price_ranges[$max_weight] = $range_price;
				$start_weight = $max_weight + 0.000001;
				$weight_ranges[$max_weight] = $range_weight;
			}
			foreach ($zones as $z)
			{
				/*
				 * No prices for economy shipments in the country only zone.
				 */
				if ($mode == BoxdropShipment::CONF_MODE_DIRECT_ECONOMY && $country_zone->id == $z['id_zone'])
					continue;

				/*
				 * We are NOT using Carrier::addZone() here, as its filling the database with zeroes in the price matrix.
				 */
				Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'carrier_zone`(`id_carrier`, `id_zone`)VALUES('.$carrier->id.', '.$z['id_zone'].')');
				/*
				 * We cannot use the object classes "Delivery" here, as it is not allowing to put NULL into id_shop and id_shop_group
				 * which is needed in single shop constellations, when the price matirx query will ask for those values "IS NULL"
				 *
				 * So we will borrow the source from Carrier::addZone() here, to avoid selecting the zero-inserted price values and insert it directly,
				 * as there is no documentation on Carrier::addDeliveryPrice() in direct relation with a new CarrierZone.
				 */
				$sql = 'INSERT INTO `'._DB_PREFIX_.'delivery` (`id_carrier`, `id_range_price`, `id_range_weight`, `id_zone`, `price`) VALUES ';
				foreach ($weight_ranges as $price => $weight_range)
				{
					$price = self::$default_ranges[$mode][$weight_range->delimiter2];
					$sql .= '('.$carrier->id.', '.$price_ranges[$weight_range->delimiter2]->id.', 0, '.$z['id_zone'].', '.(float)$price.'), ';
					$sql .= '('.$carrier->id.', 0, '.$weight_range->id.', '.$z['id_zone'].', '.(float)$price.'), ';
				}
				$sql = rtrim($sql, ', ');
				Db::getInstance()->execute($sql);
			}
		}

		/**
		 * Creates the free shipment pricing preset for the given carrier
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  string  $mode
		 * @param  Carrier $carrier
		 * @param  Zone    $country_zone
		 * @param  float   $min_free_order_total
		 * @param  float   $default_shipping_price
		 */
		public static function createPricingPresetFree($mode, $carrier, $country_zone, $min_free_order_total, $default_shipping_price)
		{
			$zones = Zone::getZones(true);
			$carrier->shipping_method = Carrier::SHIPPING_METHOD_PRICE;
			$carrier->save();
			self::deleteRangesByCarrier($carrier->id);
			self::deleteDeliveriesByCarrier($carrier->id);
			self::deleteCarrierZonesByCarrier($carrier->id);
			$range_weight = new RangeWeight();
			$range_weight->id_carrier = $carrier->id;
			$range_weight->delimiter1 = 0;
			$range_weight->delimiter2 = 99;
			$range_weight->add();
			$range_price = new RangePrice();
			$range_price->id_carrier = $carrier->id;
			$range_price->delimiter1 = 0;
			$range_price->delimiter2 = $min_free_order_total;
			$range_price->add();
			$range_price2 = new RangePrice();
			$range_price2->id_carrier = $carrier->id;
			$range_price2->delimiter1 = ($min_free_order_total + 0.01);
			$range_price2->delimiter2 = 99999.99;
			$range_price2->add();
			foreach ($zones as $z)
			{
				/*
				 * No prices for economy shipments in the country only zone.
				 */
				if ($mode == BoxdropShipment::CONF_MODE_DIRECT_ECONOMY && $country_zone->id == $z['id_zone'])
					continue;

				/*
				 * We are NOT using Carrier::addZone() here, as its filling the database with zeroes in the price matrix.
				 */
				Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'carrier_zone`(`id_carrier`, `id_zone`)VALUES('.$carrier->id.', '.$z['id_zone'].')');
				/*
				 * We cannot use the object classes "Delivery" here, as it is not allowing to put NULL into id_shop and id_shop_group
				 * which is needed in single shop constellations, when the price matirx query will ask for those values "IS NULL"
				 *
				 * So we will borrow the source from Carrier::addZone() here, to avoid selecting the zero-inserted price values and insert it directly,
				 * as there is no documentation on Carrier::addDeliveryPrice() in direct relation with a new CarrierZone.
				 */
				$sql = 'INSERT INTO `'._DB_PREFIX_.'delivery` (`id_carrier`, `id_range_price`, `id_range_weight`, `id_zone`, `price`) VALUES ';
				$sql .= '('.$carrier->id.', '.$range_price->id.', 0, '.$z['id_zone'].', '.(float)$default_shipping_price.'), ';
				$sql .= '('.$carrier->id.', 0, '.$range_weight->id.', '.$z['id_zone'].', '.(float)$default_shipping_price.'), ';
				$sql .= '('.$carrier->id.', '.$range_price2->id.', 0, '.$z['id_zone'].', 0), ';
				$sql .= '('.$carrier->id.', 0, '.$range_weight->id.', '.$z['id_zone'].', 0);';
				Db::getInstance()->execute($sql);
			}
		}

		/**
		 * Deletes all price and weight ranges for a given carrier
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  integer $carrier_id
		 */
		private static function deleteRangesByCarrier($carrier_id)
		{
			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'range_price` WHERE `id_carrier` = '.(int)$carrier_id.';');
			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'range_weight` WHERE `id_carrier` = '.(int)$carrier_id.';');
		}

		/**
		 * Deletes all delivery connections for a given carrier
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  integer $carrier_id
		 */
		private static function deleteDeliveriesByCarrier($carrier_id)
		{
			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'delivery` WHERE `id_carrier` = '.(int)$carrier_id.';');
		}

		/**
		 * Deletes all carrier zone connections for a given carrier
		 *
		 * @author sweber  <sw@boxdrop.com>
		 * @param  integer $carrier_id
		 */
		private static function deleteCarrierZonesByCarrier($carrier_id)
		{
			Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'carrier_zone` WHERE `id_carrier` = '.(int)$carrier_id.';');
		}

		/**
		 * Deletes a carrier and restores another carrier as the default
		 *
		 * @author sweber <sw@boxdrop.com>
		 * @param  string $mode
		 * @return boolean
		 */
		public static function deleteCarrier($mode = self::CONF_MODE_DROPOFF)
		{
			$carrier_id = BoxdropHelper::getCarrierId($mode);
			$default_lang_id = (int)Configuration::get('PS_LANG_DEFAULT');
			$default_carrier_id = (int)Configuration::get('PS_CARRIER_DEFAULT');
			if ($carrier_id !== null)
			{
				$carriers = Carrier::getCarriers($default_lang_id, true, false, false, null, PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
				$delete_carrier = new Carrier($carrier_id);
				/*
				 * restore another default carrier
				 */
				if ($carrier_id == $default_carrier_id)
				{
					foreach ($carriers as $carrier)
					{
						if ($carrier['active'] && !$carrier['deleted'] && ($carrier['name'] != $carrier->name))
							Configuration::updateValue('PS_CARRIER_DEFAULT', $carrier['id_carrier']);

					}
				}
				if (!$delete_carrier->delete())
					return false;

			}
			return true;
		}
	}
