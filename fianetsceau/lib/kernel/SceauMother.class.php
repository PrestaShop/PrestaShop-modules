<?php

/**
 * Mother class, providing getters and setters *
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 * 
 * @method string getAttributename() returns the value of the attribute named Attributename
 * Usage :
 * <code>
 * $user->setFirstname('Jonny'); //creates an attribute 'firstname' in the object, and sets it to 'Jonny'
 * echo $user->getFirstname(); //displays the value of the attribute 'firstname' : Jonny
 * </code>
 */
abstract class SceauMother {

  /**
   * returns the value of the attribute $name
   * 
   * @param string $name
   * @return mixed 
   */
  public function __get($name) {
    return $this->$name;
  }

  /**
   * sets the attribute value
   * 
   * @param string $name name of the attribute to set
   * @param mixed $value value to set
   */
  public function __set($name, $value) {
    $this->$name = $value;
  }

  public function __call($name, array $params) {
    if (preg_match('#^get(.+)$#', $name, $out))
      return $this->__get(strtolower($out[1]));

    if (preg_match('#^set(.+)$#', $name, $out))
      return $this->__set(strtolower($out[1]), $params[0]);
  }

}