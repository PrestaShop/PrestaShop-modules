<?php

/**
 * Objet XML <transaction>, contenu dans les résultat get_alert et get_validation et get_validstack
 *
 * @version 3.1
 * @author ESPIAU Nicolas
 */
class TransactionResponse extends XMLResult {
    /**
     * retourne le détail de la validation ou de l'erreur pour la transaction
     *
     * @return string
     */
    public function getDetail() {
        $detail = array_pop($this->getChildrenByName('detail'));

        return $detail->getValue();
    }

    /**
     * retourne la valeur de l'atribut Item si existant, null sinon
     *
     * @param string $name
     * @return string
     */
    private function getEvalItem($name) {
        $eval = array_pop($this->getChildrenByName('eval'));

        $eval = new XMLResult($eval->getXML());

        $funcname = "return$name";
        return $eval->$funcname();
    }

    /**
     * retourne l'évaluation de la transaction
     *
     * @return int
     */
    public function getEval() {
        $eval = array_pop($this->getChildrenByName('eval'));

        return $eval->getValue();
    }

    /**
     * retourne l'ID du classement de la transaction
     *
     * @return int
     */
    public function getClassementID() {
        $classement = array_pop($this->getChildrenByName('classement'));

        return $classement->getAttribute('id');
    }

    /**
     * retourne le libellé du classement de la transaction
     *
     * @return string
     */
    public function getClassementLabel() {
        $classement = array_pop($this->getChildrenByName('classement'));

        return $classement->getValue();
    }

    public function __call($name, array $params) {
        //fonction getEvalItem : retourne la valeur de l'attribut Item de la balise <eval> si existant, null sinon
        if (preg_match('#^getEval.+$#', $name)) {
            $elementname = strtolower(preg_replace('#^getEval(.+)$#', '$1', $name));

            return $this->getEvalItem($elementname);
        }

        return parent::__call($name, $params);
    }
}