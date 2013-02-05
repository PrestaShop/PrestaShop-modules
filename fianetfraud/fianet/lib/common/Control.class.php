<?php

class Control extends XMLElement {
    /* !
      __construct : initie un flux XML avec la balise controle sans enfants
     */

    public function __construct() {
        parent::__construct("<control fianetmodule='api_prestashop_certissim' version='2.2'></control>");
    }

    /**
     * ajoute l'enfant <crypt> à <wallet>
     *
     * @param string $crypt valeur du crypt
     * @param string $version version du crypt
     */
    public function addCrypt($crypt, $version) {
        //on recherche la présence de la balise crypt
        $elements = $this->getChildrenByName('crypt');
        //si déjà présente
        if (count($elements) > 0) {
            //on ajoute la valeur et la version
            $cryptelement = array_pop($elements);
            $cryptelement->setAttribute('version', $version);
            $cryptelement->setValue($crypt);
        } else { //si non existante
            //création de la balise
            $cryptelement = new XMLElement('<crypt version="' . $version . '">' . $crypt . '</crypt>');
            //récupération de l'objet Wallet
            $wallet = array_pop($this->getChildrenByName('wallet'));
            //affectationd de la balise crypt à l'objet Wallet
            $wallet->childCrypt($cryptelement);
        }
    }

    /**
     * ajoute l'enfant <datelivr> à <wallet>
     *
     * @param date $date
     */
    public function addDatelivr($date){
        //on recherche la présence de la balise datelivr
        $elements = $this->getChildrenByName('datelivr');
        //si déjà présente
        if (count($elements) > 0) {
            //on ajoute la valeur et la version
            $datelivrelement = array_pop($elements);
            $datelivrelement->setValue($date);
        } else { //si non existante
            //création de la balise
            $datelivrelement = new XMLElement('<datelivr>' . $date . '</datelivr>');
            //récupération de l'objet Wallet
            $wallet = array_pop($this->getChildrenByName('wallet'));
            //affectationd de la balise crypt à l'objet Wallet
            $wallet->childDatelivr($datelivrelement);
        }
    }

}