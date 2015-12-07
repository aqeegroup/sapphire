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
namespace Sapphire\Queue\Adapter;

use Sapphire\Queue\QueueInterface;

/**
 * Queue Adapter for Redis
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Redis implements QueueInterface
{
    /**
     * Directory in which to save cache files
     *
     * @var string
     */
    protected $redis = NULL;

    /**
     * Constructor
     *
     * @access public
     * @param  array $options
     */
    public function __construct($options)
    {
        $this->redis = new Redis($options['dsn'], $options['timeout']);
    }

    /**
     * Push message
     *
     * @access public
     * @param  string $key
     * @param  string $message
     * @return mixed
     */
    public function push($key, $message)
    {
        $this->redis->rpush($key, $message);
    }

    // ------------------------------------------------------------------------

    /**
     * Pop message
     *
     * @access public
     * @param  string $key
     * @return mixed
     */
    public function pop($key)
    {
        $this->redis->lpop($key);
    }

    // ------------------------------------------------------------------------

    /**
     * Publish message
     *
     * @access public
     * @param  string $id Cache ID
     * @return mixed
     */
    public function publish($key, $message)
    {

    }

    // ------------------------------------------------------------------------

    /**
     * Fetch from cache
     *
     * @access public
     * @param  string $id Cache ID
     * @return mixed
     */
    public function subscribe($key)
    {

    }
}
