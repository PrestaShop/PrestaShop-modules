<?php

/**
 * Class CertissimXMLElement, represent a XML object, uses native PHP class SimpleXMLElement but with more options and methods
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class CertissimXMLElement extends CertissimMother
{

  protected $encoding = "UTF-8";
  protected $name = "";
  protected $value = "";
  protected $attributes = array();
  protected $children = array();

  public function __construct($data = null)
  {
    //if no data given, build a tag using the name of the class (removing the prefix 'certissim-'
    if (is_null($data))
    {
      $name = preg_replace('#^(certissim-)?(.*)$#', '$2', CertissimTools::normalizeName(get_class($this)));
      $this->setName($name);
    }

    //f $data is a string
    if (is_string($data))
    {
      //remove spaces at the begining of the string
      $data = preg_replace('#^[ \r\n]*#', '', $data);
      //checks the XML validity of the string
      if (!CertissimTools::isXMLstring($data))
      {
        $msg = "La chaine \"$data\" n'est pas valide";
        CertissimTools::insertLog(get_class($this).' - __construct()', $msg);
        throw new Exception($msg);
      }

      //sets the encoding to the wanted encoding according to the local var $this->encoding
      preg_match('#^<\?xml.+encoding= ?[\'|\"]([^\"\']+).+$#s', $data, $out);
      if (isset($out[1]) && $out[1] != "")
      {
        $actualencoding = $out[1];
        $wantedencoding = $this->getEncoding();
        //converts the string into the wanted encoding
        $data = CertissimTools::convert_encoding($data, $wantedencoding, $actualencoding);
      }
      else
      {
        $data = CertissimTools::convert_encoding($data, $this->getEncoding());
      }
      //converts the XML string into a SimpleXMLElement object
      $data = new SimpleXMLElement($data);
    }

    //if $data is a SimpleXMLElement object
    if (is_object($data) && get_class($data) == 'SimpleXMLElement')
    {
      //gets the data as a string
      $string = (string) $data;
      //converts the encoding to the wanted one
      preg_match('#^<\?xml.+encoding= ?[\'|\"]([^\"\']+).+$#s', $string, $out);
      if (isset($out[1]) && $out[1] != "")
      {
        $actualencoding = $out[1];
        $wantedencoding = $this->getEncoding();
        //converts the string into the wanted encoding
        $data = CertissimTools::convert_encoding($data, $wantedencoding, $actualencoding);
      }
      else
      {
        $data = CertissimTools::convert_encoding($data, $this->getEncoding());
      }
      //registers the tag name
      $this->name = $data->getName();
      //registers the tag value
      $this->value = $string;
      //registers the attributes
      foreach ($data->attributes() as $attname => $attvalue)
      {
        $this->attributes[$attname] = $attvalue;
      }
      //register the children
      foreach ($data->children() as $simplexmlelementchild)
      {
        $child = new CertissimXMLElement($simplexmlelementchild);
        $this->addChild($child);
      }
    }
  }

  /**
   * adds an attribute to the current object
   * 
   * @param string $name attribute name
   * @param string $value attribute value
   */
  public function addAttribute($name, $value)
  {
    $this->attributes[$name] = $value;
  }

  /**
   * returns the value of the attribute $name
   *
   * @param string $name attribute name
   * @return string
   */
  public function getAttribute($name)
  {
    return (isset($this->attributes[$name]) ? $this->attributes[$name] : null);
  }

  /**
   * returns a table containing all the chidren named $name
   *
   * @param string $name
   * @return array
   */
  public function getChildrenByName($name)
  {
    //initializes an empty array
    $children = array();

    //checks all the chidren
    foreach ($this->getChildren() as $child)
    {
      //if the name matches, the child is added into the array
      if ($child->getName() == $name)
        array_push($children, $child);

      //finds the matching children inside the current child
      $children = array_merge($children, $child->getChildrenByName($name));
    }

    return $children;
  }

  /**
   * return an array containing all the children name $name and having an attribute named $attributename that has for value $attributevalue (if not null)
   *
   * @param <type> $name
   * @param <type> $attributename
   * @param <type> $attributevalue
   * @return <type> 
   */
  public function getChildrenByNameAndAttribute($name, $attributename, $attributevalue = null)
  {
    //gets the list of children that have a matching name
    $children = $this->getChildrenByName($name);

    //checks the attributes of each preselected children
    foreach ($children as $key => $child)
    {
      //if the attribute does not exist or its value does not match
      if (is_null($child->getAttribute($attributename)) || (!is_null($attributevalue) && $child->getAttribute($attributename) != $attributevalue))
      //the child is removed from the array
        unset($children[$key]);
    }

    return $children;
  }

  /**
   * adds a child to the current object after already existings children and returns the added child object
   * 
   * @param mixed $input child to add, of type CertissimXMLElement string or SimpleXMLElement
   * @param string $value child value
   * @param array $attributes child attributes
   * @return XMLElement 
   */
  public function addChild($input, $value = null, $attributes = array())
  {
    //normalize the child: permit to add sub children
    $input = $this->createChild($input, $value, $attributes);

    //add the child into the children table
    $this->children[] = $input;

    return $input;
  }

  /**
   * adds a child to the current object, before already existing childrend, and returns the added child object
   * 
   * @param mixed $input child to add, of type CertissimXMLElement string or SimpleXMLElement
   * @param string $value child value
   * @param array $attributes child attributes
   * @return XMLElement 
   */
  public function stackChild($input, $value = null, $attributes = array())
  {
    //normalize the child: permit to add sub children
    $input = $this->createChild($input, $value, $attributes);

    //add the child at the top of the children table
    array_unshift($this->children, $input);

    return $input;
  }

  /**
   * normalizes $input into a CertissimXMLElement with children
   * call cases:
   * createChild(CertissimXMLElement) --> won't do anything
   * createChild(simpleXMLElement)
   * createChild("<element a='1' b='2'>valeur</element>")
   * createChild("element","valeur", array('a'=>1, 'b'=>2))
   * 
   * @param mixed $input object to normalize, type string expected as the name of the element, or SimpleXMLElement as an entire object
   * @param string $value object value (if $input is a string)
   * @param string $attributes
   * @return CertissimXMLElement
   */
  private function createChild($input, $value = null, $attributes = array())
  {
    //if $input is a non XML formated string, builds a XML formated string based on $input as a name et $value as a value and $attributes as attributes
    if (is_string($input) && !CertissimTools::isXMLstring($input))
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

    //if $input is a XML formated string or a SimpleXMLElement object
    if (is_string($input) || CertissimTools::isSimpleXMLElement($input))
    {
      //conversion into a CertissimXMLElement
      $input = new CertissimXMLElement($input);
    }

    //at this step, if $input is not a CertissimXMLElement it's ignored
    if (!CertissimTools::isXMLElement($input))
    {
      $msg = "Le paramètre entré n'est pas pris en compte par la classe XMLElement";
      CertissimTools::insertLog(get_class($this)." - createChild()", $msg);
      throw new Exception($msg);
    }

    return $input;
  }

  /**
   * returns true if the value is empty and the current object has no child, false otherwise
   *
   * @return bool 
   */
  public function isEmpty()
  {
    return ($this->getValue() == "" || is_null($this->getValue())) && ($this->countChildren() == 0);
  }

  /**
   * returns the number of first level children of the current object
   *
   * @return int
   */
  public function countChildren()
  {
    return count($this->children);
  }

  /**
   * returns the SimpleXMLElement object corresponding to the current object
   * 
   * @param boolean $recursive allow to scan and return children and gran children
   * @return SimpleXMLElement 
   */
  public function toSimpleXMLElement($recursive = false)
  {
    //SimpleXMLElement object generation
    $simplexlmelementobject = new SimpleXMLElement('<'.$this->getName().'>'.$this->getValue().'</'.$this->getName().'>');

    //addition of the attributes
    foreach ($this->getAttributes() as $name => $value)
    {
      $simplexlmelementobject->addAttribute($name, $value);
    }

    //if recusrivity set to true, scan of children
    if ($recursive)
      $this->attachChildren($simplexlmelementobject);

    return $simplexlmelementobject;
  }

  /**
   * attach all the descendants to the SimpleXMLElement object given
   * 
   * @param SimpleXMLElement $simplexmlelement
   */
  public function attachChildren($simplexmlelement)
  {
    //foreach child of the current object
    foreach ($this->getChildren() as $child)
    {
      //creating a SimpleXMLElement object and adding it to the object given in parameter
      $simplexmlelement_child = $simplexmlelement->addChild($child->getName(), $child->getValue());

      //adding the attrbutes
      foreach ($child->getAttributes() as $name => $value)
      {
        $simplexmlelement_child->addAttribute($name, $value);
      }

      //adding children of the loop child to the object given in parameter
      $child->attachChildren($simplexmlelement_child);
    }
  }

  /**
   * returns the object as an XML string
   * 
   * @return string
   */
  public function getXML()
  {
    //adding the XML declaration in the string
    $ret = preg_replace('#^.*(<\?xml.+)(\?>)#is', '$1 encoding="'.$this->getEncoding().'"$2', $this->toSimpleXMLElement(true)->asXML());
    //encoding security
    $ret = html_entity_decode($ret, ENT_NOQUOTES, $this->getEncoding());
    //removes carriage returns
    $ret = preg_replace('#[\r\n'.chr(10).chr(13).']#', '', $ret);
    //removes spaces between tags
    $ret = preg_replace('#>( )+<#', '><', $ret);

    return ($ret);
  }

  /**
   * returns the object as an XML string
   * 
   * @return string
   */
  public function __toString()
  {
    return $this->getXML();
  }

  /**
   * enregistre la chaine XML dans un fichier
   *
   * @param string $filename chemin du fichie
   * @return string
   */
  public function saveInFile($filename)
  {
    return $this->toSimpleXMLElement(true)->asXML($filename);
  }

  public function __call($name, array $params)
  {
    //if the called method is prefix by the string 'get', it's a getting method
    if (preg_match('#^get(.+)$#', $name, $out))
    {
      return $this->__get(strtolower($out[1]));
    }
    //if the called method is prefix by the string 'set', it's a setting method
    if (preg_match('#^set(.+)$#', $name, $out))
    {
      return $this->__set(strtolower($out[1]), $params[0]);
    }

    //if the called method is prefix by the string 'child', it's a method that adds a child to the current object
    if (preg_match('#^child(.+)$#', $name, $out))
    {
      //gets the name of the object to add
      $elementname = strtolower($out[1]);

      //sets the boolean that allow to have empty tag
      $empty_allowed = (isset($params[2]) ? $params[2] : false);

      //if a param is given and it's a CertisismXMLElement, it's added directly as the child if its name match with the suffix of the called method
      if (isset($params[0]) && CertissimTools::isXMLElement($params[0]))
      {
        $childname = preg_replace('#^(certissim-)?(.*)$#', '$2', $params[0]->getName());
        //if the name does not match, throwing an error
        if ($childname != $elementname)
          throw new Exception("Le nom de la balise ne correspond pas : $elementname attendu, ".$childname." trouvé.");

        //if the element is not empty or if empty tags are allowed, adding the child to the current object
        if (!$params[0]->isEmpty() || $empty_allowed)
          return $this->addChild($params[0]);

        //if emtpy not allowed, end of process
        return false;
      }

      //creating the child object
      $child = new CertissimXMLElement("<$elementname></$elementname>");
      //if attributes were given in params, adding them to the new object
      if (isset($params[1]))
      {
        foreach ($params[1] as $att => $value)
        {
          $child->addAttribute($att, $value);
        }
      }

      //if no params and empty tag allowed, adding the child obect to the current object
      if ((!isset($params[0]) || is_null($params[0])))
      {
        if ($empty_allowed)
          return $this->addChild($child);

        //if emtpy not allowed, end of process
        return false;
      }

      //if the param is a string
      if (is_string($params[0]) or is_int($params[0]))
      {
        //if it's an XML string, creates and adds the child to the current object
        if (CertissimTools::isXMLstring($params[0]))
        {
          $granchild = $this->createChild($params[0]);
          $child->addChild($granchild);
        }
        else
        {
          //if it's not an XML string, it's added as a value
          $child->setValue($params[0]);
        }
      }

      //adding the child
      if (!$child->isEmpty() || $empty_allowed)
        return $this->addChild($child);

      return false;
    }
  }

}