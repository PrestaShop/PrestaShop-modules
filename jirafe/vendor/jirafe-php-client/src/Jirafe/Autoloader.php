<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API client autoloader.
 *
 * @author knplabs.com
 */
class Jirafe_Autoloader
{
    /**
     * Registers Jirafe_Autoloader as an SPL autoloader.
     */
    static public function register()
    {
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        spl_autoload_register(array(new self, 'autoload'));
    }

    /**
     * Handles autoloading of classes.
     *
     * @param   string  $class  class name
     *
     * @return  Boolean         true if the class has been loaded
     */
    static public function autoload($class)
    {
        if (0 !== strpos($class, 'Jirafe')) {
            return;
        }

        if (file_exists($file = dirname(__FILE__) . '/../' . str_replace('_', '/', $class) . '.php')) {
            require $file;
        }
    }
}

