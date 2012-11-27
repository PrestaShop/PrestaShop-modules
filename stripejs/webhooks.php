<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 7040 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/stripejs.php');

if (!defined('_PS_VERSION_'))
	exit;

/* Check that the module is active and that we have the token */
$stripe = new StripeJs();
if ($stripe->active)
{
	if (isset($_GET['token']) && Configuration::get('STRIPE_WEBHOOK_TOKEN') == $_GET['token'])
	{
		include(dirname(__FILE__).'/lib/Stripe.php');
		Stripe::setApiKey(Configuration::get('STRIPE_MODE') ? Configuration::get('STRIPE_PRIVATE_KEY_LIVE') : Configuration::get('STRIPE_PRIVATE_KEY_TEST'));

		$event_json = json_decode(@file_get_contents('php://input'));
		if (isset($event_json->id))
		{
			/* In case there is an issue with the event, Stripe throw an exception, just ignore it. */
			try
			{
				/* To double-check and for more security, we retrieve the original event directly from Stripe */
				$event = Stripe_Event::retrieve($event_json->id);

				/* We are only handling chargebacks, other events are ignored */
				if ($event->type == 'charge.disputed')
				{
					$id_order = (int)Db::getInstance()->getValue('SELECT id_order FROM '._DB_PREFIX_.'stripe_transaction WHERE id_stripe_transaction = \''.pSQL($event->id).'\' AND `charge_back` = 0');
					if ($id_order)
					{
						$order = new Order((int)$id_order);
						if (Validate::isLoadedObject($order))
						{
							if (Configuration::get('STRIPE_CHARGEBACKS_ORDER_STATUS') != -1)
								if ($order->getCurrentState() != Configuration::get('STRIPE_CHARGEBACKS_ORDER_STATUS'))
								{
									$order->changeIdOrderState((int)Configuration::get('STRIPE_CHARGEBACKS_ORDER_STATUS'), (int)$id_order);
									Db::getInstance()->getValue('UPDATE `'._DB_PREFIX_.'stipe_transaction` SET `charge_back` = 1 WHERE `id_stripe_transaction` = \''.pSQL($event->id).'\' AND `charge_back` = 0');
								}

							$message = new Message();
							$message->message = $this->l('A chargeback occured on this order and was reported by Stripe on').' '.date('Y-m-d H:i:s');
							$message->id_order = (int)$order->id;
							$message->id_employee = 1;
							$message->private = 1;
							$message->date_add = date('Y-m-d H:i:s');
							$message->add();
						}
					}
				}
			}
			catch (Exception $e)
			{
				header('HTTP/1.1 501 NOT SUPPORTED');
				exit;
			}
			header('HTTP/1.1 200 OK');
			exit;
		}
	}
}
header('HTTP/1.1 501 NOT SUPPORTED');
exit;
