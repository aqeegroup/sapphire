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

/**
 * The Facade abstract class should inherit from this interface,
 * a subclass need to implement "getFacadeAccessor" static method,
 * this method returns a value to a valid object instance.
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
interface FacadeInterface
{
    public static function getFacadeAccessor();
}

/**
 * Facade provide a "static" interface to classes that are
 * available in the application's service container.
 * This "facade" serve as "static proxies" to underlying classes
 * in the service container, providing the benefit of a terse,
 * expressive syntax while maintaining more testability and
 * flexibility than traditional static methods.
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
abstract class Facade implements FacadeInterface
{
    /**
     * Array of cached facade objects
     *
     * @var array
     */
    private static $instances = [];

    /**
     * Calling static magic methods
     *
     * @return mixed
     */
    final static function __callStatic($method, $parameters)
    {
        $className = get_called_class();

        if ( ! isset(self::$instances[$className]))
        {
            self::$instances[$className] = static::getFacadeAccessor();
        }

        return call_user_func_array([self::$instances[$className], $method], $parameters);
    }
}
