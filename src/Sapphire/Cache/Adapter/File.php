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
namespace Sapphire\Cache\Adapter;

use Sapphire\Cache\CacheInterface;
use Sapphire\Utilities\File as FileAlias;

/**
 * Cache Adapter for File
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class File implements CacheInterface
{
    /**
     * Directory in which to save cache files
     *
     * @var string
     */
    protected $cachePath;

    /**
     * Constructor
     *
     * @access public
     * @param  array $options
     */
    public function __construct($options)
    {
        $this->cachePath = rtrim($options['cache_path'], '/') . '/';
    }

    /**
     * Fetch from cache
     *
     * @access public
     * @param  string $id Cache ID
     * @return mixed
     */
    public function get($id)
    {
        $data = $this->read($id);

        return is_array($data) ? $data['data'] : FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Save into cache
     *
     * @access public
     * @param  string  $id   Cache ID
     * @param  mixed   $data Data to store
     * @param  integer $ttl  Time to live in seconds
     * @param  boolean $raw  Whether to store the raw value (unused)
     * @return boolean
     */
    public function set($id, $data, $ttl = 60, $raw = FALSE)
    {
        $contents = [
            'time' => time(),
            'ttl'  => $ttl,
            'data' => $data
        ];

        if (file_put_contents($this->cachePath . $id, serialize($contents)))
        {
            chmod($this->cachePath . $id, 0640);

            return TRUE;
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Delete from Cache
     *
     * @access public
     * @param  mixed $id Unique identifier of item in cache
     * @return boolean
     */
    public function delete($id)
    {
        return file_exists($this->cachePath . $id) ? unlink($this->cachePath . $id) : FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Increment a raw value
     *
     * @access public
     * @param  string  $id     Cache ID
     * @param  integer $offset Step/value to add
     * @return mixed
     */
    public function increment($id, $offset = 1)
    {
        $data = $this->read($id);

        if ($data === FALSE)
        {
            $data = ['data' => 0, 'ttl' => 60];
        }
        elseif ( ! is_int($data['data']))
        {
            return FALSE;
        }

        $new_value = $data['data'] + $offset;

        return $this->set($id, $new_value, $data['ttl'])
            ? $new_value
            : FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Decrement a raw value
     *
     * @access public
     * @param  string  $id     Cache ID
     * @param  integer $offset Step/value to reduce by
     * @return mixed
     */
    public function decrement($id, $offset = 1)
    {
        $data = $this->read($id);

        if ($data === FALSE)
        {
            $data = ['data' => 0, 'ttl' => 60];
        }
        elseif ( ! is_int($data['data']))
        {
            return FALSE;
        }

        $new_value = $data['data'] - $offset;

        return $this->set($id, $new_value, $data['ttl'])
            ? $new_value
            : FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Clean the Cache
     *
     * @access public
     * @return boolean
     */
    public function clean()
    {
        return FileAlias::delete($this->cachePath, FALSE, 0, TRUE);
    }

    // ------------------------------------------------------------------------

    /**
     * Cache Info
     *
     * Not supported by file-based caching
     *
     * @access public
     * @param  string
     * @return mixed
     */
    public function cacheInfo($type = NULL)
    {
        return FileAlias::getDirInfo($this->cachePath);
    }

    // ------------------------------------------------------------------------

    /**
     * Get Cache Metadata
     *
     * @access public
     * @param  mixed $id Key to get cache metadata on
     * @return mixed
     */
    public function getMetadata($id)
    {
        if ( ! file_exists($this->cachePath . $id))
        {
            return FALSE;
        }

        $data = unserialize(file_get_contents($this->cachePath . $id));

        if (is_array($data))
        {
            $mtime = filemtime($this->cachePath . $id);

            if ( ! isset($data['ttl']))
            {
                return FALSE;
            }

            return [
                'expire' => $mtime + $data['ttl'],
                'mtime'  => $mtime
            ];
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Is supported
     *
     * In the file driver, check to see that the cache directory is
     * indeed writable.
     *
     * @access public
     * @return boolean
     */
    public function isSupported()
    {
        return FileAlias::isReallyWritable($this->cachePath);
    }

    // ------------------------------------------------------------------------

    /**
     * Get all data
     *
     * Internal method to get all the relevant data about a cache item
     *
     * @access protected
     * @param  string $id Cache ID
     * @return mixed
     */
    protected function read($id)
    {
        if ( ! file_exists($this->cachePath . $id))
        {
            return FALSE;
        }

        $data = unserialize(file_get_contents($this->cachePath . $id));

        if ($data['ttl'] > 0 && time() > $data['time'] + $data['ttl'])
        {
            unlink($this->cachePath . $id);

            return FALSE;
        }

        return $data;
    }
}
