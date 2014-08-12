<?php
if (!defined('_PS_VERSION_'))
    exit;
function upgrade_module_2_0($object) {
  return Db::getInstance()->execute(
            'DROP TABLE IF EXISTS `'._DB_PREFIX_.'payu_token`;');
}

?>