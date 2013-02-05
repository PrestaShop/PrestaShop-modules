<?php

/**
 * Description of Paiement
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class Paiement extends XMLElement {
    const TYPE_CARTE = 'carte';
    const TYPE_CHEQUE = 'cheque';
    const TYPE_REMBOURSEMENT = 'contre-remboursement';
    const TYPE_VIREMENT = 'virement';
    const TYPE_CBNFOIS = 'cb en n fois';
    const TYPE_PAYPAL = 'paypal';
    const TYPE_1EURO = '1euro.com';

    public function __construct($type=null, $nom=null, $bin=null, $numcb=null, $dateval=null, $bin4=null, $bin42=null) {
        parent::__construct();
        $this->childType($type);
        $this->childNom($nom);
        $this->childBin($bin);
        $this->childNumcb($numcb);
        $this->childDateval($dateval);
        $this->childBin4($bin4);
        $this->childBin42($bin42);
    }

}