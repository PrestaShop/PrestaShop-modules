<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FianetTools
 *
 * @author nespiau
 */
class CertissimTools extends CertissimMother
{

  /**
   * retourne vrai si $string est uns chaine XML valide, faux sinon
   *
   * @param string $string chaine à tester
   * @return bool
   */
  static function isXMLstring($string)
  {
    //on vérifie si des balises sont présentes avec ou sans déclaration xml
    preg_match('#^(<\?xml.+\?>[\r\n ]*)?<([^( |>)]+).*>.*</(.+)>$#s', $string, $output);
    preg_match('#^(<\?xml.+\?>[\r\n ]*)?<([^( |>)]+).*/>$#s', $string, $output2);

    //retourne vrai si des balises sont présentes et si les balises ont le meme nom
    return (count($output) != 0 && ($output[2] == $output[3])) || count($output2) != 0;
  }

  /**
   * retourne vrai si l'objet $object et de type $type, faux sinon
   *
   * @param string $type nom de la classe attendue
   * @param mixed $object objet à tester
   * @return bool
   */
  static function isType($type, $object)
  {
    //retourne faux directement si le paramètre n'est pas un objet
    if (!is_object($object))
      return false;

    return (get_class($object) == $type || in_array($type, class_parents($object)));
  }

  /**
   * retour vrai si l'objet en paramètre est un objet XMLElement, faux sinon
   *
   * @param mixed $input
   * @return bool
   */
  static function isXMLElement($input)
  {
    return self::isType('CertissimXMLElement', $input);
  }

  /**
   * retour vrai si l'objet en paramètre est un objet FormField, faux sinon
   *
   * @param mixed $input
   * @return boolean
   */
  static function isFormField($input)
  {
    return self::isType('CertissimFormField', $input);
  }

  /**
   * retour vrai si l'objet en paramètre est un objet Form, faux sinon
   *
   * @param mixed $input
   * @return boolean
   */
  static function isForm($input)
  {
    return self::isType('CertissimForm', $input);
  }

  /**
   * retourne vrai si $input est un objet de classe SimpleXMLElement, faux sinon
   *
   * @param mixed $input objet à tester
   * @return bool
   */
  static function isSimpleXMLElement($input)
  {
    return self::isType('SimpleXMLElement', $input);
  }

  /**
   * converti une chaine en chaine valide pour une balise XML, exemple : OptionsPaiement devient options-paiment
   * @param string $name
   */
  static function normalizeName($name)
  {
    $string = strtolower($name[0]);
    $i = 1;
    for ($i; $i < strlen($name); $i++) {
      if (ord($name[$i]) >= ord('A') && ord($name[$i]) <= ord('Z'))
      {
        $string .= '-';
      }

      $string .= strtolower($name[$i]);
    }

    return $string;
  }

  /**
   * ins�re une erreur en haut du fichier de log, en le cr�ant s'il n'existe pas d�j�
   *
   * @param string $func nom de la fonction reportant le bug
   * @param string $msg description de l'erreur
   */
  static function insertLog($func, $msg)
  {
    //si le fichier log existe mais a d�pass� 1Mo on le renomme pour en cr�er un vierge
    if (file_exists(SAC_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt') && filesize(SAC_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt') > 500000)
    {
      $prefix = SAC_ROOT_DIR.'/logs/fianetlog-';
      $base = date('YmdHis');
      $sufix = '.txt';
      $filename = $prefix.$base.$sufix;

      for ($i = 0; file_exists($filename); $i++)
        $filename = $prefix.$base."-$i".$sufix;

      rename(SAC_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt', $filename);
    }
    //si le fichier log n'existe pas on le cr�� vide
    if (!file_exists(SAC_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt'))
    {
      //cr�ation du fichier en �criture
      $handle = fopen(SAC_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt', 'w');

      $entry = date('d-m-Y h:i:s')." | ".__METHOD__." : ".__LINE__." | Cr�ation du fichier de log\r";

      fwrite($handle, $entry);

      //fermeture imm�diate du fichier
      fclose($handle);
    }

    //cr�ation d'une nouvelle entr�e
    $entry = date('d-m-Y h:i:s')." | $func | $msg\r";

    //ouverture du log principal
    $handle = fopen(SAC_ROOT_DIR.'/logs/'.sha1(_COOKIE_KEY_.'fianet_log').'.txt', 'a+');
    fwrite($handle, $entry);

    //fermeture imm�diate du fichier
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
