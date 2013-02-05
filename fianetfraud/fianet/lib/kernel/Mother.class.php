<?php

/**
 * Classe mère, contenant les méthodes magique utilisables partout (getter, setter)
 *
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
abstract class Mother {

    /**
     * retourne la valeur de l'attribut $name
     * @param string $name nom de l'attribut à récupérer
     * @return mixed 
     * @todo ajouter les throw error pour les attributs inexistants
     */
    public function __get($name) {
        return $this->$name;
    }

    /**
     * défini l'attribut $name avec la valeur $value
     * @param string $name nom de l'attribut à définir
     * @param mixed $value valeur à définir
     * @return boolean 
     * @todo ajouter les throw error pour les attributs inexistants
     */
    public function __set($name, $value) {
        $this->$name = $value;
        return true;
    }

    /**
     * méthode appelée lorsqu'une méthode appelée dans une classe fille n'existe pas
     * appelle une méthode local selon certaines conditions
     *
     * @param string $name nom de la méthode appelée
     * @param array $params parmaètres passés
     * @return mixed
     */
    public function __call($name, array $params) {
        //si le préfixe est "get", c'est une méthode de lecture
        if (preg_match('#^get(.+)$#', $name, $out)) {
            return $this->__get(strtolower($out[1]));
        }
        //si le préfixe est "set", c'est une méthode d'écriture
        if (preg_match('#^set(.+)$#', $name, $out)) {
            return $this->__set(strtolower($out[1]), $params[0]);
        }
    }

}