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
namespace Sapphire\Queue;

/**
 * Queue Class
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Queue
{
    /**
     * Queue adapter
     *
     * @var Object
     */
    protected $adapter = NULL;

    /**
     * Constructor
     *
     * @access public
     * @param  array $options
     */
    public function __construct($options)
    {
        $class = __NAMESPACE__ . '\Adapter\\' . ucwords($options['adapter']);
        $this->adapter = new $class($options);
    }

    public function push($key, $message)
    {
        return $this->adapter->push($key, $message);
    }

    public function pop($key)
    {
        return $this->adapter->push($key);
    }

    public function publish($key, $message)
    {
        return $this->adapter->publish($key, $message);
    }

    public function subscribe($key, $message)
    {
        return $this->adapter->subscribe($key, $message);
    }
}
