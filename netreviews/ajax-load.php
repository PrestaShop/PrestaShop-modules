<?php
/**
 * ajax-load.php file used to use ajax load for reviews list using pagination
 *
 * @author    NetReviews (www.avis-verifies.com) - Contact: contact@avis-verifies.com
 * @category  ajax
 * @copyright NetReviews
 * @license   NetReviews 
 * @date 09/04/2014
 */

require(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/models/NetReviewsModel.php');

/*
# Ajax file to pagination enfine
# This file contains the same code as hook productTabContent but use a template dedicated to the ajax data loaded
*/

$id_product = Tools::getValue('id_product');

if (empty($id_product))
	exit;

$o_av = new NetReviewsModel();

$nb_comments = (int)Tools::getValue('count_reviews');

$p = abs((int)Tools::getValue('p', 1));

$range = 2;

if ($p > (($nb_comments / $o_av->reviews_by_page) + 1))
	Tools::redirect(preg_replace('/[&?]p=\d+/', '', $_SERVER['REQUEST_URI']));
$pages_nb = ceil($nb_comments / (int)$o_av->reviews_by_page);
$start = (int)$p - $range;
if ($start < 1)
	$start = 1;
$stop = (int)$p + $range;
if ($stop > $pages_nb)
	$stop = (int)$pages_nb;


/* $first_review = ($p - 1) * $reviews_by_page;  */

$reviews = $o_av->getProductReviews((int)$id_product, false, $p);

$reviews_list = array();

foreach ($reviews as $k => $review)
{
	/*Reaffect variables to template engine*/
	$my_review = array();
	$my_review['ref_produit'] = $review['ref_product'];
	$my_review['id_product_av'] = $review['id_product_av'];
	$my_review['rate'] = $review['rate'];
	$my_review['avis'] = urldecode($review['review']);
	$my_review['horodate'] = date('d/m/Y', $review['horodate']);
	$my_review['customer_name'] = urldecode($review['customer_name']);
	$my_review['discussion'] = '';

	$unserialized_discussion = json_decode(NetReviewsModel::AcDecodeBase64($review['discussion']),true);

	if ($unserialized_discussion)
	{

		foreach ($unserialized_discussion as $k_discussion => $each_discussion)
		{
			$my_review['discussion'][$k_discussion]['commentaire'] = $each_discussion['commentaire'];
			$my_review['discussion'][$k_discussion]['horodate'] = date('d/m/Y', time($each_discussion['horodate']));

			if ($each_discussion['origine'] == 'ecommercant')
				$my_review['discussion'][$k_discussion]['origine'] = Configuration::get('PS_SHOP_NAME');
			elseif ($each_discussion['origine'] == 'internaute')
				$my_review['discussion'][$k_discussion]['origine'] = $my_review['customer_name'];
			else
				$my_review['discussion'][$k_discussion]['origine'] = $this->l('Moderator');
		}
	}

	$reviews_list[] = $my_review;

}

$smarty->assign(array(
	'current_url' =>  $_SERVER['REQUEST_URI'],
	'reviews' => $reviews_list,
	'p' => (int)$p,
	'n' => $o_av->reviews_by_page,
	'pages_nb' => $pages_nb,
	'start' => $start,
	'stop' => $stop,
));

echo $smarty->fetch(dirname(__FILE__).'/views/templates/hook/ajax-load-tab-content.tpl');

?>