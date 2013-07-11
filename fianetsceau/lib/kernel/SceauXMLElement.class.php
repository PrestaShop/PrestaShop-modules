<?php

/**
 * Class XML
 * 
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class SceauXMLElement extends SceauMother
{

	protected $encoding = "UTF-8";
	protected $version = "1.0";
	protected $name = "";
	protected $value = "";
	protected $attributes = array();
	protected $children = array();

	public function __construct($data=null, $debug=false)
	{
		if (is_null($data))
		{
			$name = preg_replace('#^(sceau-)?(.*)$#', '$2', CertissimTools::normalizeName(get_class($this)));
			$this->setName($name);
		}


		if (is_string($data))
		{

			$data = preg_replace('#^[ \r\n'.chr(13).chr(10).']*#', '', $data);


			if (!SceauTools::isXMLstring($data))
			{
				$msg = "La chaine \"$data\" n'est pas valide";
				SceauTools::insertLog(__METHOD__.' : '.__LINE__, $msg);
				throw new Exception($msg);
			}


			$data = new SimpleXMLElement($data);
		}


		if (is_object($data) && get_class($data) == 'SimpleXMLElement')
		{
			$string = (string) $data;

			$this->name = $data->getName();

			$this->value = trim($string);

			foreach ($data->attributes() as $attname => $attvalue)
			{
				$this->attributes[$attname] = $attvalue;
			}

			foreach ($data->children() as $simplexmlelementchild)
			{
				$child = new SceauXMLElement($simplexmlelementchild);
				$this->addChild($child);
			}
		}
	}

	/**
	 * adds an attribute to the current object
	 * 
	 * @param string $name attribute's name
	 * @param string $value attribute's value
	 */
	public function addAttribute($name, $value)
	{
		$this->attributes[$name] = $value;
	}

	/**
	 * returns the value of the current element's attribute named $name
	 *
	 * @param string $name attribute's name
	 * @return string
	 */
	public function getAttribute($name)
	{
		return (isset($this->attributes[$name]) ? $this->attributes[$name] : null);
	}

	/**
	 * returns an array containing all the children of the current element that are namde $name
	 *
	 * @param string $name
	 * @return array
	 */
	public function getChildrenByName($name)
	{
		$children = array();

		foreach ($this->getChildren() as $child)
		{
			if ($child->getName() == $name)
				array_push($children, $child);

			$children = array_merge($children, $child->getChildrenByName($name));
		}

		return $children;
	}

	/**
	 * returns the first current object's child with the tagname given in param
	 *
	 * @param string $name
	 * @return SceauXMLElement 
	 */
	public function getChildByName($name)
	{
		$children = $this->getChildrenByName($name);
		$child = array_pop($children);
		return $child;
	}

	/**
	 * returns an array containing all the children of the current element that are named $name and where the attribute $attributename exists and equals $attributevalue if not null
	 *
	 * @param <type> $name
	 * @param <type> $attributename
	 * @param <type> $attributevalue
	 * @return <type> 
	 */
	public function getChildrenByNameAndAttribute($name, $attributename, $attributevalue=null)
	{
		$children = $this->getChildrenByName($name);

		foreach ($children as $key => $child)
		{
			if (is_null($child->getAttribute($attributename)) || (!is_null($attributevalue) && $child->getAttribute($attributename) != $attributevalue))
				unset($children[$key]);
		}

		return $children;
	}

	/**
	 * appends a child to the children and returns the child CertissimXMLElement object
	 * 
	 * @param mixed $input CertissimXMLElement, string or SimpleXMLElement
	 * @param string $value value of the child
	 * @param array $attributes attributes of the child
	 * @return XMLElement 
	 */
	public function addChild($input, $value=null, $attributes=array())
	{
		$input = $this->createChild($input, $value, $attributes);

		$this->children[] = $input;

		return $input;
	}

	/**
	 * stacks a child to the children and returns the child CertissimXMLElement object
	 * 
	 * @param mixed $input CertissimXMLElement, string or SimpleXMLElement
	 * @param string $value value of the child
	 * @param array $attributes attributes of the child
	 * @return XMLElement 
	 */
	public function stackChild($input, $value=null, $attributes=array())
	{
		$input = $this->createChild($input, $value, $attributes);

		array_unshift($this->children, $input);

		return $input;
	}

	/**
	 * normalizes $input into a CertissimXMLElement object with children
	 * use cases:
	 * createChild(XMLElement) --> won't do anything
	 * createChild(simpleXMLElement)
	 * createChild("<element a='1' b='2'>valeur</element>")
	 * createChild("element","valeur", array('a'=>1, 'b'=>2))
	 * 
	 * @param mixed $input
	 * @param string $value
	 * @param string $attributes
	 * @return XMLElement objet normalized
	 */
	private function createChild($input, $value=null, $attributes=array())
	{
		if (is_string($input) && !SceauTools::isXMLstring($input))
		{
			$str = "<$input";
			foreach ($attributes as $name => $val)
			{
				$str .= " $name='$val'";
			}
			$str .= '>';

			if (!is_null($value))
				$str .= $value;

			$str .= "</$input>";
			$input = new SimpleXMLElement($str);
		}

		if (is_string($input) || SceauTools::isSimpleXMLElement($input))
		{
			$input = new SceauXMLElement($input);
		}

		if (!SceauTools::isXMLElement($input))
		{
			$msg = "Le type du paramètre entré n'est pas pris en compte par la classe SceauXMLElement : ".get_class($input);
			SceauTools::insertLog(get_class($this)." - createChild()", $msg);
			throw new Exception($msg);
		}

		return $input;
	}

	/**
	 * returns true if the current object has no value and no child, false otherwise
	 *
	 * @return bool 
	 */
	public function isEmpty()
	{
		return ($this->getValue() == "" || is_null($this->getValue())) && ($this->countChildren() == 0);
	}

	/**
	 * returns the current object child count
	 *
	 * @return int
	 */
	public function countChildren()
	{
		return count($this->children);
	}

	/**
	 * returns the current object as a SimpleXMLElement object
	 * 
	 * @param boolean $recursive allow to add children into the result
	 * @return SimpleXMLElement 
	 */
	public function toSimpleXMLElement($recursive = false)
	{
		$simplexlmelementobject = new SimpleXMLElement('<'.$this->getName().'>'.$this->getValue().'</'.$this->getName().'>');

		foreach ($this->getAttributes() as $name => $value)
		{
			$simplexlmelementobject->addAttribute($name, $value);
		}

		if ($recursive)
			$this->attachChildren($simplexlmelementobject);

		return $simplexlmelementobject;
	}

	/**
	 * attaches all the children and their children of the current object to the object given in parameter
	 * 
	 * @param SimpleXMLElement $simplexmlelement
	 */
	public function attachChildren($simplexmlelement)
	{
		foreach ($this->getChildren() as $child)
		{
			$simplexmlelement_child = $simplexmlelement->addChild($child->getName(), $child->getValue());

			foreach ($child->getAttributes() as $name => $value)
			{
				$simplexmlelement_child->addAttribute($name, $value);
			}

			$child->attachChildren($simplexmlelement_child);
		}
	}

	/**
	 * returns the current object as a string
	 * 
	 * @param bool $withcdatas add CDATA sections or not
	 * @return type
	 */
	public function getXML()
	{
		$ret = preg_replace('#<\?xml(.+)?>#', '<?xml version="'.$this->getVersion().'" encoding="'.$this->getEncoding().'" ?>', $this->toSimpleXMLElement(true)->asXML());

		$ret = preg_replace('#[\r\n'.chr(10).chr(13).']#', '', $ret);

		$ret = preg_replace('#>( )+<#', '><', $ret);

		return ($ret);
	}

	/**
	 * returns a XML object in a string
	 * @return string XML string
	 */
	public function __toString()
	{
		return $this->getXML();
	}

	/**
	 * saves the XML string into a file
	 *
	 * @param string $filename file path
	 * @return string
	 */
	public function saveInFile($filename)
	{
		return $this->toSimpleXMLElement(true)->asXML($filename);
	}

	/**
	 *
	 * @param string $name
	 * @param array $params
	 * @return mixed 
	 */
	public function __call($name, array $params)
	{
		if (preg_match('#^get(.+)$#', $name, $out))
		{
			return $this->__get(strtolower($out[1]));
		}

		if (preg_match('#^set(.+)$#', $name, $out))
		{
			return $this->__set(strtolower($out[1]), $params[0]);
		}

		if (preg_match('#^child(.+)$#', $name, $out))
		{

			$elementname = strtolower($out[1]);


			$empty_allowed = (isset($params[2]) ? $params[2] : false);


			if (isset($params[0]) && SceauTools::isXMLElement($params[0]))
			{

				if ($params[0]->getName() != $elementname)
					throw new Exception("Le nom de la balise ne correspond pas : $elementname attendu, ".$params[0]->getName()." trouvé.");


				if (!$params[0]->isEmpty() || $empty_allowed)
					return $this->addChild($params[0]);


				return false;
			}


			$child = new SceauXMLElement("<$elementname></$elementname>");

			if (isset($params[1]))
			{
				foreach ($params[1] as $att => $value)
				{
					$child->addAttribute($att, $value);
				}
			}

			if ((!isset($params[0]) || is_null($params[0])))
			{
				if ($empty_allowed)
					return $this->addChild($child);

				return false;
			}

			if (is_string($params[0]) or is_int($params[0]))
			{
				if (SceauTools::isXMLstring($params[0]))
				{
					$granchild = $this->createChild($params[0]);
					$child->addChild($granchild);
				} else
				{
					$child->setValue($params[0]);
				}
			}

			if (!$child->isEmpty() || $empty_allowed)
				return $this->addChild($child);

			return false;
		}
	}

}