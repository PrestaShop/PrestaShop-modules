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
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
	* Class for <adresse> elements
	* 
	* @author ESPIAU Nicolas
*/
class SceauAddress extends SceauXMLElement
{
	const FORMAT = 1;

	public function __construct()
	{
		parent::__construct('adresse');
	}

/**
	* creates an object SceauXMLElement representing the element <appartement>, child of <adresse>, adds it to the current object then returns it
	* 
	* @param string $digicode1 first entry code
	* @param string $digicode2 seconde entry code
	* @param string $stairway name or number of the stairway to the flat
	* @param string $floor name or number of the floor
	* @param string $door name or number of the door of the flat
	* @param string $building name or number of the building
	* @return SceauXMLElement
*/
	public function createFlat($digicode1 = null, $digicode2 = null, $stairway = null, $floor = null, $door = null, $building = null)
	{
		$flat = $this->createChild('appartement');
		if (!is_null($digicode1))
			$flat->createChild('digicode1', $digicode1);
		if (!is_null($digicode2))
			$flat->createChild('digicode2', $digicode2);
		if (!is_null($stairway))
			$flat->createChild('escalier', $stairway);
		if (!is_null($floor))
			$flat->createChild('etage', $floor);
		if (!is_null($door))
			$flat->createChild('nporte', $door);
		if (!is_null($building))
			$flat->createChild('batiment', $building);
		return $flat;
	}

}