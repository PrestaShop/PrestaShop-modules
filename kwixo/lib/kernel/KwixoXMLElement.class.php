<?php

class KwixoXMLElement extends DOMElement
{

	public function addAttribute($name, $value = null)
	{
		$this->appendChild(new DOMAttr($name, htmlspecialchars($value)));
	}

	/**
	 * creates an KwixoXMLElement then adds as a child then returns the child
	 * 
	 * @param string $name
	 * @param string $value
	 * @param array $attributes
	 * @return KwixoXMLElement
	 */
	public function createChild($name, $value = null, array $attributes = array(), $cdata = true)
	{
		$child = $this->appendChild(new KwixoXMLElement($name));
		if ($cdata && constant('FIANET_USE_CDATA') !== false)
			$child->appendChild(new DOMCdataSection($value));
		else
			$child->nodeValue = $value;
		foreach ($attributes as $attrname => $attrvalue)
		{
			$child->appendChild(new DOMAttr($attrname, htmlspecialchars($attrvalue)));
		}
		return $child;
	}

	/**
	 * adds the DOMElement given in param as a child and returns it
	 * 
	 * @param KwixoXMLElement $child
	 * @return KwixoXMLElement
	 */
	public function addChild(KwixoXMLElement $child)
	{
		return $this->appendChild($child);
	}

	/**
	 * returns the first child with the name given in param if exists, returns null otherwise
	 * 
	 * @param string $name
	 * @return KwixoXMLElement
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
	 * @return KwixoXMLElement
	 */
	public function getElementsByTagNameAndAttribute($name, $attributename, $attributevalue = null)
	{
		//gets all the children name $name
		foreach ($this->getElementsByTagName($name) as $child)
			$children[] = $child;

		//drops children that don't match
		foreach ($children as $key => $child)
		//drops the child from the children array if attribute does not exist or its value does not match with the wanted value
			if (!$child->hasAttribute($attributename) || (!is_null($attributevalue) && $child->getAttribute($attributename) != htmlspecialchars($attributevalue)))
				unset($children[$key]);

		return $children;
	}

	/**
	 * 
	 * @param type $name
	 * @param type $attributename
	 * @param type $attributevalue
	 * @return KwixoXMLElement
	 */
	public function getOneElementByTagNameAndAttribute($name, $attributename, $attributevalue = null)
	{
		//gets all the matching children
		$children = $this->getElementsByTagNameAndAttribute($name, $attributename, $attributevalue);
		//returns the first one if exists, null otherwise
		if (!empty($children))
			return $children[0];
		else
			return null;
	}

}