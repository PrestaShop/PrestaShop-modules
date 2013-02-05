<?php

/**
 * Objet XML <result>, résultat des scripts get_alert, get_validation et get_validstack
 *
 * @version 3.1
 * @author ESPIAU Nicolas
 */
class ResultResponse extends XMLResult {
    /**
     * retourne vrai si une transaction a pu être trouvée sur le Fscreener, faux sinon
     *
     * @return bool
     */
    public function hasBeenFound() {
        return $this->returnRetour() == 'trouvee';
    }

    /**
     * retourne vrai si une erreur a été rencontrée, faux sinon
     *
     * @return bool
     */
    public function hasError() {
        return in_array($this->returnRetour(), array('param_error', 'internal_error'));
    }

    /**
     * retourne un tableau d'objet GetValidationTransaction contenant toutes les transactions concernées par la référence en cours
     *
     * @return array
     */
    public function getTransactions() {
        $transactions = array();

        foreach ($this->getChildrenByName('transaction') as $transac){
            $transactions[] = new TransactionResponse($transac->getXML());
        }

        return $transactions;
    }
}