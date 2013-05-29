<?php
/*
 * 2007-2010 PrestaShop
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
 *  @author Prestashop SA <contact@prestashop.com>
 *  @author Quadra Informatique <modules@quadra-informatique.fr>
 *  @copyright  2007-2013 PrestaShop SA / 1997-2013 Quadra Informatique
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registred Trademark & Property of PrestaShop SA
 */

require_once('../../config/config.inc.php');
require_once(_PS_ROOT_DIR_ . '/init.php');
require_once(dirname(__FILE__) . '/classes/SCFields.php');

$so = new SCfields('API');

$fields = $so->getFields();

// Build back the fields list for SoColissimo, gift infos are send using the JS
$inputs = array();
foreach ($_GET as $key => $value)
    if (in_array($key, $fields))
        $inputs[$key] = Tools::getValue($key);

// if gift message has accentued chars for api 3.0 socolissimo
$gift_message = '';
if (Tools::getValue('gift_message'))
    $gift_message = preg_replace(
            array(
        /* Lowercase */
        '/[\x{0105}\x{00E0}\x{00E1}\x{00E2}\x{00E3}\x{00E4}\x{00E5}]/u',
        '/[\x{00E7}\x{010D}\x{0107}]/u',
        '/[\x{010F}]/u',
        '/[\x{00E8}\x{00E9}\x{00EA}\x{00EB}\x{011B}\x{0119}]/u',
        '/[\x{00EC}\x{00ED}\x{00EE}\x{00EF}]/u',
        '/[\x{0142}\x{013E}\x{013A}]/u',
        '/[\x{00F1}\x{0148}]/u',
        '/[\x{00F2}\x{00F3}\x{00F4}\x{00F5}\x{00F6}\x{00F8}]/u',
        '/[\x{0159}\x{0155}]/u',
        '/[\x{015B}\x{0161}]/u',
        '/[\x{00DF}]/u',
        '/[\x{0165}]/u',
        '/[\x{00F9}\x{00FA}\x{00FB}\x{00FC}\x{016F}]/u',
        '/[\x{00FD}\x{00FF}]/u',
        '/[\x{017C}\x{017A}\x{017E}]/u',
        '/[\x{00E6}]/u',
        '/[\x{0153}]/u',
        /* Uppercase */
        '/[\x{0104}\x{00C0}\x{00C1}\x{00C2}\x{00C3}\x{00C4}\x{00C5}]/u',
        '/[\x{00C7}\x{010C}\x{0106}]/u',
        '/[\x{010E}]/u',
        '/[\x{00C8}\x{00C9}\x{00CA}\x{00CB}\x{011A}\x{0118}]/u',
        '/[\x{0141}\x{013D}\x{0139}]/u',
        '/[\x{00D1}\x{0147}]/u',
        '/[\x{00D3}]/u',
        '/[\x{0158}\x{0154}]/u',
        '/[\x{015A}\x{0160}]/u',
        '/[\x{0164}]/u',
        '/[\x{00D9}\x{00DA}\x{00DB}\x{00DC}\x{016E}]/u',
        '/[\x{017B}\x{0179}\x{017D}]/u',
        '/[\x{00C6}]/u',
        '/[\x{0152}]/u',
            ), array(
        'a', 'c', 'd', 'e', 'i', 'l', 'n', 'o', 'r', 's', 'ss', 't', 'u', 'y', 'z', 'ae', 'oe',
        'A', 'C', 'D', 'E', 'L', 'N', 'O', 'R', 'S', 'T', 'U', 'Z', 'AE', 'OE'
            ), Tools::getValue('gift_message'));
$param_plus = array(
    // Get the data set before
    Tools::getValue('trParamPlus'),
    Tools::getValue('gift'),
    $gift_message
);

$inputs['trParamPlus'] = implode('|', $param_plus);
// Add signature to get the gift and gift message in the trParamPlus
$inputs['signature'] = $so->generateKey($inputs);

if (Tools::isSubmit('first_call'))
    $onload_script = 'document.getElementById(\'socoForm\').submit();';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
    <head>
        <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=iso-8859-1" />
    </head>
    <body onload="<?php echo $onload_script; ?>">
        <div style="width:320px;margin:0 auto;text-align:center;">
            <form id="socoForm" name="form" action="<?php echo Configuration::get('SOCOLISSIMO_URL_MOBILE'); ?>" method="POST">
                <?php
                foreach ($inputs as $key => $val)
                    echo '<input type="hidden" name="' . $key . '" value="' . $val . '"/>';
                ?>
                <img src="logo.gif" />
                <p>Vous allez être redirigé vers Socolissimo dans quelques instants, si ce n'est pas le cas veuillez cliquer sur le bouton.</p>
                <p><img src="ajax-loader.gif" /></p>
                <input type="submit" value="Envoyer" />

            </form>
        </div>
    </body>
</html>
