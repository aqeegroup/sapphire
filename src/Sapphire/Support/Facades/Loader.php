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

use Sapphire\Loader as _Loader;

/**
 * Loader Facade
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */

/**
 * Allows the class to know which magic methods are callable.
 *
 * @method static void register()
 * @method static void addPath(string $path)
 * @method static void import(string $file)
 * @method static void setCacheEnabled(boolean $isEnabled)
 * @method static void setCacheTTL(integer $ttl)
 * @method static void setCacheTimeKey(string $key)
 * @method static void setCacheFilePath(string $path)
 */
class Loader extends Facade
{
    public static function getFacadeAccessor()
    {
        return new _Loader;
    }
}
