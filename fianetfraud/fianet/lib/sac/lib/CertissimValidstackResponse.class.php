<?php

/**
 * Objet XMLElement <validstack> de retour des script d'envoi de transaction à Certissim (stacking)
 *
 * @version 3.1
 * @author ESPIAU Nicolas
 */
class CertissimValidstackResponse extends CertissimXMLResult
{

  const ROOT_NAME = "validstack";

  public function __construct($data)
  {
    $data = preg_replace('#\"#', '\'', $data);
    parent::__construct($data);

    if ($this->getName() != self::ROOT_NAME)
    {
      $msg = "L'�l�ment racine n'est pas valide : ".$this->getName()." trouve, ".self::ROOT_NAME." attendu.";
      CertissimTools::insertLog(__FILE__." - __construct()", $msg);
    }
  }

  /**
   * retourne vrai si le stack de transaction a été refusé, faux sinon
   *
   * @return bool
   */
  public function hasFatalError()
  {
    return count($this->getChildrenByName('unluck')) > 0;
  }

  /**
   * retourne le libellé de l'erreur si <unluck>, null sinon
   *
   * @return mixed
   */
  public function getError()
  {
    $unluck = $this->hasFatalError() ? array_pop($this->getChildrenByName('unluck'))->getValue() : null;

    return ($unluck);
  }

  /**
   * retourne un tableau contenant tous les éléments <result> sous forme d'objets SendStackResult
   *
   * @return array
   */
  public function getResults()
  {
    $results = array();
    foreach ($this->getChildrenByName('result') as $result) {
      $results[] = new CertissimValidstackResultResponse($result->getXML());
    }

    return $results;
  }

  public function getResultCount()
  {
    return count($this->$this->getChildrenByName('result'));
  }

}