<?php

/**
 * Classe pour les input de soumission en forme d'image
 * 
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class FormFieldInputImage extends FormField {

    protected $src; //chemin de l'image, si type=image
    protected $alt; //texte alternatif, si type=image

    public function __construct($src, $name='submit', $label=null, $alt=null, $class='inputimageclass', $id=null) {
        parent::__construct('image', $name, null, $id, $class, $label);

        $this->setSrc($src);
        $this->setAlt($alt);
    }

    public function __toString() {
        $str = '<input';
        if (!is_null($this->getType()))
            $str .= ' type="' . $this->getType() . '"';
        if (!is_null($this->getSrc()))
            $str .= ' src="' . $this->getSrc() . '"';
        if (!is_null($this->getAlt()))
            $str .= ' alt="' . $this->getAlt() . '"';
        if (!is_null($this->getName()))
            $str .= ' name="' . $this->getName() . '"';
        if (!is_null($this->getValue()))
            $str .= ' value="' . $this->getValue() . '"';
        if (!is_null($this->getId()))
            $str .= ' id="' . $this->getId() . '"';
        if (!is_null($this->getClass()))
            $str .= ' type="' . $this->getClass() . '"';
        $str .= ' />';

        if (!is_null($this->getLabel())) {
            $label = '<spans class="fieldlabel">' . $this->getLabel() . '</span>';
        } else {
            $label = '<spans class="fieldlabel">' . $this->getName() . '</span>';
        }

        return $label . $str;
    }

    public function toArrayRow($withLabel = true) {
        $str = '<tr' . ($this->isHidden() ? ' style="display: none;"' : '') . '><td>';

        if ($withLabel) {
            if (!is_null($this->getLabel())) {
                $label = '<spans class="fieldlabel">' . $this->getLabel() . '</span>';
            } else {
                $label = '<spans class="fieldlabel">' . $this->getName() . '</span>';
            }

            $str .= $label;
        }
        $str .= '</td><td>';

        $str .= '<input';
        if (!is_null($this->getType()))
            $str .= ' type="' . $this->getType() . '"';
        if (!is_null($this->getSrc()))
            $str .= ' src="' . $this->getSrc() . '"';
        if (!is_null($this->getAlt()))
            $str .= ' alt="' . $this->getAlt() . '"';
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