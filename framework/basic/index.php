<?php
/**
 * Sapphire
 *
 * Licensed under the Massachusetts Institute of Technology
 *
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Lorne Wang < post@lorne.wang >
 * @copyright   Copyright (c) 2014 - 2015 , All rights reserved.
 * @link        http://lorne.wang/projects/sapphire
 * @license     http://lorne.wang/licenses/MIT
 */

/**
 *---------------------------------------------------------------
 * APPLICATION FOLDER PATH
 *---------------------------------------------------------------
 *
 * If you want this application to use a different "project"
 * folder then the default one you can set its name here. The
 * folder can also be renamed or relocated anywhere on your
 * server. If you do, use a full server path.
 */
define('APP_FOLDER', 'app');

/**
 *---------------------------------------------------------------
 * AUTOLOAD FILE PATH
 *---------------------------------------------------------------
 * Path to `sapphire/src`
 */
define('AUTOLOAD_PATH', __DIR__ . '/vendor/autoload.php');

/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     testing
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 */
define('ENVIRONMENT', @trim(@file_get_contents(__DIR__ . DIRECTORY_SEPARATOR .  'env')) ?: 'development');

/*
 *---------------------------------------------------------------
 * ERROR REPORTING 
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of
 * error reporting.
 * By default development will show errors but testing and
 * live will hide them.
 */
switch (ENVIRONMENT)
{
    case 'production':
        ini_set('display_errors', 0);
        if (version_compare(PHP_VERSION, '5.4', '>='))
        {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        }
        else
        {
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
        }
        break;

    default:
        error_reporting(-1);
        ini_set('display_errors', 1);
}

/**
 * ---------------------------------------------------------------
 *  NOW THAT WE KNOW THE PATH, SET THE MAIN PATH CONSTANTS
 * ---------------------------------------------------------------
 */
// Set the index directory correctly for CLI requests
defined('STDIN') AND chdir(__DIR__);

// Path to this file
define('ENTRY_PATH', __DIR__ . DIRECTORY_SEPARATOR);

// Path to the application folder
define('APP_PATH', (realpath(APP_FOLDER) ?: APP_FOLDER) . DIRECTORY_SEPARATOR);

/**
 * ---------------------------------------------------------------
 *  LOAD THE BOOTSTRAP FILE
 * ---------------------------------------------------------------
 *
 * And away we go...
 */
if (file_exists(AUTOLOAD_PATH))
{
    require AUTOLOAD_PATH;
}

/**
 * ---------------------------------------------------------------
 *  USE NAMESPACES
 * ---------------------------------------------------------------
 */
use Sapphire\Support\Facades\Loader;
use Sapphire\Support\Facades\Route;
use Sapphire\Support\Facades\Config;

/**
 * ---------------------------------------------------------------
 *  AUTO LOADER REGISTER
 * ---------------------------------------------------------------
 */
Loader::register();

/**
 * ---------------------------------------------------------------
 *  IMPORT PROJECT PREDEFINED LOADER
 * ---------------------------------------------------------------
 *
 * Try to load the loader class in project directory, in order to
 * anywhere to realize automatic loading from config file.
 */
Loader::import(APP_PATH . 'loader.php');

/**
 * ---------------------------------------------------------------
 *  SET CONFIG CLASS CONFIGURATION
 * ---------------------------------------------------------------
 */
Config::setDirectory(APP_PATH . 'config');
Config::setSubDirectory(ENVIRONMENT);

/**
 * ---------------------------------------------------------------
 *  SET THE DEFAULT DATE TIMEZONE
 * ---------------------------------------------------------------
 *
 * Sets the default timezone used by all date/time functions
 * in a script.
 *
 * @see http://php.net/manual/en/timezones.php
 */
ini_set('date.timezone', Config::get('primary.timezone', 'UTC'));

/**
 * ---------------------------------------------------------------
 *  SET THE DEFAULT CHARSET
 * ---------------------------------------------------------------
 *
 * PHP always outputs a character encoding by default in
 * the Content-type: header. To disable sending of the
 * charset, simply set it to be empty.
 *
 * @see http://php.net/htmlspecialchars
 */
ini_set('default_charset', Config::get('primary.charset', 'UTF-8'));

/**
 * ---------------------------------------------------------------
 *  IMPORT PROJECT PREDEFINED ROUTES
 * ---------------------------------------------------------------
 *
 * Try to load the routes config file in project directory, this
 * will be an important part of the program starts.
 */
Loader::import(APP_PATH . 'routes.php');

/**
 * ---------------------------------------------------------------
 *  RUNNING ROUTING
 * ---------------------------------------------------------------
 *
 * Run the router, parsing uri dispatch to the specific
 * executing processes.
 */
Route::start();