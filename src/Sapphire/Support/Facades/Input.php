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
namespace Sapphire\Support\Facades;

use Sapphire\HTTP\Input as _Input;

/**
 * Input Facade
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */

/**
 * Allows the class to know which magic methods are callable.
 *
 * @method static string ipAddress()
 * @method static string userAgent()
 * @method static array requestHeaders()
 * @method static array getRequestHeader()
 * @method static boolean isAjaxRequest()
 * @method static boolean isCliRequest()
 * @method static string method()
 * @method static string xssClean($str)
 * @method static string get($index = NULL, $default = NULL, $xssClean = FALSE)
 * @method static string post($index = NULL, $default = NULL, $xssClean = FALSE)
 * @method static string server($index = NULL, $default = NULL, $xssClean = FALSE)
 * @method static string cookie($index = NULL, $default = NULL, $xssClean = FALSE)
 * @method static void setCookie($name = '', $value = '', $expire = '', $domain = '', $path = '/', $prefix = '', $secure = FALSE, $httpOnly = FALSE)
 */
class Input extends Facade
{
    public static function getFacadeAccessor()
    {
        return new _Input;
    }
}
