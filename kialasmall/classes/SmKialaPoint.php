<?php
/* 2007-2011 PrestaShop
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
*  @version  Release: $Revision: 11467 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class SmKialaPoint
{
	public $short_id;
	public $name;
	public $street;
	public $zip;
	public $city;
	public $location_hint;
	public $available;
	public $code;
	public $opening_hours;
	public $picture;
	public $coordinate;

	public $status;

	public static function getPointFromXml($kp)
	{
		$kiala_point = new self();
		$kiala_point->short_id = (string)$kp['shortId'];
		$kiala_point->name = (string)$kp->name;
		$kiala_point->street = (string)$kp->address->street;
		$kiala_point->zip = (string)$kp->address->zip;
		$kiala_point->city = (string)$kp->address->city;
		$kiala_point->location_hint = (string)$kp->address->locationHint;
		$kiala_point->available = (int)$kp->status['available'];
		$kiala_point->code = (string)$kp->status['code'];
		$kiala_point->opening_hours = array();
		foreach($kp->openingHours->day as $day)
			$kiala_point->opening_hours[(string)$day['name']] = (array)$day->timespan;
		if (isset($kp->picture))
			$kiala_point->picture = (string)$kp->picture['href'];
		$kiala_point->coordinate = (array)$kp->coordinate;

		return $kiala_point;
	}

	public static function getPointListFromXml($xml)
	{
		if (!$xml)
			return false;
		$points = array();
		foreach ($xml->kp as $kp)
			$points[] = self::getPointFromXml($kp);
		return $points;
	}
}