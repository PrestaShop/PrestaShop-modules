<?php

/**
 * Class form form field
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class CertissimFormField extends CertissimMother
{

  protected $label; //field label
  protected $type; //field type
  protected $name; //field name
  protected $value; //field value
  protected $id; //id attribute of the field
  protected $class; //class attribute of the field

  public function __construct($type = 'text', $name = '', $value = '', $id = null, $class = 'standardfieldclass', $label = null)
  {
    $this->setType($type);
    $this->setName($name);
    $this->setValue($value);
    $this->setId($id);
    $this->setClass($class);
  }

  /**
   * returns true if the field is hidden, false otherwise
   *
   * @return boolean
   */
  public function isHidden()
  {
    return $this->getType() == 'hidden';
  }

  /**
   * returns the fiels as an HTML string, prefixed with the label if defined
   *
   * @return string
   */
  public function __toString()
  {
    $str = '<input';
    if (!is_null($this->getType()))
      $str .= ' type="'.$this->getType().'"';
    if (!is_null($this->getName()))
      $str .= ' name="'.$this->getName().'"';
    if (!is_null($this->getValue()))
      $str .= ' value="'.$this->getValue().'"';
    if (!is_null($this->getId()))
      $str .= ' id="'.$this->getId().'"';
    if (!is_null($this->getClass()))
      $str .= ' class="'.$this->getClass().'"';
    $str .= ' />';

    if (!is_null($this->getLabel()))
    {
      $label = '<span class="fieldlabel">'.$this->getLabel().'</span>';
    }
    else
    {
      $label = '<span class="fieldlabel">'.$this->getName().'</span>';
    }

    return $label.$str;
  }

  /**
   * returns the field as an HTML table row (<tr>), prefixed with the label if defined
   *
   * @param boolean $withLabel
   * @return string
   */
  public function toArrayRow($withLabel = true)
  {
    $str = '<tr'.($this->isHidden() ? ' style="display: none;"' : '').'><td>';

    if ($withLabel)
    {
      if (!is_null($this->getLabel()))
      {
        $label = '<span class="fieldlabel">'.$this->getLabel().'</span>';
      }
      else
      {
        $label = '<span class="fieldlabel">'.$this->getName().'</span>';
      }

      $str .= $label;
    }
    $str .= '</td><td>';

    $str .= '<input';
    if (!is_null($this->getType()))
      $str .= ' type="'.$this->getType().'"';
    if (!is_null($this->getName()))
      $str .= ' name="'.$this->getName().'"';
    if (!is_null($this->getValue()))
      $str .= ' value="'.$this->getValue().'"';
    if (!is_null($this->getId()))
      $str .= ' id="'.$this->getId().'"';
    if (!is_null($this->getClass()))
      $str .= ' type="'.$this->getClass().'"';
    $str .= ' />';
    $str .= '</td></tr>';

    return $str;
  }

}