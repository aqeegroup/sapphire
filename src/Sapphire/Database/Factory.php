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
namespace Sapphire\Database;

/**
 * Database Instance Factory
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Factory
{
    /**
     * Create connection object
     * 
     * @access public
     * @param  string $key
     * @param  array  $options
     * @return Connection
     */
    public static function create($key = 'default', $options)
    {
        if ($connection = ConnectionManager::getConnection($key))
        {
            return $connection;
        }

        if (array_key_exists($key, $options) && is_array($options[$key]))
        {
            $options = $options[$key];
        }

        $class = __NAMESPACE__ . '\Driver\\' . ucwords($options['driver']);

        return ConnectionManager::addConnection($key, new $class($options));
    }
}
