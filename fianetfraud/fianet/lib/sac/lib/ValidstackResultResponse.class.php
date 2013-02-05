<?php

/**
 * Object XMLElement <result> enfant de <validstack>, obtenu en réponse au script stacking
 *
 * @author nespiau
 */
class ValidstackResultResponse extends XMLResult {

    /**
     * retour le détail sur la réception ou sur l'erreur d'envoi
     *
     * @return string libellé du détail
     */
    public function getDetail() {
        //récupération du libellé de l'erreur ou du détail sur l'avancement
        $detail = array_pop($this->getChildrenByName('detail'));

        return $detail->getValue();
    }

    /**
     * retourne vrai si une erreur empêche l'analyse, faux sinon
     *
     * @return bool présence d'erreur
     */
    public function hasError() {
        return !is_null($this->returnErrorid());
    }

}