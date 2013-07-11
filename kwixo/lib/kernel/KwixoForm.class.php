<?php

/**
 * Implements an HTML form
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 * 
 * @method void setAction(string $action) defines the action attribute of the form tag
 * @method void setName(string $name) defines the attribute name of the form tag
 * @method void setMethod(string $method) defines the attribute method of the form tag
 * @method void setClass(string $class) defines the attribute class of the form tag
 * @method void setId(string $id) defines the attribute id of the form tag
 * @method void setAutosubmit(bool $autosubmit) sets the form to be submitted automatically or not
 */
class KwixoForm extends KwixoMother {

  const SUBMIT_STANDARD = 'standard';
  const SUBMIT_AUTO = 'auto';
  const SUBMIT_IMAGE = 'image';

  protected $action;
  protected $name;
  protected $method = 'POST';
  protected $id;
  protected $class;
  protected $autosubmit = false;
  protected $fields = array();

  public function __construct($action = null, $name = 'fianet_form', $method = 'POST', array $fields = null, $class = null, $id = null) {
    if (is_null($id))
      $id = time();

    $this->setAction($action);
    $this->setName($name);
    $this->setMethod($method);
    $this->setClass($class);
    $this->setId($id);

    if (is_array($fields)) {
      foreach ($fields as $field) {
        $this->addField($field);
      }
    }
  }

  /**
   * adds a field to the current form
   * 
   * @param mixed $field field to add as an array or a FormField object
   */
  public function addField($field) {
    //adds the field directly to the form fields if $field is a FormField object
    if (isFormFieldKwixo($field)) {
      $this->fields[] = $field;
    }

    //creates an object KwixoFormField and adds it to the fields list if $field is an array
    if (is_array($field)) {
      $new_field = new KwixoFormField();
      //if $field contains a label key
      if (array_key_exists('label', $field)) {
        //sets the new field label
        $new_field->setLabel($field['label']);
        //destroy the label idex not to read it again later
        unset($field['label']);
      }
      
      //sets field params
      foreach ($field as $name => $value) {
        $funcname = 'set' . $name;
        $new_field->$funcname($value);
      }
      
      $this->fields[] = $new_field;
    }
  }

  /**
   * adds a submit button
   *
   * @param string $name name attribute
   * @param string $value value attribute
   * @param string $id id attribute
   * @param string $class class atrribute
   * @param string $label field label
   */
  public function addSubmit($name = null, $value = null, $id = null, $class = null, $label = null) {
    $this->addField(new KwixoFormField('submit', $name, $value, $id, $class, $label));
  }

  /**
   * adds an image submit button
   *
   * @param string $src image path
   * @param string $name name attribute
   * @param string $label field label
   * @param string $alt alt attribute
   * @param string $id id attribute
   * @param string $class class atrribute
   */
  public function addImageSubmit($src, $name, $label, $alt, $class = null, $id = null) {
    $this->addField(new KwixoFormFieldInputImage($src, $name, $label, $alt, $class, $id));
  }

  /**
   * returns the HTML string
   * 
   * @return string
   */
  public function __toString() {
    $str = '<form';
    if (!is_null($this->getId()))
      $str .= ' id="' . $this->getId() . '"';
    if (!is_null($this->getAction()))
      $str .= ' action="' . $this->getAction() . '"';
    if (!is_null($this->getName()))
      $str .= ' name="' . $this->getName() . '"';
    if (!is_null($this->getMethod()))
      $str .= ' method="' . $this->getMethod() . '"';
    $str .= '>';

    foreach ($this->getFields() as $field) {
      $str .= $field->__toString();
    }

    if ($this->getAutosubmit())
      $str .= '<script type="text/javascript">document.getElementById(' . $this->getId() . ').submit()</script>';

    $str .= '</form>';

    return $str;
  }

  /**
   * returns the field in an HTML <table>
   * 
   * @param type $withLabels
   * @return string
   */
  public function toArray($withLabels = true) {
    $str = '<form';
    if (!is_null($this->getId()))
      $str .= ' id="' . $this->getId() . '"';
    if (!is_null($this->getAction()))
      $str .= ' action="' . $this->getAction() . '"';
    if (!is_null($this->getName()))
      $str .= ' name="' . $this->getName() . '"';
    if (!is_null($this->getMethod()))
      $str .= ' method="' . $this->getMethod() . '"';
    $str .= '>';

    $str .= '<table>';
    foreach ($this->getFields() as $field) {
      $str .= $field->toArrayRow(($field->getType() == 'submit' ? false : $withLabels));
    }
    $str .= '</table>';

    if ($this->getAutosubmit())
      $str .= '<script type="text/javascript" language="javascript">document.getElementById(' . $this->getId() . ').submit()</script>';

    $str .= '</form>';

    return $str;
  }

}