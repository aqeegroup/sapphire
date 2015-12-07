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
 * Connection Manager Class
 *
 * Singleton to manage any and all database connections.
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class ConnectionManager
{
    private static $connections = [];

    /**
     * Add the connection to the connection manager
     *
     * @access public
     * @param  string     $key        Optional name of a connection
     * @param  Connection $connection A connection object
     * @return Connection
     */
    public static function addConnection($key, Connection $connection)
    {
        return self::$connections[$key] = $connection;
    }

    // --------------------------------------------------------------------

    /**
     * Get the connection from the connection manager
     *
     * @access public
     * @param  string $key Optional name of a connection
     * @return Connection
     */
    public static function getConnection($key)
    {
        if (isset(self::$connections[$key]))
        {
            return self::$connections[$key];
        }

        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Drops the connection from the connection manager
     *
     * Does not actually close it since there is no close
     * method in PDO.
     *
     * @access public
     * @param  string $key Name of the connection to forget about
     * @return void
     */
    public static function dropConnection($key)
    {
        if (isset(self::$connections[$key]))
        {
            self::$connections[$key]->connection = NULL;
            unset(self::$connections[$key]);
        }
    }
}
