<?php
/**
 * AVOLUTIONS
 * 
 * Just another open source PHP framework.
 * 
 * @copyright	Copyright (c) 2019 - 2020 AVOLUTIONS
 * @license		MIT License (http://avolutions.org/license)
 * @link		http://avolutions.org
 */

namespace Avolutions\Core;

use Avolutions\Config\Config;

/**
 * Autoloader class
 * 
 * Autoloads all required classes
 * 
 * @author	Alexander Vogt <alexander.vogt@avolutions.org>
 * @since	0.1.0
 */
class Autoloader
{	
	/**
	 * register
	 * 
	 * This method finds the absolute pathes for all required classes and 
	 * includes them. Has to be called before the usage of any class in the
	 * framework. 
	 */
    public static function register()
    {
		spl_autoload_register(function ($class) {	
            // replace 'Avolutions' (namespace) with 'src' (directory) to get correct path
            $class = str_replace('Avolutions', SRC, $class); 

            if (defined('APPLICATION_NAMESPACE')) {
                // replace application namespace with 'application' (directory) to get correct path
                $class = str_replace(APPLICATION_NAMESPACE, APPLICATION, $class); 
            }

            // replace backslash with correct directory separator to get it work fine on all OS
            $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
			
            $file = BASE_PATH.$class.'.php';

            if (file_exists($file)) { 
                require_once $file;
            }
		});
	}
}