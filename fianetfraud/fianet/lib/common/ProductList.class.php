<?php

/**
 * objet pour la balise <list>
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class ProductList extends XMLElement {

    public function __construct($produits=array()) {
        parent::__construct("<list nbproduit='0'></list>");

        foreach ($produits as $produit) {
            $this->addProduit($produit);
        }
    }

    /**
     * ajoute le produit dans la liste, et incrémente l'attribut nbproduits du nombre de produit ajoutés
     * 
     * @param mixed $produit un tableau ou un objet XMLElement
     * @param array $attrs tableau regroupant les attributs, renseigné si $produit est sous forme de tableau
     * @return XMLElement le produit ajouté
     */
    public function addProduit($produit, $attrs=array()) {
        $produit = $this->childProduit($produit, $attrs, true);
        $this->addAttribute('nbproduit', $this->getAttribute('nbproduit') + $produit->getAttribute('nb'));

        return $produit;
    }

}