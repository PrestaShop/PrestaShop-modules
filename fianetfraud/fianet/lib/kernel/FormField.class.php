<?php

/**
 * Classe pour les formulaires, représente un champ html
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class FormField extends Mother {

    protected $label; //label du champ
    protected $type; //type du champ
    protected $name; //nom du champ
    protected $value; //valeur du champ
    protected $id; //attribut html id du champ
    protected $class; //attribut html class du champ

    public function __construct($type='text', $name='', $value='', $id=null, $class='standardfieldclass', $label=null) {
        $this->setType($type);
        $this->setName($name);
        $this->setValue($value);
        $this->setId($id);
        $this->setClass($class);
    }
    
    /**
     * retour true si le champ courant est de type hidden, faux sinon
     *
     * @return boolean
     */
    public function isHidden(){
        return $this->getType() == 'hidden';
    }

    /**
     * retourne le champ sous forme de chaine html, avec le label si existant
     *
     * @return string
     */
    public function __toString() {
        $str = '<input';
        if (!is_null($this->getType()))
            $str .= ' type="' . $this->getType() . '"';
        if (!is_null($this->getName()))
            $str .= ' name="' . $this->getName() . '"';
        if (!is_null($this->getValue()))
            $str .= ' value="' . $this->getValue() . '"';
        if (!is_null($this->getId()))
            $str .= ' id="' . $this->getId() . '"';
        if (!is_null($this->getClass()))
            $str .= ' class="' . $this->getClass() . '"';
        $str .= ' />';

        if (!is_null($this->getLabel())) {
            $label = '<span class="fieldlabel">' . $this->getLabel() . '</span>';
        } else {
            $label = '<span class="fieldlabel">' . $this->getName() . '</span>';
        }

        return $label . $str;
    }

    /**
     * retourne le champ sous forme d'une ligne de tableau html, avec label si précisé
     *
     * @param boolean $withLabel présence de label ou non
     * @return string
     */
    public function toArrayRow($withLabel = true) {
        $str = '<tr' . ($this->isHidden() ? ' style="display: none;"' : '') . '><td>';

        if ($withLabel) {
            if (!is_null($this->getLabel())) {
                $label = '<span class="fieldlabel">' . $this->getLabel() . '</span>';
            } else {
                $label = '<span class="fieldlabel">' . $this->getName() . '</span>';
            }

            $str .= $label;
        }
        $str .=  '</td><td>';

        $str .= '<input';
        if (!is_null($this->getType()))
            $str .= ' type="' . $this->getType() . '"';
        if (!is_null($this->getName()))
            $str .= ' name="' . $this->getName() . '"';
        if (!is_null($this->getValue()))
            $str .= ' value="' . $this->getValue() . '"';
        if (!is_null($this->getId()))
            $str .= ' id="' . $this->getId() . '"';
        if (!is_null($this->getClass()))
            $str .= ' type="' . $this->getClass() . '"';
        $str .= ' />';
        $str .= '</td></tr>';
        
        return $str;
    }

}