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
 * Implements an image submit input
 * 
 * @author ESPIAU Nicolas <nicolas.espiau at fia-net.com>
 * 
 * @method void setSrc(string $src) defines the attribute src of the tag
 * @method void setAlt(string $alt) defines the attribute alt of the tag
 */
class KwixoFormFieldInputImage extends KwixoFormField
{

	protected $src;
	protected $alt;

	public function __construct($src, $name = 'submit', $label = null, $alt = null, $class = 'inputimageclass', $id = null)
	{
		parent::__construct('image', $name, null, $id, $class, $label);

		$this->setSrc($src);
		$this->setAlt($alt);
	}

	public function __toString()
	{
		$str = '<input';
		if (!is_null($this->getType()))
			$str .= ' type="'.$this->getType().'"';
		if (!is_null($this->getSrc()))
			$str .= ' src="'.$this->getSrc().'"';
		if (!is_null($this->getAlt()))
			$str .= ' alt="'.$this->getAlt().'"';
		if (!is_null($this->getName()))
			$str .= ' name="'.$this->getName().'"';
		if (!is_null($this->getValue()))
			$str .= ' value="'.$this->getValue().'"';
		if (!is_null($this->getId()))
			$str .= ' id="'.$this->getId().'"';
		if (!is_null($this->getClass()))
			$str .= ' type="'.$this->getClass().'"';
		$str .= ' />';

		if (!is_null($this->getLabel()))
			$label = '<spans class="fieldlabel">'.$this->getLabel().'</span>';
		else
			$label = '<spans class="fieldlabel">'.$this->getName().'</span>';

		return $label.$str;
	}

	public function toArrayRow($with_label = true)
	{
		$str = '<tr'.($this->isHidden() ? ' style="display: none;"' : '').'><td>';

		if ($with_label)
		{
			if (!is_null($this->getLabel()))
				$label = '<spans class="fieldlabel">'.$this->getLabel().'</span>';
			else
				$label = '<spans class="fieldlabel">'.$this->getName().'</span>';

			$str .= $label;
		}
		$str .= '</td><td>';

		$str .= '<input';
		if (!is_null($this->getType()))
			$str .= ' type="'.$this->getType().'"';
		if (!is_null($this->getSrc()))
			$str .= ' src="'.$this->getSrc().'"';
		if (!is_null($this->getAlt()))
			$str .= ' alt="'.$this->getAlt().'"';
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