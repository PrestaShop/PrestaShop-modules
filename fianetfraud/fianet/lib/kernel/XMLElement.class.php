<?php

/**
 * Classe XMLElement complétant la classe native SimpleXMLElement
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 */
class XMLElement extends Mother {

    protected $encoding = "UTF-8";
    protected $name = "";
    protected $value = "";
    protected $attributes = array();
    protected $children = array();

    public function __construct($data=null) {
        //on définit l'encodage par défaut au niveau de la conf PHP, pour éviter toute erreur d'encodage
        ini_set('default_charset', $this->getEncoding());

        if (is_null($data)) {
            $name = normalizeName(get_class($this));
            $this->setName($name);
        }

        //si $data est une chaine de caractères valide
        if (is_string($data)) {
            //on supprime les espaces en début de chaine
            $data = preg_replace('#^[ \r\n]*#', '', $data);
            //$data = preg_replace('#^[ \r\n' . chr(13) . chr(10) . ']*#', '', $data);
            //on vérifie si la chaine est une chaine valide, si non, on jette un erreur
            if (!isXMLstring($data)) {
                $msg = "La chaine \"$data\" n'est pas valide";
                insertLog(get_class($this) . ' - __construct()', $msg);
                throw new Exception($msg);
            }
            
            //on vérifie l'encodage et on le converti en UTF-8
            preg_match('#^<\?xml.+encoding= ?[\'|\"]([^\"\']+).+$#s', $data, $out);
            if (isset($out[1]) && $out[1] != "") {
                $actualencoding = $out[1];
                $wantedencoding = $this->getEncoding();
                //converti la chaine entrée en utf-8, quel que soit son encodage d'origine
                $data = mb_convert_encoding($data, $wantedencoding, $actualencoding);
            } else {
                $data = mb_convert_encoding($data, $this->getEncoding());
            }
            //on la convertit en SimpleXMLElement
            $data = new SimpleXMLElement($data);
        }

        //si $data est un SimpleXMLElement object
        if (is_object($data) && get_class($data) == 'SimpleXMLElement') {
            $string = (string) $data;
            //on vérifie l'encodage et on le converti en UTF-8
            preg_match('#^<\?xml.+encoding= ?[\'|\"]([^\"\']+).+$#s', $string, $out);
            if (isset($out[1]) && $out[1] != "") {
                $actualencoding = $out[1];
                $wantedencoding = $this->getEncoding();
                //converti la chaine entrée en utf-8, quel que soit son encodage d'origine
                $string = mb_convert_encoding($string, $wantedencoding, $actualencoding);
            } else {
                $string = mb_convert_encoding($string, $this->getEncoding());
            }
            //on récupère le nom
            $this->name = $data->getName();
            //on récupère la valeur
            $this->value = $string;
            //on récupère les attributs
            foreach ($data->attributes() as $attname => $attvalue) {
                $this->attributes[$attname] = $attvalue;
            }
            //on rattache les enfants
            foreach ($data->children() as $simplexmlelementchild) {
                $child = new XMLElement($simplexmlelementchild);
                $this->addChild($child);
            }
        }
    }

    /**
     * ajoute un attribut à l'objet courant
     * @param string $name nom de l'attribut
     * @param string $value valeur de l'attribut
     */
    public function addAttribute($name, $value) {
        $this->attributes[$name] = $value;
    }

    /**
     * retourne la valeur de l'attribut $name
     *
     * @param string $name nom de l'attribut
     * @return string
     */
    public function getAttribute($name) {
        return (isset($this->attributes[$name]) ? $this->attributes[$name] : null);
    }

    /**
     * retourne un tableau contenant tous les enfants et sous enfants dont le nom est $name
     *
     * @param string $name
     * @return array
     */
    public function getChildrenByName($name) {
        //ouverture du tableau
        $children = array();

        //pour tous les enfants
        foreach ($this->getChildren() as $child) {
            //si le nom correspond on l'ajoute au tableau
            if ($child->getName() == $name)
                array_push($children, $child);

            //on cherche dans les sous enfants
            $children = array_merge($children, $child->getChildrenByName($name));
        }

        return $children;
    }

    /**
     * retourne un tableau contenant tous les enfants et sous enfants dont le nom est $name, où l'attribut $attrbutename existe et vaut $attributevalue si non null
     *
     * @param <type> $name
     * @param <type> $attributename
     * @param <type> $attributevalue
     * @return <type> 
     */
    public function getChildrenByNameAndAttribute($name, $attributename, $attributevalue=null) {
        //on commence par filtrer les enfants par nom
        $children = $this->getChildrenByName($name);

        //pour chaque enfant pré-sélectionné
        foreach ($children as $key => $child) {
            //l'attribut recherché est absent ou si sa valeur ne correspond pas
            if (is_null($child->getAttribute($attributename)) || (!is_null($attributevalue) && $child->getAttribute($attributename) != $attributevalue))
            //on retire l'enfant du tableau
                unset($children[$key]);
        }

        return $children;
    }

    /**
     * ajoute un enfant à la fin de l'objet courant et retourne l'objet XML de l'enfant
     * @param mixed $input l'enfant (XMLElement, chaine ou SimpleXMLElement
     * @param string $value value of the child
     * @param array $attributes attributes of the child
     * @return XMLElement 
     */
    public function addChild($input, $value=null, $attributes=array()) {
        //normalisation de l'enfant, permettra d'ajouter tous les sous-enfants
        $input = $this->createChild($input, $value, $attributes);

        //ajout de l'enfant au tableau
        $this->children[] = $input;

        return $input;
    }

    /**
     * ajoute un enfant à la fin de l'objet courant et retourne l'objet XML de l'enfant
     * @param mixed $input l'enfant (XMLElement, chaine ou SimpleXMLElement
     * @param string $value value of the child
     * @param array $attributes attributes of the child
     * @return XMLElement 
     */
    public function stackChild($input, $value=null, $attributes=array()) {
        //normalisation de l'enfant, permettra d'ajouter tous les sous-enfants
        $input = $this->createChild($input, $value, $attributes);

        //ajout de l'enfant en haut du tableau
        array_unshift($this->children, $input);

        return $input;
    }

    /**
     * normalise $input en XMLElement avec sous enfants
     * cas d'appels :
     * createChild(XMLElement) --> ne fera rien
     * createChild(simpleXMLElement)
     * createChild("<element a='1' b='2'>valeur</element>")
     * createChild("element","valeur", array('a'=>1, 'b'=>2))
     * 
     * @param mixed $input objet à normaliser
     * @param string $value valeur de l'objet (si $input est une chaine)
     * @param string $attributes
     * @return XMLElement objet normalisé
     */
    private function createChild($input, $value=null, $attributes=array()) {
        //si l'entrée est une chaine non xml, on la construit à partir des autres paramètres
        if (is_string($input) && !isXMLstring($input)) {
            $str = "<$input";
            foreach ($attributes as $name => $val) {
                $str .= " $name='$val'";
            }
            $str .= '>';

            if (!is_null($value))
                $str .= $value;

            $str .= "</$input>";
            $input = new SimpleXMLElement($str);
        }

        //si l'entrée est une chaine XML ou un objet simpleXMLElement
        if (is_string($input) || isSimpleXMLElement($input)) {
            //conversion en XMLElement
            $input = new XMLElement($input);
        }

        //si à ce stade $input n'est pas un XMLElement, il n'est pas pris en compte
        if (!isXMLElement($input)) {
            $msg = "Le paramètre entré n'est pas pris en compte par la classe XMLElement";
            insertLog(get_class($this) . " - createChild()", $msg);
            throw new Exception($msg);
        }

        return $input;
    }

    /**
     * retourne vrai si la valeur est vide et s'il n'y a aucun enfant, faux sinon
     *
     * @return bool 
     */
    public function isEmpty() {
        return ($this->getValue() == "" || is_null($this->getValue())) && ($this->countChildren() == 0);
    }

    /**
     * retourne le nombre d'enfants au premier degré de l'objet courant
     *
     * @return int 
     */
    public function countChildren() {
        return count($this->children);
    }

    /**
     * retourne l'objet SimpleXMLElement correspondant à l'objet courant
     * @param boolean $recursive autorise la descente dans les enfants et sous-enfants
     * @return SimpleXMLElement 
     */
    public function toSimpleXMLElement($recursive = false) {
        //on créé simplement l'objet SimpleXMLElement
        $simplexlmelementobject = new SimpleXMLElement('<XMLElement>' . $this->getValue() . '</XMLElement>');

        //on ajoute les attributs
        foreach ($this->getAttributes() as $name => $value) {
            $simplexlmelementobject->addAttribute($name, $value);
        }

        //si la récurisivité est autorisée on attache les enfants
        if ($recursive)
            $this->attachChildren($simplexlmelementobject);

        return $simplexlmelementobject;
    }

    /**
     * rattache toute la descendance à l'objet SimpleXMLElement en paramètre
     * 
     * @param SimpleXMLElement $simplexmlelement objet auquel rattacher toute la descendance
     */
    public function attachChildren($simplexmlelement) {
        //pour chaque enfant de l'objet courant
        foreach ($this->getChildren() as $child) {
            //on créé un objet SimpleXMLElement et on l'ajoute à l'objet en paramètre
            $simplexmlelement_child = $simplexmlelement->addChild($child->getName(), $child->getValue());

            //on ajoute les attributs
            foreach ($child->getAttributes() as $name => $value) {
                $simplexmlelement_child->addAttribute($name, $value);
            }

            //on rattache les enfants de l'enfant lu à l'objet SimpleXMLElement qui lui correspond
            $child->attachChildren($simplexmlelement_child);
        }
    }

    /**
     * retourne l'objet sous forme de chaine XML
     * @return type string la chaine XML
     */
    public function getXML() {
        //ajout de la déclaration d'encodage dans le flux
        $ret = preg_replace('#^.*(<\?xml.+)(\?>)#is', '$1 encoding="' . $this->getEncoding() . '"$2', $this->toSimpleXMLElement(true)->asXML());
        //sécurité encodage
        $ret = html_entity_decode($ret, ENT_NOQUOTES, $this->getEncoding());
        //drop des retours de chariot
        $ret = preg_replace('#[\r\n'.chr(10).chr(13).']#', '', $ret);
        //suppression des espaces entre les balises
        $ret = preg_replace('#>( )+<#', '><', $ret);

        return ($ret);
    }

    /**
     * retourne l'objet sous forme de chaine XML
     * @return string la chaine XML
     */
    public function __toString() {
        return $this->getXML();
    }

    /**
     * enregistre la chaine XML dans un fichier
     *
     * @param string $filename chemin du fichie
     * @return string 
     */
    public function saveInFile($filename) {
        return $this->toSimpleXMLElement(true)->asXML($filename);
    }

    /**
     *
     * @param string $name
     * @param array $params
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

        //si le préfixe est "child", c'est un ajout d'enfant dynamique
        if (preg_match('#^child(.+)$#', $name, $out)) {
            //on stocke le nom de l'élément à ajouter
            $elementname = strtolower($out[1]);

            //on stock le booleen indiquant la possibilité d'ajouter une balise vide
            $empty_allowed = (isset($params[2]) ? $params[2] : false);

            //si un paramètre est passé et que c'est un XMLElement on l'ajoute directement comme fils si le nom correspond à celui de la fonction
            if (isset($params[0]) && isXMLElement($params[0])) {
                //si le nom ne correspond pas on jette un erreur
                if ($params[0]->getName() != $elementname)
                    throw new Exception("Le nom de la balise ne correspond pas : $elementname attendu, " . $params[0]->getName() . " trouvé.");

                //si l'élément n'est pas vide ou si on autorise les éléments vides
                if (!$params[0]->isEmpty() || $empty_allowed)
                    return $this->addChild($params[0]);

                //si vide non autorisé, on sort
                return false;
            }

            //création de l'élément fils
            $child = new XMLElement("<$elementname></$elementname>");
            //si des attributs sont passés en paramètres on les ajouts
            if (isset($params[1])) {
                foreach ($params[1] as $att => $value) {
                    $child->addAttribute($att, $value);
                }
            }

            //si il n'y a aucun paramètre entré et qu'on autorise les balises vides
            if ((!isset($params[0]) || is_null($params[0]))) {
                if ($empty_allowed)
                    return $this->addChild($child);

                //si vide non autorisé on sort sans rien faire
                return false;
            }

            //si le paramètre est une chaine
            if (is_string($params[0]) or is_int($params[0])) {
                //si c'est une chaine XML on créé un sous-enfant et on l'affecte
                if (isXMLstring($params[0])) {
                    $granchild = $this->createChild($params[0]);
                    $child->addChild($granchild);
                } else {
                    //si c'est une chaine normale, on l'affecte comme valeur
                    $child->setValue($params[0]);
                }
            }

            //on ajoute l'enfant
            if (!$child->isEmpty() || $empty_allowed)
                return $this->addChild($child);

            return false;
        }
    }

}


if (!function_exists('mb_convert_encoding'))
{
	function mb_convert_encoding($string, $to, $from = '')
	{
        // Convert string to ISO_8859-1
        if ($from == "UTF-8")
                $iso_string = utf8_decode($string);
        else
                if ($from == "UTF7-IMAP")
                        $iso_string = imap_utf7_decode($string);
                else
                        $iso_string = $string;

        // Convert ISO_8859-1 string to result coding
        if ($to == "UTF-8")
                return(utf8_encode($iso_string));
        else
                if ($to == "UTF7-IMAP")
                        return(imap_utf7_encode($iso_string));
                else
                        return($iso_string); 
	}
}