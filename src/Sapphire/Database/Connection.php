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

use PDO;
use PDOStatement;
use PDOException;

/**
 * Connection Class
 *
 * The base class for database connection adapters.
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
abstract class Connection
{
    /**
     * Default port
     *
     * @var integer
     */
    const DEFAULT_PORT = 0;

    /**
     * The quote character for stuff like column and field names
     *
     * @var string
     */
    const QUOTE_CHARACTER = '`';

    /**
     * The last query run
     *
     * @var string
     */
    protected $lastQuery = '';

    /**
     * The PDO connection object
     *
     * @var object
     */
    protected $connection = NULL;

    /**
     * Default PDO options to set for each connection
     *
     * @var array
     */
    protected static $PdoOptions = [
        PDO::ATTR_CASE               => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS       => PDO::NULL_NATURAL,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_STRINGIFY_FETCHES  => FALSE
    ];

    // --------------------------------------------------------------------

    /**
     * Multiple singletons pattern constructor,
     * and support the created new instance.
     *
     * @access public
     * @param  array $options
     */
    public function __construct($options = [])
    {
        $this->connection = @new PDO($this->dsn($options), $options['username'], $options['password'], self::$PdoOptions);
        $this->connection->setAttribute(PDO::ATTR_PERSISTENT, $options['pconnect']);
        $this->setCharset($options['encoding']);
    }

    // --------------------------------------------------------------------

    /**
     * Execute a raw SQL query on the database.
     *
     * @access public
     * @param  string $sql    Raw SQL string to execute
     * @param  array  $values Optional array of bind values
     * @return PDOStatement
     */
    public function query($sql, $values = [])
    {
        $this->lastQuery = $sql;
        $statement = $this->connection->prepare($sql);

        if ($values)
        {
            foreach ($values as $key => $value)
            {
                $type = FALSE;

                if (is_int($value))
                {
                    $type = PDO::PARAM_INT;
                }
                elseif (is_bool($value))
                {
                    $type = PDO::PARAM_BOOL;
                }
                elseif (is_null($value))
                {
                    $type = PDO::PARAM_NULL;
                }
                else
                {
                    $type = PDO::PARAM_STR;
                }

                if ($type)
                {
                    $statement->bindValue($key, $value, $type);
                }
            }
        }

        $statement->execute();

        return $statement;
    }

    // --------------------------------------------------------------------

    /**
     * Create a table object and returns the query builder
     *
     * @access public
     * @param  string $table Table name
     * @return QueryBuilder
     */
    public function table($table)
    {
        return (new QueryBuilder($this))->table($table);
    }

    // --------------------------------------------------------------------

    /**
     * Retrieve the insert id of the last model saved
     *
     * @access public
     * @param  string $sequence Optional name of a sequence to use
     * @return integer
     */
    public function insertId($sequence = NULL)
    {
        return $this->connection->lastInsertId($sequence);
    }

    // --------------------------------------------------------------------

    /**
     * Starts a transaction
     *
     * @access public
     */
    public function transaction()
    {
        if ( ! $this->connection->beginTransaction())
        {
            throw new PDOException($this);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Commits the current transaction
     *
     * @access public
     */
    public function commit()
    {
        if ( ! $this->connection->commit())
        {
            throw new PDOException($this);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Rollback a transaction
     *
     * @access public
     */
    public function rollback()
    {
        if ( ! $this->connection->rollback())
        {
            throw new PDOException($this);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Quote a name like table names and field names
     *
     * @access public
     * @param  string $string String to quote
     * @return string
     */
    public function quoteName($string)
    {
        return $string[0] === static::QUOTE_CHARACTER || $string[strlen($string) - 1] === static::QUOTE_CHARACTER ?
            $string : static::QUOTE_CHARACTER . $string . static::QUOTE_CHARACTER;
    }

    // --------------------------------------------------------------------

    /**
     * Build the DSN string
     *
     * @access public
     * @param  array  $options
     * @return string
     */
    abstract public function dsn($options);

    // --------------------------------------------------------------------

    /**
     * Adds a limit clause to the SQL query
     *
     * @access public
     * @param  string  $sql    the SQL statement
     * @param  integer $offset row offset to start at
     * @param  integer $limit  number of rows to return
     * @return string
     */
    abstract public function limit($sql, $offset, $limit);

    // --------------------------------------------------------------------

    /**
     * Retrieves column meta data for the specified table
     *
     * @access public
     * @param  string $table name of a table
     * @return array
     */
    abstract public function columns($table);

    // --------------------------------------------------------------------

    /**
     * Set the character encoding
     *
     * @access public
     * @param  string $charset charset name
     * @return void
     */
    abstract public function setCharset($charset);

    // --------------------------------------------------------------------

    /**
     * Specifies whether or not adapter can use LIMIT/ORDER clauses
     * with DELETE & UPDATE operations
     *
     * @access public
     * @return boolean
     */
    abstract public function acceptsLimitAndOrderForUpdateAndDelete();
}
