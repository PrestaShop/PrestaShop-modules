<?php

/**
 * classe SAC avec toutes les méthodes d'accès aux services et scripts du SAC
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class Sac extends SACService {
    const INPUT_TYPE = 'text'; //type par défaut des champs du form. text pour le debug, hidden sinon
    const IDSEPARATOR = '^'; //séparateur des refid pour la méthode getvalidstack
    const CONSULT_MODE_MINI = 'mini';
    const CONSULT_MODE_FULL = 'full';

    /**
     * génère le formulaire de soumission du flux par redirection (script redirect.cgi) en soumission automatique si $autosubmit=true, en soumission manuelle (par l'internaute) si $autosubmit=false
     *
     * @param bool $autosubmit permet une soumission automatique ou manuelle ou par bouton image
     */
    public function generateRedirectForm(XMLElement $controlcallback, $urlcallback, $paracallback, $submittype=Form::SUBMIT_STANDARD, $imagepath=null) {
        //si le paracallback est un objet XMLElement on en déduit la chaine correspondante
        if (isXMLElement($paracallback))
            $paracallback = $paracallback->getXML();

        //définition des différents champs du form
        $fields = array(
            'siteid' => array('type' => Sac::INPUT_TYPE, 'name' => 'siteid', 'value' => $this->getSiteId()),
            'controlcallback' => array('type' => Sac::INPUT_TYPE, 'name' => 'controlcallback', 'value' => preg_replace('#"#', "'" , $controlcallback->getXML())),
            'urlcallback' => array('type' => Sac::INPUT_TYPE, 'name' => 'urlcallback', 'value' => $urlcallback),
            'paracallback' => array('type' => Sac::INPUT_TYPE, 'name' => 'paracallback', 'value' => $paracallback),
        );

        //instanciation du form
        $form = new Form($this->getUrlredirect(), 'submit_fianet_xml', 'POST', $fields);

        //ajout du submit
        switch ($submittype) {
            case Form::SUBMIT_IMAGE:
                $form->addImageSubmit($imagepath, 'payer', 'Payer', 'Payer', 'image_sumbit');
                break;

            case Form::SUBMIT_STANDARD:
                $form->addSubmit();
                break;

            case Form::SUBMIT_AUTO:
                $form->setAutosubmit(true);
                break;

            default:
                $msg = "Type submit non reconnu.";
                insertLog(__METHOD__ . " : " . __LINE__, $msg);
                break;
        }

        return $form;
    }

    /**
     * envoi une transaction au sac en post, via le script singet.cgi
     *
     * @param XMLElement $xml flux xml de la transaction
     * @param mixed $paracallback paramètres de retours
     */
    public function sendSinget(XMLElement $xml) {
        $data = array(
            'siteid' => $this->getSiteId(),
            'controlcallback' => $xml->getXML(),
        );
        $con = new FianetSocket($this->getUrlsinget(), 'POST', $data);
        $res = $con->send();
        return $res;
    }

    /**
     * envoi un stack de transactions via le script stacking.cgi
     *
     * @param XMLElement $stack
     * @return string réponse du script
     */
    public function sendStacking(XMLElement $stack) {
        $data = array(
            'siteid' => $this->getSiteId(),
            'controlcallback' => $stack->getXML(),
        );
        $con = new FianetSocket($this->getUrlstacking(), 'POST', $data);
        $result = $con->send();
        
        $xmlresult = ($result !== false ? new XMLElement($result) : false);

        return $xmlresult;
    }

    /**
     * envoi un stack de transactions via le script stackfast.cgi
     *
     * @param XMLElement $stack
     * @return string réponse du script
     */
    public function sendStackfast(XMLElement $stack) {
        $data = array(
            'siteid' => $this->getSiteId(),
            'controlcallback' => $stack->getXML(),
        );
        $con = new FianetSocket($this->getUrlstacking(), 'POST', $data);
        return new XMLElement($con->send());
    }

    /**
     * récupère l'évaluation de la transaction de référence $refid en mode $mode, avec ou sans réponse FT ($repFT à 1 ou 0)
     *
     * @param string $refid référence transaction marchand
     * @param string $mode mode de réponse (mini, full, ...)
     * @param bool $repFT affichage ou non de la réponse FT
     * @return string réponse du script
     */
    public function getValidation($refid, $mode='mini', $repFT='0') {
        $data = array(
            'SiteID' => $this->getSiteId(),
            'Pwd' => $this->getPassword(),
            'RefID' => $refid,
            'Mode' => $mode,
            'RepFT' => $repFT
        );
        $con = new FianetSocket($this->getUrlgetvalidation(), 'POST', $data);
        return new XMLElement($con->send());
    }

    /**
     * récupère l'évaluation de la transaction de référence $refid en mode $mode, avec ou sans réponse FT ($repFT à 1 ou 0) et envoi le résultat en POST à l'url urlback
     * si définie, à l'urlback par défaut si null
     *
     * @param string $refid référence transaction marchand
     * @param string $mode mode de réponse (mini, full, ...)
     * @param bool $repFT affichage ou non de la réponse FT
     * @param string $urlback url de renvoi de la réponse en post
     * @return string réponse du script
     */
    public function getRedirectValidation($refid, $mode=Sac::CONSULT_MODE_MINI, $urlback=null, $repFT='0') {
        $data = array(
            'SiteID' => $this->getSiteId(),
            'Pwd' => $this->getPassword(),
            'RefID' => $refid,
            'Mode' => $mode,
            'RepFT' => $repFT,
            'urlBack' => (!is_null($urlback) ? $urlback : $this->getUrldefaultredirectvaildationurlback()),
        );
        $con = new FianetSocket($this->getUrlredirectvalidation(), 'POST', $data);
        return new XMLElement($con->send());
    }

    /**
     * retourne la liste des résultats d'évaluation des transactions dont la ref est dans $listId
     *
     * @param array $listId tableau des références des transac à récupérer
     * @param string $mode mode de réponse (mini, full, ...)
     * @param bool $repFT affichage ou non de la réponse FT
     * @return string réponse du script
     */
    public function getValidstackByReflist(array $listId, $mode=Sac::CONSULT_MODE_MINI, $repFT='0') {
        //construction de la liste des id
        $list = '';
        foreach ($listId as $rid) {
            $list .= $rid . Sac::IDSEPARATOR;
        }

        $list = preg_replace('#^(.+)' . Sac::IDSEPARATOR . '$#', '$1', $list);

        $data = array(
            'SiteID' => $this->getSiteId(),
            'Pwd' => $this->getPassword(),
            'Mode' => $mode,
            'RepFT' => $repFT,
            'ListID' => $list,
            'Separ' => Sac::IDSEPARATOR
        );
        return $this->getValidstack($data);
    }

    /**
     * retourne la liste des résultats d'évaluation des commandes passées à la date $date
     *
     * @param array $date date scannée
     * @param int $numpage numéro de la page à consulter
     * @param string $mode mode de réponse (mini, full, ...)
     * @param bool $repFT affichage ou non de la réponse FT
     * @return string réponse du script
     */
    public function getValidstackByDate($date, $numpage, $mode=Sac::CONSULT_MODE_MINI, $repFT='0') {
        //vérifie que la date est au bon format
        if (!preg_match('#^[0-9]{2}/[0-1][0-9]/[0-9]{4}$#', $date)) {
            $msg = "La date '$date' n'est pas au bon format. Format attendu : dd/mm/YYYY";
            insertLog(__METHOD__ . " : " . __LINE__, $msg);
            throw new Exception($msg);
        }

        $data = array(
            'SiteID' => $this->getSiteId(),
            'Pwd' => $this->getPassword(),
            'Mode' => $mode,
            'RepFT' => $repFT,
            'DtStack' => $date,
            'Ind' => $numpage
        );
        return $this->getValidstack($data);
    }

    /**
     * fait un appel à getValidstack avec les paramèters dans $param
     *
     * @param array $param
     * @return string réponse du script
     */
    private function getValidstack($param) {
        $con = new FianetSocket($this->getUrlgetvalidstack(), 'POST', $param);
        return new XMLElement($con->send());
    }

    public function getAlert($mode='all', $output='mini', $repFT='0') {
        $data = array(
            'SiteID' => $this->getSiteId(),
            'Pwd' => $this->getPassword(),
            'Mode' => $mode,
            'Output' => $output,
            'RepFT' => $repFT,
        );
        $con = new FianetSocket($this->getUrlgetalert(), 'POST', $data);
        return new XMLElement($con->send());
    }

    /**
     * retourne la html du lien vers le détail de l'analyse de la commande de ref $rid
     *
     * @param string $rid référence de la commande
     * @param string $txt texte à afficher pour le lien. Si null la référence $rid servira de lien
     * @param string $target target du lien. Ouverture dans un nouvel onglet / fenêtre par défaut.
     * @return string code html du lien
     */
    public function getVisuCheckUrl($rid, $txt = null, $target = '_blank') {
        $url = $this->getUrlvisucheckdetail();
        $url .= '?sid=' . $this->getSiteid() . '&log=' . $this->getLogin() . '&pwd=' . $this->getPasswordurlencoded() . "&rid=$rid";

        $link = "<a href='$url'" . (!is_null($target) ? " target='$target'" : '') . ">$rid</a>";

        return $link;
    }

}