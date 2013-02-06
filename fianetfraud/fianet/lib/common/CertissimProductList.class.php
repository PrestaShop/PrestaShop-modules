<?php

/**
 * Class for tag <list>
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class CertissimProductList extends CertissimXMLElement
{

  public function __construct($produits = array())
  {
    parent::__construct("<list nbproduit='0'></list>");

    foreach ($produits as $produit) {
      $this->addProduit($produit);
    }
  }

  /**
   * adds the product in the list and increments the value of the attribute 'nbproduits' with the number of products added
   * 
   * @param mixed $produit array or CertissimXMLElement
   * @param array $attrs array containing product attributes
   * @return CertissimXMLElement product added
   */
  public function addProduit($produit, $attrs = array())
  {
    $produit = $this->childProduit($produit, $attrs, true);
    $this->addAttribute('nbproduit', $this->getAttribute('nbproduit') + $produit->getAttribute('nb'));

    return $produit;
  }

}