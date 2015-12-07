<?php
namespace Sapphire\Support\Facades;

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
use Sapphire\Database\Factory;
use Sapphire\Database\QueryBuilder;
use PDOStatement;

/**
 * Database Facade
 *
 * @method static QueryBuilder table(string $table_name)
 * @method static PDOStatement query(string $sql)
 * @method static array columns(string $table_name)
 */
class Database extends Facade
{
    public static function getFacadeAccessor()
    {
        return self::connection('default');
    }

    public static function connection($key)
    {
        return Factory::create($key, Config::get('database'));
    }
}
