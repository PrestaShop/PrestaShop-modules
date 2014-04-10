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

class SceauXMLElement extends DOMElement
{

	public function addAttribute($name, $value = null)
	{
		$this->appendChild(new DOMAttr($name, htmlspecialchars($value)));
	}

	/**
	 * creates an SceauXMLElement then adds as a child then returns the child
	 * 
	 * @param string $name
	 * @param string $value
	 * @param array $attributes
	 * @return SceauXMLElement
	 */
	public function createChild($name, $value = null, array $attributes = array(), $cdata = true)
	{
		$child = $this->appendChild(new SceauXMLElement($name));
		if ($cdata && constant('SCEAU_USE_CDATA') !== false)
			$child->appendChild(new DOMCdataSection($value));
		else
			$child->node_value = $value;
		foreach ($attributes as $attrname => $attrvalue)
			$child->appendChild(new DOMAttr($attrname, htmlspecialchars($attrvalue)));

		return $child;
	}

	/**
	 * adds the DOMElement given in param as a child and returns it
	 * 
	 * @param SceauXMLElement $child
	 * @return SceauXMLElement
	 */
	public function addChild(SceauXMLElement $child)
	{
		return $this->appendChild($child);
	}

	/**
	 * returns the first child with the name given in param if exists, returns null otherwise
	 * 
	 * @param string $name
	 * @return FianetXMLElement
	 */
	public function getOneElementByName($name)
	{
		$children = $this->getElementsByTagName($name);
		if (!empty($children))
			return $children->item(0);
		else
			return null;
	}

	/**
	 * 
	 * @param type $name
	 * @param type $attributename
	 * @param type $attributevalue
	 * @return SceauXMLElement
	 */
	public function getElementsByTagNameAndAttribute($name, $attributename, $attributevalue = null)
	{
		//gets all the children name $name
		$children = $this->getElementsByTagName($name);

		//drops children that don't match
		foreach ($children as $key => $child)
		{
		//drops the child from the children array if attribute does not exist or its value does not match with the wanted value
			if (!$child->hasAttribute($attributename) || (!is_null($attributevalue)
				&& $child->getAttribute($attributename) != htmlspecialchars($attributevalue)))
				unset($children[$key]);
		}
		return $children;
	}

	/**
	 * 
	 * @param type $name
	 * @param type $attributename
	 * @param type $attributevalue
	 * @return SceauXMLElement
	 */
	public function getOneElementByTagNameAndAttribute($name, $attributename, $attributevalue = null)
	{
		//gets all the matching children
		$children = $this->getElementsByTagNameAndAttribute($name, $attributename, $attributevalue);
		//returns the first one if exists, null otherwise
		if (!empty($children))
			return $children->item(0);
		else
			return null;
	}

}