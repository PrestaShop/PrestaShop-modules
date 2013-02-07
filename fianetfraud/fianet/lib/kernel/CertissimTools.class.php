<?php

/**
 * Helper functions
 *
 * @author nespiau
 */
class CertissimTools extends CertissimMother
{

  /**
   * returns true if $string is a valid XML string, false otherwise
   *
   * @param string $string
   * @return bool
   */
  static function isXMLstring($string)
  {
    //checks if tags are present with or without XML declaration
    preg_match('#^(<\?xml.+\?>[\r\n ]*)?<([^( |>)]+).*>.*</(.+)>$#s', $string, $output);
    preg_match('#^(<\?xml.+\?>[\r\n ]*)?<([^( |>)]+).*/>$#s', $string, $output2);

    //return true if tags are present and have the same name
    return (count($output) != 0 && ($output[2] == $output[3])) || count($output2) != 0;
  }

  /**
   * returns true if $object is of type $type, false otherwise
   *
   * @param string $type
   * @param mixed $object
   * @return bool
   */
  static function isType($type, $object)
  {
    //returns false if $object is not an object
    if (!is_object($object))
      return false;

    return (get_class($object) == $type || in_array($type, class_parents($object)));
  }

  /**
   * reutrns false if $object is of type CertissimXMLElement, false otherwise
   *
   * @param mixed $input
   * @return bool
   */
  static function isXMLElement($input)
  {
    return self::isType('CertissimXMLElement', $input);
  }

  /**
   * returns true if $object is of type CertissimFormField, false otherwise
   *
   * @param mixed $input
   * @return boolean
   */
  static function isFormField($input)
  {
    return self::isType('CertissimFormField', $input);
  }

  /**
   * returns true if $object is of type CertissimForm, false otherwise
   *
   * @param mixed $input
   * @return boolean
   */
  static function isForm($input)
  {
    return self::isType('CertissimForm', $input);
  }

  /**
   * returns true if $object is of type SimpleXMLElement, false otherwise
   *
   * @param mixed $input objet à tester
   * @return bool
   */
  static function isSimpleXMLElement($input)
  {
    return self::isType('SimpleXMLElement', $input);
  }

  /**
   * normalize string $name to be a valid XML tag name
   * 
   * @param string $name
   */
  static function normalizeName($name)
  {
    $string = strtolower($name[0]);
    $i = 1;
    for ($i; $i < strlen($name); $i++)
    {
      if (ord($name[$i]) >= ord('A') && ord($name[$i]) <= ord('Z'))
      {
        $string .= '-';
      }

      $string .= strtolower($name[$i]);
    }

    return $string;
  }

  /**
   * create a log file if does not exist
   * rename the log file if too heavy and creates a new one
   * inserts a log entry
   *
   * @param string $func funcname calling the log
   * @param string $msg message to log
   */
  static function insertLog($func, $msg)
  {
    //if log file already exists but is too heavy
    if (file_exists(SAC_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt') && filesize(SAC_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt') > 500000)
    {
      //initialize the new log file name
      $prefix = SAC_ROOT_DIR.'/logs/fianetlog-';
      $base = date('YmdHis');
      $sufix = '.txt';
      $filename = $prefix.$base.$sufix;

      //while the file with the given filename exists, increases the number at its end
      for ($i = 0; file_exists($filename); $i++)
        $filename = $prefix.$base."-$i".$sufix;

      //rename the log file
      rename(SAC_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt', $filename);
    }

    //if log file does not exist, creates an empty one
    if (!file_exists(SAC_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt'))
    {
      //opens the file in write mode
      $handle = fopen(SAC_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt', 'w');

      //creates a first entry
      $entry = date('d-m-Y h:i:s')." | ".__METHOD__." : ".__LINE__." | Création du fichier de log\r";

      //logs the entry
      fwrite($handle, $entry);

      //close the file
      fclose($handle);
    }

    //creates a new entry with the func and message given in parameters
    $entry = date('d-m-Y h:i:s')." | $func | $msg\r";

    //open the log file
    $handle = fopen(SAC_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt', 'a+');
    //write the entry inside the log file
    fwrite($handle, $entry);

    //close the file
    fclose($handle);
  }

  static function convert_encoding($string, $to, $from = '')
  {
    // Convert string to ISO_8859-1
    if ($from == "UTF-8")
      $iso_string = utf8_decode($string);
    else
    if ($from == "UTF7-IMAP")
      $iso_string = imap_utf7_decode($string);
    else
      $iso_string = $string;

    // Convert ISO_8859-1 string to result coding
    if ($to == "UTF-8")
      return(utf8_encode($iso_string));
    else
    if ($to == "UTF7-IMAP")
      return(imap_utf7_encode($iso_string));
    else
      return($iso_string);
  }

}
