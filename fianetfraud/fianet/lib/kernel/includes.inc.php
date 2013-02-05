<?php

if (!class_exists('Mother'))
  require_once SAC_ROOT_DIR . '/lib/kernel/Mother.class.php';

if (!class_exists('XMLElement'))
  require_once SAC_ROOT_DIR . '/lib/kernel/XMLElement.class.php';

if (!class_exists('Spyc'))
  require_once SAC_ROOT_DIR . '/lib/kernel/spyc.php';

if (!class_exists('FianetSocket'))
  require_once SAC_ROOT_DIR . '/lib/kernel/FianetSocket.class.php';

if (!class_exists('SACService'))
  require_once SAC_ROOT_DIR . '/lib/kernel/Service.class.php';

if (!class_exists('HashMD5'))
  if (PHP_INT_SIZE == 4)
    require_once SAC_ROOT_DIR . '/lib/kernel/fianet_key_32bits.php';
  else
    require_once SAC_ROOT_DIR . '/lib/kernel/fianet_key_64bits.php';

if (!class_exists('Form'))
  require_once SAC_ROOT_DIR . '/lib/kernel/Form.class.php';

if (!class_exists('FormField'))
  require_once SAC_ROOT_DIR . '/lib/kernel/FormField.class.php';

if (!class_exists('FormFieldInputImage'))
  require_once SAC_ROOT_DIR . '/lib/kernel/FormFieldInputImage.class.php';