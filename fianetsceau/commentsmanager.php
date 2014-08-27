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

include_once 'lib/includes/includes.inc.php';

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

include_once 'fianetsceau.php';

if (_PS_VERSION_ < '1.5')
	$sceau = new Sceau();
else
	$sceau = new Sceau(Tools::getValue('id_shop'));

if (Tools::getValue('token') == Tools::getAdminToken($sceau->getSiteid().$sceau->getAuthkey().$sceau->getLogin()))
{
	if (Tools::getValue('traitement') == 'pagination')
	{
		$module = new FianetSceau();
		$comments = $module->getFianetProductComments(Tools::getValue('product_id'), Tools::getValue('limit_min'), Tools::getValue('limit_max'));
		$size_array = count($comments);
		$html = '';
		$i = 1;
		foreach ($comments as $value)
		{
			$html .= '<div class=\'fianetsceau_content\'>
			<div class=\'fianetsceau_left_content\'>'
				.$value['view_note'].'<br />par <span class=\'fianetsceau_capitalize\'>'.$value['firstname'].'</span> 
					<span class=\'fianetsceau_capitalize\'>'.$value['name'].'</span><br/>le '.$value['date'].'
			</div>
			<div class=\'fianetsceau_right_content\'>'.$value['comment'].'</div></div>';
			if ($i != $size_array)
				$html .= '<hr class=\'fianetsceau_hr\' />';
			$i++;
		}
		echo $html;
	}
	else
	{
		$module = new FianetSceau();

		$site_id = Tools::getValue('SiteID');
		$product_id = Tools::getValue('RefProduit');
		$xml = str_replace('&', 'et', Tools::getValue('XMLAvis'));
		$state = Tools::getValue('State');
		$hashcontrol = Tools::getValue('HashControl');
		$hash = md5($sceau->getSiteid().'+'.$sceau->getAuthkey().'-'.$product_id);

		if ($hashcontrol == $hash)
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Données recues : 
				siteID = '.$site_id.' , 
					product_id = '.$product_id.' , 
						xml = '.$xml.' , 
							state = '.$state.' , 
								hashcontrol = '.$hashcontrol);

			$resxml = new SceauSendratingCommentsResponse($xml);
			$listavis = $resxml->getResults();

			foreach ($listavis as $avis)
			{
				if ($state == 1)
					if (!$module->checkFianetComments($avis->getIdComment(), FianetSceau::SCEAU_COMMENTS_TABLE_NAME))
						$module->insertDBElement(array('id_comment' => (int)$avis->getIdComment(),
						'id_product' => (int)$avis->getIdProductComment(),
						'comment' => $avis->getComment(),
						'note' => $avis->getNoteComment(),
						'firstname' => $avis->getFirstnameComment(),
						'name' => $avis->getNameComment(),
						'state' => $state,
						'date' => $avis->getDateComment()), _DB_PREFIX_.FianetSceau::SCEAU_COMMENTS_TABLE_NAME);

					if (!$module->checkFianetComments((int)$product_id, FianetSceau::SCEAU_PRODUCT_COMMENTS_TABLE_NAME))
						$module->insertDBElement(array('id_product' => (int)$product_id,
						'global_note' => $avis->getGeneralNoteComment(),
						'nb_comments' => $avis->getNbComment()), _DB_PREFIX_.FianetSceau::SCEAU_PRODUCT_COMMENTS_TABLE_NAME);
					else
						$module->updateFianetComments($product_id, array('global_note' => $avis->getGeneralNoteComment(),
						'nb_comments' => $avis->getNbComment()), FianetSceau::SCEAU_PRODUCT_COMMENTS_TABLE_NAME);

				if ($state == 3)
					$module->deleteFianetComments((int)$avis->getIdComment());
				if ($state == 2)
					$module->updateFianetComments((int)$avis->getIdComment(), array('comment' => $avis->getComment(),
				'state' => (int)$state,
				'note' => $avis->getNoteComment(),
				'firstname' => $avis->getFirstnameComment()), FianetSceau::SCEAU_COMMENTS_TABLE_NAME);
			}
		}
		else
		{
			SceauLogger::insertLogSceau(__METHOD__.' : '.__LINE__, 'Hashcontrol non conforme : attendu :'.$hash.' , recu :'.$hashcontrol);
			header('Location: ../');
		}
	}
}
else
	header('Location: ../');