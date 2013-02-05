<?php

/**
 * Classe abstraite pour les services proposés (Sac / Kwixo)
 *
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
abstract class SACService extends Mother {

    protected $siteid;
    protected $login;
    protected $password;
    protected $passwordurlencoded;
    protected $authkey;
    protected $status;
    protected $url = array();

    public function __construct() {
        //définition du nom du service en fonction de la classe de l'objet
        $name = strtolower(get_class($this));

        //on charge les paramètres du site en mémoire
        $siteparams = Spyc::YAMLLoad(SAC_ROOT_DIR . '/lib/' . $name . '/const/site_params.yml');
        foreach ($siteparams as $key => $value) {
            $funcname = "set$key";
            $this->$funcname($value);
        }

        //si le service n'est pas activé, on log une erreur
        if ($this->getStatus() === false) {
            insertLog(__METHOD__ . ' : ' . __LINE__, 'le service ' . $name . ' n\'est pas activé. Vérifier le paramétrage.');
        }

        //on charge les url des scripts en mémoire si le service est actif
        $this->loadURLs();
    }

    /**
     * charge les URLs des scripts du service, selon le mode de fonctionnement enregistré
     *
     * @version 3.1
     */
    private function loadURLs() {
        //définition du nom du service en fonction de la classe de l'objet
        $name = strtolower(get_class($this));
        //on charge les url des scripts en mémoire si le service est actif
        if (!($this->getStatus() === false)) {
            $url = Spyc::YAMLLoad(SAC_ROOT_DIR . '/lib/' . $name . '/const/url.yml');
            foreach ($url as $script => $modes) {
                $this->url[$script] = $modes[$this->getStatus()];
            }
        }
    }

    /**
     * retourne l'url du script demandé selon le mode de fonctionnement du service si il existe, génére une exception sinon
     *
     * @param string $script nom du script
     * @return string url correspondante
     */
    public function getUrl($script) {
        if (!array_key_exists($script, $this->url)) {
            $msg = "L'url pour le script $script n'existe pas ou n'est pas chargée. Vérifiez le paramétrage.";
            insertLog(get_class($this) . '->getUrl()', $msg);
            //throw new Exception($msg);
        }

        return $this->url[$script];
    }

    /**
     * modifie le paramétrage du mode de fonctionnement du module (test, prod, off)
     *
     * @version 3.1
     * @param bool $mode
     * @return bool vrai si la mise à jour est ok, faux sinon
     */
    public function switchMode($mode) {
        //si le mode n'est pas reconnu
        if (!in_array($mode, array('test', 'prod', 'off'))) {
            //l'erreur est loguée
            insertLog(__FILE__, "Le mode '$mode' n'est pas reconnu.");
            return false;
        }

        //sinon on change la valeur
        $this->setStatus($mode);

        //on recharge les URLs
        $this->loadURLs();
    }

    /**
     * sauvegarde les paramètres mis à jour dans le fichier de config et retourne vrai si les paramètres ont été sauvegardés, faux sinon
     *
     * @version 3.1
     * @return bool
     */
    public function saveParamInFile() {
        $name = strtolower(get_class($this));
        //on charge les paramètres du site en mémoire
        $siteparams = Spyc::YAMLLoad(SAC_ROOT_DIR . '/lib/' . $name . '/const/site_params.yml');

        foreach ($siteparams as $param => $value) {
            $funcname = "get$param";
            $newparams[$param] = $this->$funcname();
        }

        $yaml_string = Spyc::YAMLDump($newparams);
        //définition du nom du service en fonction de la classe de l'objet
        $handle = fopen(SAC_ROOT_DIR . '/lib/' . $name . '/const/site_params.yml', 'w');
        $written = @fwrite($handle, $yaml_string);
        fclose($handle);

        return $written;
    }

    public function __call($name, array $params) {
        //si la fonction appelée est préfixée par "getUrl"
        if (preg_match('#^getUrl.+$#', $name) > 0) {
            return $this->getUrl(preg_replace('#^getUrl(.+)$#', '$1', $name));
        }

        return parent::__call($name, $params);
    }

}