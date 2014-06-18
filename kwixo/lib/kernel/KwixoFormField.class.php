<?php
/**
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Implements an HTML form field
 *
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 * 
 * @method void setType(string $type) defines the attribute type of the tag
 * @method void setName(string $name) defines the attribute name of the tag
 * @method void setValue(string $value) defines the attribute value of the tag
 * @method void setClass(string $class) defines the attribute class of the tag
 * @method void setId(string $id) defines the attribute id of the tag
 */
class KwixoFormField extends KwixoMother
{

	protected $label; /*field label*/
	protected $type; /*type attribute*/
	protected $name; /*name attribute*/
	protected $value; /*field value*/
	protected $id; /*id attribute*/
	protected $class; /*class attribute*/

	public function __construct($type = 'text', $name = '', $value = '', $id = null, $class = 'standardfieldclass')
	{
		$this->setType($type);
		$this->setName($name);
		$this->setValue($value);
		$this->setId($id);
		$this->setClass($class);
	}

	/**
	 * returns true if the current object is hidden, false otherwise
	 *
	 * @return boolean
	 */
	public function isHidden()
	{
		return $this->getType() == 'hidden';
	}

	/**
	 * returns the field as an HTML string
	 *
	 * @return string
	 */
	public function __toString()
	{
		$str = '<input';
		if (!is_null($this->getType()))
			$str .= ' type="'.$this->getType().'"';
		if (!is_null($this->getName()))
			$str .= ' name="'.$this->getName().'"';
		if (!is_null($this->getValue()))
			$str .= ' value="'.$this->getValue().'"';
		if (!is_null($this->getId()))
			$str .= ' id="'.$this->getId().'"';
		if (!is_null($this->getClass()))
			$str .= ' class="'.$this->getClass().'"';
		$str .= ' />';
		/*if (!is_null($this->getLabel()))
		{
			$label = '<span class="fieldlabel">'.$this->getLabel().'</span>';
		} else
		{
			$label = '<span class="fieldlabel">'.$this->getName().'</span>';
		}
		$label = '';*/
		return $str;
	}

	/**
	 * returns the field in an HTML <table>
	 *
	 * @param boolean $withLabel include the label or not
	 * @return string
	 */
	public function toArrayRow($with_label = true)
	{
		$str = '<tr'.($this->isHidden() ? ' style="display: none;"' : '').'><td>';

		if ($with_label)
		{
			if (!is_null($this->getLabel()))
				$label = '<span class="fieldlabel">'.$this->getLabel().'</span>';
			else
				$label = '<span class="fieldlabel">'.$this->getName().'</span>';
			$str .= $label;
		}
		$str .= '</td><td>';

		$str .= '<input';
		if (!is_null($this->getType()))
			$str .= ' type="'.$this->getType().'"';
		if (!is_null($this->getName()))
			$str .= ' name="'.$this->getName().'"';
		if (!is_null($this->getValue()))
			$str .= ' value="'.$this->getValue().'"';
		if (!is_null($this->getId()))
			$str .= ' id="'.$this->getId().'"';
		if (!is_null($this->getClass()))
			$str .= ' type="'.$this->getClass().'"';
		$str .= ' />';
		$str .= '</td></tr>';

		return $str;
	}

}