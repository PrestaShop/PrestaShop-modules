<?php

/**
 * Description of Form
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class Form extends Mother {
    const SUBMIT_STANDARD = 'standard';
    const SUBMIT_AUTO = 'auto';
    const SUBMIT_IMAGE = 'image';

    protected $action;
    protected $name;
    protected $method = 'POST';
    protected $id;
    protected $class;
    protected $autosubmit = false;
    protected $fields = array();

    /**
     * construction d'un formulaire HTML
     * @param string $action action du formulaire
     * @param string $name nom du formulaire
     * @param string $method methode d'envoi des données du formulaire
     * @param array $fields champs du formulaire
     */
    public function __construct($action=null, $name='fianet_form', $method='POST', array $fields=null, $class=null, $id=null) {
        if (is_null($id))
            $id = time();

        $this->setAction($action);
        $this->setName($name);
        $this->setMethod($method);
        $this->setClass($class);
        $this->setId($id);

        if (is_array($fields)) {
            foreach ($fields as $field) {
                $this->addField($field);
            }
        }
    }

    /**
     * ajoute un champs au formulaire courant.
     * 
     * @param mixed $field peut être de type FormField ou simple tableau
     */
    public function addField($field) {
        //si le param est déjà un FormField on l'ajoute simplement
        if (isFormField($field)) {
            $this->fields[] = $field;
        }

        //si le paramètre entré un est tableau
        if (is_array($field)) {
            //création d'un objet FormField vide
            $new_field = new FormField();
            //si l'une des clés du tableau est "label"
            if (array_key_exists('label', $field)) {
                //on ajoute le label à l'objet FormField
                $new_field->setLabel($field['label']);
                //on détruit la cellule "label" du tableau pour ne pas répéter l'entrée
                unset($field['label']);
            }
            //pour chaque cellule du tableau
            foreach ($field as $name => $value) {
                //on définit le nom de la fonction a appeler en fonction de la clé de la cellule
                $funcname = 'set' . $name;
                //on ajoute l'attribut en question au FormField
                $new_field->$funcname($value);
            }
            //on ajoute le nouveau champ au form
            $this->fields[] = $new_field;
        }
    }

    /**
     * ajoute un bouton submit
     *
     * @param string $name
     * @param string $value
     * @param string $id
     * @param string $class
     * @param string $label
     */
    public function addSubmit($name=null, $value=null, $id=null, $class=null, $label=null) {
        $this->addField(new FormField('submit', $name, $value, $id, $class, $label));
    }

    /**
     * ajout un bouton image pour la soumission
     *
     * @param <type> $src //chemin absolu de l'image
     * @param <type> $name
     * @param <type> $label
     * @param <type> $alt //texte alternatif
     * @param <type> $class
     * @param <type> $id
     */
    public function addImageSubmit($src, $name, $label, $alt, $class=null, $id=null) {
        $this->addField(new FormFieldInputImage($src, $name, $label, $alt, $class, $id));
    }

    public function __toString() {
        $str = '<form';
        if (!is_null($this->getId()))
            $str .= ' id="' . $this->getId() . '"';
        if (!is_null($this->getAction()))
            $str .= ' action="' . $this->getAction() . '"';
        if (!is_null($this->getName()))
            $str .= ' name="' . $this->getName() . '"';
        if (!is_null($this->getMethod()))
            $str .= ' method="' . $this->getMethod() . '"';
        $str .= '>';

        foreach ($this->getFields() as $field) {
            $str .= $field->__toString() . '<br />';
        }

        if ($this->getAutosubmit())
            $str .= '<script type="text/javascript">document.getElementById(' . $this->getId() . ').submit()</script>';

        $str .= '</form>';

        return $str;
    }

    public function toArray($withLabels = true) {
        $str = '<form';
        if (!is_null($this->getId()))
            $str .= ' id="' . $this->getId() . '"';
        if (!is_null($this->getAction()))
            $str .= ' action="' . $this->getAction() . '"';
        if (!is_null($this->getName()))
            $str .= ' name="' . $this->getName() . '"';
        if (!is_null($this->getMethod()))
            $str .= ' method="' . $this->getMethod() . '"';
        $str .= '>';

        $str .= '<table>';
        foreach ($this->getFields() as $field) {
            $str .= $field->toArrayRow(($field->getType() == 'submit' ? false : $withLabels));
        }
        $str .= '</table>';

        if ($this->getAutosubmit())
            $str .= '<script type="text/javascript" language="javascript">document.getElementById(' . $this->getId() . ').submit()</script>';

        $str .= '</form>';

        return $str;
    }

}