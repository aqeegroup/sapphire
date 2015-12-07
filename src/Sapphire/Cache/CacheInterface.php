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
namespace Sapphire\Cache;

interface CacheInterface
{
    /**
     * Get
     *
     * Since this is the dummy class, it's always going to return false.
     *
     * @access public
     * @param  string
     * @return boolean
     */
    public function get($id);

    // ------------------------------------------------------------------------

    /**
     * Cache Save
     *
     * @access public
     * @param  string  $id   Unique Key
     * @param  mixed   $data Data to store
     * @param  integer $ttl  Length of time (in seconds) to cache the data
     * @return boolean
     */
    public function set($id, $data, $ttl = 60);

    // ------------------------------------------------------------------------

    /**
     * Delete from Cache
     *
     * @access public
     * @param  mixed $id Unique identifier of the item in the cache
     * @return boolean
     */
    public function delete($id);

    // ------------------------------------------------------------------------

    /**
     * Increment a raw value
     *
     * @access public
     * @param  string  $id     Cache ID
     * @param  integer $offset Step/value to add
     * @return mixed
     */
    public function increment($id, $offset = 1);

    // ------------------------------------------------------------------------

    /**
     * Decrement a raw value
     *
     * @access public
     * @param  string  $id     Cache ID
     * @param  integer $offset Step/value to reduce by
     * @return mixed
     */
    public function decrement($id, $offset = 1);

    // ------------------------------------------------------------------------

    /**
     * Clean the cache
     *
     * @access public
     * @return boolean
     */
    public function clean();

    // ------------------------------------------------------------------------

    /**
     * Cache Info
     *
     * @access public
     * @param  string
     * @return boolean
     */
    public function cacheInfo($type = NULL);

    // ------------------------------------------------------------------------

    /**
     * Get Cache Metadata
     *
     * @access public
     * @param  mixed $id Key to get cache metadata on
     * @return boolean
     */
    public function getMetadata($id);

    // ------------------------------------------------------------------------

    /**
     * Is this caching driver supported on the system?
     * Of course this one is.
     *
     * @access public
     * @return boolean
     */
    public function isSupported();
}
