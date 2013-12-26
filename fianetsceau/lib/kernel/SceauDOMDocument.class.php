<?php

class SceauDOMDocument extends DOMDocument
{

	protected $root;

	public function __construct($xml_string)
	{
		parent::__construct('1.0', 'UTF-8');
		$this->loadXML($xml_string);
		$this->root = $this->childNodes->item(0);
	}

	/**
	 * 
	 * @return SceauXMLElement
	 */
	public function getRootElement()
	{
		return $this->root;
	}

	/**
	 * creates an SceauXMLElement then adds it to the root as a child then returns the child
	 * 
	 * @param string $name
	 * @param string $value
	 * @param array $attributes
	 * @return SceauXMLElement
	 */
	public function createChild($name, $value = null, array $attributes = array())
	{
		$child = $this->root->appendChild(new SceauXMLElement($name, $value));
		foreach ($attributes as $key => $attrval)
			$child->addAttribute($key, $attrval);
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
		return $this->root->appendChild($child);
	}

	public function addAttribute($name, $value = null)
	{
		$this->root->setAttribute($name, $value);
	}

	/**
	 * returns the first child with the name given in param if exists, returns null otherwise
	 * 
	 * @param string $name
	 * @return SceauXMLElement
	 */
	public function getOneElementByTagName($name)
	{
		$children = $this->root->getElementsByTagName($name);
		if (!empty($children))
			return $children->item(0);
		else
			return null;
	}

	/**
	 * returns a collection of SceauXMLElement found in the children of the current element matching with the search criterias given in param
	 * 
	 * @param string $name name of the searched elements
	 * @param string $attributename name of the attribute of the searched elements
	 * @param string $attributevalue value of the attribute. If null the presence of the attribute will be the only criteria
	 * @return DOMNodeList
	 */
	public function getElementsByTagNameAndAttribute($name, $attributename, $attributevalue = null)
	{
		//gets all the children name $name
		foreach ($this->root->getElementsByTagName($name) as $child)
			$children[] = $child;

		//drops children that don't match
		foreach ($children as $key => $child)
		//drops the child from the children array if attribute does not exist or its value does not match with the wanted value
			if (!$child->hasAttribute($attributename) || (!is_null($attributevalue) && $child->getAttribute($attributename) != $attributevalue))
				unset($children[$key]);
		return $children;
	}

	/**
	 * returns the first of the SceauXMLElement found in the children of the current element that matches with the criterias ginven in param if at least one found, returns null otherwise
	 * 
	 * @param string $name name of the searched elements
	 * @param string $attributename name of the attribute of the searched elements
	 * @param string $attributevalue value of the attribute. If null the presence of the attribute will be the only criteria
	 * @return SceauXMLElement
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

	public function __toString()
	{
		return $this->saveXML();
	}

}