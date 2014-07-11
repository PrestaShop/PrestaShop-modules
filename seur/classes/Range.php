<?php
/**
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @version  Release: 0.4.4
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class Range extends ObjectModel
{
	/* countries */
	protected static $spain;

	protected static $portugal_country;

	/* zones */
	protected static $provincia;

	protected static $peninsula;

	protected static $portugal;

	protected static $baleares;

	protected static $canarias;

	protected static $ceuta_melilla;

	protected static $array_isos_zones;

	/* carriers */
	protected static $carrier_seur;

	protected static $carrier_pos;

	protected static $carrier_canarias_48;

	protected static $carrier_canarias_m;

	protected static $array_ids_carriers;

	/* ranges */

	protected static $ranges;

	public static function setRanges()
	{
		self::init();

		if (!self::createZones())
			return false;
		if (!self::asignCarriersToZones())
			return false;
		if (!self::createCountries())
			return false;
		if (!self::createStates())
			return false;
		if (!self::createDefaultRanges())
			return false;

		return true;
	}

	private static function init()
	{
		self::$ranges = array(0 => 2, 2 => 3, 3 => 5, 5 => 10, 10 => 11, 11 => 12, 12 => 13, 13 => 14, 14 => 15);

		$historyTable = SeurLib::getSeurCarriers();

		if (!empty($historyTable))
		{
			foreach ($historyTable as $historyCarrier)
			{
				switch ($historyCarrier['type'])
				{
					case 'SEN':
					self::$carrier_seur = new Carrier((int)$historyCarrier['id']);
					self::$carrier_seur->active = 1;
					self::$carrier_seur->deleted = 0;
					break;
					case 'SEP':
					self::$carrier_pos = new Carrier((int)$historyCarrier['id']);
					self::$carrier_pos->active = 0;
					self::$carrier_pos->deleted = 0;
					break;
					case 'SCN':
					self::$carrier_canarias_m = new Carrier((int)$historyCarrier['id']);
					self::$carrier_canarias_m->active = 1;
					self::$carrier_canarias_m->deleted = 0;
					break;
					case 'SCE':
					self::$carrier_canarias_48 = new Carrier((int)$historyCarrier['id']);
					self::$carrier_canarias_48->active = 1;
					self::$carrier_canarias_48->deleted = 0;
					break;
					default;
				}
			}
		}
	}

	private static function disableCountries()
	{
		$spain_ant = new Country(Country::getByIso('ES'));
		$portugal_ant = new Country(Country::getByIso('PT'));

		if (!Validate::isLoadedObject($spain_ant) || !Validate::isLoadedObject($portugal_ant))
				return false;

		$spain_ant->active = false;
		$portugal_ant->active = false;

		if (!$spain_ant->update() || !$portugal_ant->update())
			return false;

		return true;
	}

	private static function createZones()
	{
		$id_provincia = Zone::getIdByName(SeurLib::$seur_zones[0]);
		if ($id_provincia)
		{
			self::$provincia = new Zone($id_provincia);
			self::$provincia->active = 1;
		}
		else
		{
			self::$provincia = new Zone();
			self::$provincia->name = html_entity_decode(SeurLib::$seur_zones[0]);
		}

		$id_peninsula = Zone::getIdByName(SeurLib::$seur_zones[1]);
		if ($id_peninsula)
		{
			self::$peninsula = new Zone($id_peninsula);
			self::$peninsula->active = 1;
		}
		else
		{
			self::$peninsula = new Zone();
			self::$peninsula->name = html_entity_decode(SeurLib::$seur_zones[1]);
		}

		$id_portugal = Zone::getIdByName(SeurLib::$seur_zones[2]);
		if ($id_portugal)
		{
			self::$portugal = new Zone($id_portugal);
			self::$portugal->active = 1;
		}
		else
		{
			self::$portugal = new Zone();
			self::$portugal->name = html_entity_decode(SeurLib::$seur_zones[2]);
		}

		$id_baleares = Zone::getIdByName(SeurLib::$seur_zones[3]);
		if ($id_baleares)
		{
			self::$baleares = new Zone($id_baleares);
			self::$baleares->active = 1;
		}
		else
		{
			self::$baleares = new Zone();
			self::$baleares->name = html_entity_decode(SeurLib::$seur_zones[3]);
		}

		$id_canarias = Zone::getIdByName(SeurLib::$seur_zones[4]);
		if ($id_canarias)
		{
			self::$canarias = new Zone($id_canarias);
			self::$canarias->active = 1;
		}
		else
		{
			self::$canarias = new Zone();
			self::$canarias->name = html_entity_decode(SeurLib::$seur_zones[4]);
		}

		$id_ceuta_melilla = Zone::getIdByName(SeurLib::$seur_zones[5]);
		if ($id_ceuta_melilla)
		{
			self::$ceuta_melilla = new Zone($id_ceuta_melilla);
			self::$ceuta_melilla->active = 1;
		}
		else
		{
			self::$ceuta_melilla = new Zone();
			self::$ceuta_melilla->name = html_entity_decode(SeurLib::$seur_zones[5]);
		}

		if (!self::$provincia->save() ||
			!self::$peninsula->save() ||
			!self::$portugal->save() ||
			!self::$baleares->save() ||
			!self::$canarias->save() ||
			!self::$ceuta_melilla->save())
			return false;

		return true;
	}

	private static function asignCarriersToZones()
	{
		if (!self::$carrier_seur->addZone(self::$provincia->id) ||
			!self::$carrier_seur->addZone(self::$peninsula->id) ||
			!self::$carrier_seur->addZone(self::$portugal->id) ||
			!self::$carrier_seur->addZone(self::$baleares->id) ||
			!self::$carrier_seur->addZone(self::$ceuta_melilla->id))
			return false;

		if (self::$carrier_seur->id != self::$carrier_pos->id)
			if (!self::$carrier_pos->addZone(self::$provincia->id) ||
				!self::$carrier_pos->addZone(self::$peninsula->id) ||
				!self::$carrier_pos->addZone(self::$portugal->id) ||
				!self::$carrier_pos->addZone(self::$baleares->id) ||
				!self::$carrier_pos->addZone(self::$ceuta_melilla->id))
				return false;

		if (!self::$carrier_canarias_48->addZone(self::$canarias->id))
			return false;

		if (self::$carrier_canarias_48->id != self::$carrier_canarias_m->id)
			if (!self::$carrier_canarias_m->addZone(self::$canarias->id))
				return false;

		return true;
	}

	private static function createCountries()
	{
		if (!Country::getByIso('ES') || !Country::getByIso('PT'))
		{
			if (!self::disableCountries())
				return false;

			$modulos = SeurLib::getModulosPago();
			$values = array();
			$currency = Currency::getDefaultCurrency();

			/* ESPAÑA */

			if (!Country::getByIso('ES'))
			{
				self::$spain = new Country();
				self::$spain->name = array (1 => 'Spain', 2 => 'Espagne',3 => 'España');
				self::$spain->id_zone = self::$peninsula->id;
				self::$spain->id_currency = $currency->id;
				self::$spain->iso_code = 'ES';
				self::$spain->contains_states = true;
				self::$spain->need_identification_number = true;

				if (!self::$spain->save())
					return false;

				foreach ($modulos as $modulo)
					$values[] = '('.(int)$modulo->id.', '.(int)self::$spain->id.')';

				if (!empty($values))
					Db::getInstance()->Execute('
						INSERT INTO '._DB_PREFIX_.'module_country (`id_module`, `id_country`) 
						VALUES '.implode(',', $values)
					);
			}

			/* PORTUGAL */

			if (!Country::getByIso('PT'))
			{
				self::$portugal_country = new Country();
				self::$portugal_country->name = array (1 => 'Portugal', 2 => 'Portugal',3 => 'Portugal');
				self::$portugal_country->id_zone = self::$portugal->id;
				self::$portugal_country->id_currency = $currency->id;
				self::$portugal_country->iso_code = 'PT';
				self::$portugal_country->contains_states = false;
				self::$portugal_country->need_identification_number = false;

				if (!self::$portugal_country->save())
					return false;

				foreach ($modulos as $modulo)
					$values[] = '('.(int)$modulo->id.', '.(int)self::$portugal_country->id.')';

				if (!empty($values))
					Db::getInstance()->Execute('
						INSERT INTO '._DB_PREFIX_.'module_country (`id_module`, `id_country`) 
						VALUES '.implode(',', $values)
					);
			}
		}
		else
		{
			self::$spain = new Country((int)Country::getByIso('ES'));
			self::$spain->active = true;
			self::$spain->contains_states = true;

			if (!self::$spain->save())
				return false;

			self::$portugal_country = new Country((int)Country::getByIso('PT'));
			self::$portugal_country->active = true;
			self::$portugal_country->id_zone = self::$portugal->id;

			if (!self::$portugal_country->save())
				return false;
		}

		return true;
	}

	private static function createStates()
	{
		$ps_state_iso_code_max_length = 7;

		if (version_compare(_PS_VERSION_, '1.5', '<'))
			$ps_state_iso_code_max_length = 4;

		foreach (SeurLib::$baleares_states as $iso_code => $state_name)
		{
			if ((Tools::strlen($iso_code) > $ps_state_iso_code_max_length))
			{
				$tmpArray = explode('-', $iso_code);
				$iso_code = $tmpArray[0];

				if (count($tmpArray) > 0)
					$iso_code = 'E'.$tmpArray[1];
			}

			$exists_id = State::getIdByIso($iso_code);

			if (isset($exists_id) && !empty($exists_id))
			{
				$state = new State($exists_id);
				$state->active = true;
				$state->id_zone = self::$baleares->id;

				if (!$state->update())
					return false;
			}
			else
			{
				$state = new State();
				$state->name = $state_name;
				$state->id_country = self::$spain->id;
				$state->id_zone = self::$baleares->id;
				$state->iso_code = $iso_code;
				$state->active = true;

				if (!$state->save())
					return false;
			}
		}

		foreach (SeurLib::$canarias_states as $iso_code => $state_name)
		{
			if ((Tools::strlen($iso_code) > $ps_state_iso_code_max_length))
			{
				$tmpArray = explode('-', $iso_code);
				$iso_code = $tmpArray[0];

				if (count($tmpArray) > 0)
					$iso_code = 'E'.$tmpArray[1];
			}

			$exists_id = State::getIdByIso($iso_code);

			if (isset($exists_id) && !empty($exists_id))
			{
				$state = new State($exists_id);
				$state->active = true;
				$state->id_zone = self::$canarias->id;

				if (!$state->update())
					return false;
			}
			else
			{
				$state = new State();
				$state->name = $state_name;
				$state->id_country = self::$spain->id;
				$state->id_zone = self::$canarias->id;
				$state->iso_code = $iso_code;
				$state->active = true;

				if (!$state->save())
					return false;
			}
		}

		foreach (SeurLib::$ceuta_melilla_states as $iso_code => $state_name)
		{
			if ((Tools::strlen($iso_code) > $ps_state_iso_code_max_length))
			{
				$tmpArray = explode('-', $iso_code);
				$iso_code = $tmpArray[0];

				if (count($tmpArray) > 0)
					$iso_code = 'E'.$tmpArray[1];
			}

			$exists_id = State::getIdByIso($iso_code);

			if (isset($exists_id) && !empty($exists_id))
			{
				$state = new State($exists_id);
				$state->id_zone = self::$ceuta_melilla->id;
				$state->active = true;

				if (!$state->update())
					return false;
			}
			else
			{
				$state = new State();
				$state->name = $state_name;
				$state->id_country = self::$spain->id;
				$state->id_zone = self::$ceuta_melilla->id;
				$state->iso_code = $iso_code;
				$state->active = true;

				if (!$state->save())
					return false;
			}
		}

		foreach (SeurLib::$spain_states as $iso_code => $state_name)
		{
			if ((Tools::strlen($iso_code) > $ps_state_iso_code_max_length))
			{
				$tmpArray = explode('-', $iso_code);
				$iso_code = $tmpArray[0];

				if (count($tmpArray) > 0)
					$iso_code = 'E'.$tmpArray[1];
			}

			$exists_id = State::getIdByIso($iso_code);

			if (isset($exists_id) && !empty($exists_id))
			{
				$state = new State($exists_id);
				$state->active = true;

				if (Tools::strtoupper($state_name) == Tools::strtoupper(SeurLib::getMerchantField('state')))
					$state->id_zone = self::$provincia->id;
				else
					$state->id_zone = self::$peninsula->id;

				if (!$state->update())
					return false;
			}
			else
			{
				$state = new State();
				$state->name = $state_name;
				$state->id_country = self::$spain->id;

				if (Tools::strtoupper($state_name) == Tools::strtoupper(SeurLib::getMerchantField('state')))
					$state->id_zone = self::$provincia->id;
				else
					$state->id_zone = self::$peninsula->id;

				$state->iso_code = $iso_code;
				$state->active = true;

				if (!$state->save())
					return false;
			}
		}

		return true;
	}


	private static function createDefaultRanges()
	{
		$ids_ranges_seur = array();
		$ids_ranges_pos = array();
		$ids_ranges_canarias_48 = array();
		$ids_ranges_canarias_m = array();

		if (self::$carrier_seur->id != self::$carrier_pos->id &&
			self::$carrier_seur->id != self::$carrier_canarias_48->id &&
			self::$carrier_seur->id != self::$carrier_canarias_m->id)
			foreach (self::$ranges as $from => $to)
			{
				$range_seur = new RangeWeight();
				$range_seur->id_carrier = self::$carrier_seur->id;
				$range_seur->delimiter1 = $from;
				$range_seur->delimiter2 = $to;
				$range_seur->save();
				$ids_ranges_seur[$from] = $range_seur->id;
			}

		if (self::$carrier_pos->id != self::$carrier_seur->id &&
			self::$carrier_pos->id != self::$carrier_canarias_48->id &&
			self::$carrier_pos->id != self::$carrier_canarias_m->id)
			foreach (self::$ranges as $from => $to)
			{
				$range_pos = new RangeWeight();
				$range_pos->id_carrier = self::$carrier_pos->id;
				$range_pos->delimiter1 = $from;
				$range_pos->delimiter2 = $to;
				$range_pos->save();
				$ids_ranges_pos[$from] = $range_pos->id;

			}

		if (self::$carrier_canarias_48->id != self::$carrier_seur->id &&
			self::$carrier_canarias_48->id != self::$carrier_pos->id &&
			self::$carrier_canarias_48->id != self::$carrier_canarias_m->id)
			foreach (self::$ranges as $from => $to)
			{
				$range_canarias_48 = new RangeWeight();
				$range_canarias_48->id_carrier = self::$carrier_canarias_48->id;
				$range_canarias_48->delimiter1 = $from;
				$range_canarias_48->delimiter2 = $to;
				$range_canarias_48->save();
				$ids_ranges_canarias_48[$from] = $range_canarias_48->id;
			}

		if (self::$carrier_canarias_m->id != self::$carrier_seur->id &&
			self::$carrier_canarias_m->id != self::$carrier_pos->id &&
			self::$carrier_canarias_m->id != self::$carrier_canarias_48->id)
			foreach (self::$ranges as $from => $to)
			{
				$range_canarias_m = new RangeWeight();
				$range_canarias_m->id_carrier = self::$carrier_canarias_m->id;
				$range_canarias_m->delimiter1 = $from;
				$range_canarias_m->delimiter2 = $to;
				$range_canarias_m->save();
				$ids_ranges_canarias_m[$from] = $range_canarias_m->id;
			}

		$range_table_seur = self::$carrier_seur->getRangeTable();
		$range_table_pos = self::$carrier_pos->getRangeTable();
		$range_table_48 = self::$carrier_canarias_48->getRangeTable();
		$range_table_m = self::$carrier_canarias_m->getRangeTable();

		self::$carrier_seur->deleteDeliveryPrice($range_table_seur);
		self::$carrier_pos->deleteDeliveryPrice($range_table_pos);
		self::$carrier_canarias_48->deleteDeliveryPrice($range_table_48);
		self::$carrier_canarias_m->deleteDeliveryPrice($range_table_m);

		if (version_compare(_PS_VERSION_, '1.5', '>='))
		{
			$priceListSeur = array();
			$priceListPos = array();
			$priceListC_48 = array();
			$priceListSeurC_M = array();

			foreach (self::$ranges as $from => $to)
			{
				/*
				 * carrier_seur
				 */

				$priceListSeur[] = array(
					'id_range_price' =>'NULL',
					'id_range_weight' => (int)$ids_ranges_seur[$from],
					'id_carrier' => (int)self::$carrier_seur->id,
					'id_zone' => (int)self::$provincia->id,
					'price' => 0);
				$priceListSeur[] = array(
					'id_range_price' =>'NULL',
					'id_range_weight' => (int)$ids_ranges_seur[$from],
					'id_carrier' => (int)self::$carrier_seur->id,
					'id_zone' => (int)self::$peninsula->id,
					'price' => 0);
				$priceListSeur[] = array(
					'id_range_price' =>'NULL',
					'id_range_weight' => (int)$ids_ranges_seur[$from],
					'id_carrier' => (int)self::$carrier_seur->id,
					'id_zone' => (int)self::$portugal->id,
					'price' => 0);
				$priceListSeur[] = array(
					'id_range_price' =>'NULL',
					'id_range_weight' => (int)$ids_ranges_seur[$from],
					'id_carrier' => (int)self::$carrier_seur->id,
					'id_zone' => (int)self::$baleares->id,
					'price' => 0);
				$priceListSeur[] = array(
					'id_range_price' =>'NULL',
					'id_range_weight' => (int)$ids_ranges_seur[$from],
					'id_carrier' => (int)self::$carrier_seur->id,
					'id_zone' => (int)self::$ceuta_melilla->id,
					'price' => 0);

				/*
				 * carrier_pos
				 */
				if (self::$carrier_seur->id != self::$carrier_pos->id)
				{
					$priceListPos[] = array(
						'id_range_price' =>'NULL',
						'id_range_weight' => (int)$ids_ranges_seur[$from],
						'id_carrier' => (int)self::$carrier_pos->id,
						'id_zone' => (int)self::$provincia->id,
						'price' => 0);
					$priceListPos[] = array(
						'id_range_price' =>'NULL',
						'id_range_weight' => (int)$ids_ranges_seur[$from],
						'id_carrier' => (int)self::$carrier_pos->id,
						'id_zone' => (int)self::$peninsula->id,
						'price' => 0);
					$priceListPos[] = array(
						'id_range_price' =>'NULL',
						'id_range_weight' => (int)$ids_ranges_seur[$from],
						'id_carrier' => (int)self::$carrier_pos->id,
						'id_zone' => (int)self::$portugal->id,
						'price' => 0);
					$priceListPos[] = array(
						'id_range_price' =>'NULL',
						'id_range_weight' => (int)$ids_ranges_seur[$from],
						'id_carrier' => (int)self::$carrier_pos->id,
						'id_zone' => (int)self::$baleares->id,
						'price' => 0);
					$priceListPos[] = array(
						'id_range_price' =>'NULL',
						'id_range_weight' => (int)$ids_ranges_seur[$from],
						'id_carrier' => (int)self::$carrier_pos->id,
						'id_zone' => (int)self::$ceuta_melilla->id,
						'price' => 0);
				}

				/*
				 * carrier_canarias_48
				 */

				$priceListC_48[] = array(
					'id_range_price' =>'NULL',
					'id_range_weight' => (int)$ids_ranges_seur[$from],
					'id_carrier' => (int)self::$carrier_canarias_48->id,
					'id_zone' => (int)self::$canarias->id,
					'price' => 0);

				/*
				 * carrier_canarias_m
				 */
				if (self::$carrier_canarias_48->id != self::$carrier_canarias_m->id)
				{
					$priceListSeurC_M[] = array(
						'id_range_price' =>'NULL',
						'id_range_weight' => (int)$ids_ranges_seur[$from],
						'id_carrier' => (int)self::$carrier_canarias_m->id,
						'id_zone' => (int)self::$canarias->id,
						'price' => 0);
				}

			}
			self::$carrier_seur->addDeliveryPrice($priceListSeur);

			if (self::$carrier_seur->id != self::$carrier_pos->id)
				self::$carrier_pos->addDeliveryPrice($priceListPos);

			self::$carrier_canarias_48->addDeliveryPrice($priceListC_48);

			if (self::$carrier_canarias_48->id != self::$carrier_canarias_m->id)
				self::$carrier_canarias_m->addDeliveryPrice($priceListSeurC_M);
		}
		else
		{
			foreach (self::$ranges as $from => $to)
			{
				/*
				 * self::$carrier_seur
				 */
				self::$carrier_seur->addDeliveryPrice('(NULL, '.(int)$ids_ranges_seur[$from].', '.(int)self::$carrier_seur->id.', '.(int)self::$provincia->id.', 0)');
				self::$carrier_seur->addDeliveryPrice('(NULL, '.(int)$ids_ranges_seur[$from].', '.(int)self::$carrier_seur->id.', '.(int)self::$peninsula->id.', 0)');
				self::$carrier_seur->addDeliveryPrice('(NULL, '.(int)$ids_ranges_seur[$from].', '.(int)self::$carrier_seur->id.', '.(int)self::$portugal->id.', 0)');
				self::$carrier_seur->addDeliveryPrice('(NULL, '.(int)$ids_ranges_seur[$from].', '.(int)self::$carrier_seur->id.', '.(int)self::$baleares->id.', 0)');
				self::$carrier_seur->addDeliveryPrice('(NULL, '.(int)$ids_ranges_seur[$from].', '.(int)self::$carrier_seur->id.', '.(int)self::$ceuta_melilla->id.', 0)');

				/*
				 * self::$carrier_pos
				 */
				if (self::$carrier_seur->id != self::$carrier_pos->id)
				{
					self::$carrier_pos->addDeliveryPrice('(NULL, '.(int)$ids_ranges_pos[$from].', '.(int)self::$carrier_pos->id.', '.(int)self::$provincia->id.', 0)');
					self::$carrier_pos->addDeliveryPrice('(NULL, '.(int)$ids_ranges_pos[$from].', '.(int)self::$carrier_pos->id.', '.(int)self::$peninsula->id.', 0)');
					self::$carrier_pos->addDeliveryPrice('(NULL, '.(int)$ids_ranges_pos[$from].', '.(int)self::$carrier_pos->id.', '.(int)self::$portugal->id.', 0)');
					self::$carrier_pos->addDeliveryPrice('(NULL, '.(int)$ids_ranges_pos[$from].', '.(int)self::$carrier_pos->id.', '.(int)self::$baleares->id.', 0)');
					self::$carrier_pos->addDeliveryPrice('(NULL, '.(int)$ids_ranges_pos[$from].', '.(int)self::$carrier_pos->id.', '.(int)self::$ceuta_melilla->id.', 0)');
				}
				/*
				 * self::$carrier_canarias_48
				 */
				self::$carrier_canarias_48->addDeliveryPrice('(NULL, '.(int)$ids_ranges_canarias_48[$from].', '.(int)self::$carrier_canarias_48->id.', '.(int)self::$canarias->id.', 0)');

				/*
				 * self::$carrier_canarias_m
				 */
				if (self::$carrier_canarias_48->id != self::$carrier_canarias_m->id)
					self::$carrier_canarias_m->addDeliveryPrice('(NULL, '.(int)$ids_ranges_canarias_m[$from].', '.(int)self::$carrier_canarias_m->id.', '.(int)self::$canarias->id.', 0)');
			}
		}

		return true;
	}

}