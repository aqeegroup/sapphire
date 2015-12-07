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

use Sapphire\Route as _Route;

/**
 * Route Facade
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */

/**
 * Allows the class to know which magic methods are callable.
 *
 * @method static void get($uri, $mixed)
 * @method static void post($uri, $mixed)
 * @method static void put($uri, $mixed)
 * @method static void delete($uri, $mixed)
 * @method static void all($uri, $mixed)
 * @method static void auto($directory, $prefix)
 * @method static void missing($mixed)
 * @method static void start()
 */
class Route extends Facade
{
    public static function getFacadeAccessor()
    {
        return new _Route;
    }
}
