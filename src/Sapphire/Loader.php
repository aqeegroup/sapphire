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
namespace Sapphire;

/**
 * Loader Class
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Loader
{
    /**
     * Search paths
     *
     * @var array
     */
    private $paths = [];

    /**
     * Cache files
     *
     * @var array
     */
    private $cache = [];

    /**
     * Cache enabled
     *
     * @var boolean
     */
    private $cacheEnabled = FALSE;

    /**
     * Cache ttl
     *
     * @var integer
     */
    private $cacheTTL = 60;

    /**
     * Cache file path
     *
     * @var string
     */
    private $cacheFilePath = '';

    /**
     * Cache time key
     *
     * @var string
     */
    private $cacheTimeKey = '__cache__time';

    /**
     * SPL autoload register and initialize
     *
     * @access public
     * @return void
     */
    public function register()
    {
        if ($this->cacheEnabled)
        {
            $this->cache = $this->import($this->cacheFilePath);

            if (isset($this->cache[$this->cacheTimeKey]) && time() > $this->cache[$this->cacheTimeKey] + $this->cacheTTL)
            {
                $this->cache = [];
            }
        }

        spl_autoload_register([$this, 'handler']);
    }

    // --------------------------------------------------------------------

    /**
     * Register autoload function
     *
     * @access public
     * @param  string $className
     * @return void
     */
    public function handler($className)
    {
        if ($this->cacheEnabled && isset($this->cache[$className]))
        {
            require $this->cache[$className];
            return;
        }

        $relative = str_replace(['\\', '_'], DIRECTORY_SEPARATOR, $className);
        $absolute = '';

        foreach ($this->paths as $path)
        {
            $absolute = $path . DIRECTORY_SEPARATOR . $relative;

            if ($this->import($absolute . '.php')) break;
            if ($this->import(($absolute .= strrchr($absolute, DIRECTORY_SEPARATOR)) . '.php')) break;

            $absolute = '';
        }

        if ($absolute && $this->cacheEnabled)
        {
            $this->cache[$this->cacheTimeKey] = time();
            $this->cache[$className] = "$absolute.php";
            file_put_contents($this->cacheFilePath, '<?php return ' . var_export($this->cache, TRUE) . ';?>');
        }
    }

    // --------------------------------------------------------------------

    /**
     * Trying include the file
     *
     * @access public
     * @param  string $path
     * @return boolean
     */
    public function import($path)
    {
        return file_exists($path) ? (require_once $path) : FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Add search path
     *
     * @access public
     * @param  string $path
     * @return void
     */
    public function addPath($path)
    {
        $this->paths[] = $path;
    }

    // --------------------------------------------------------------------

    /**
     * Set cache enabled
     *
     * @access public
     * @param  boolean $isEnabled
     * @return void
     */
    public function setCacheEnabled($isEnabled = FALSE)
    {
        $this->cacheEnabled = $isEnabled;
    }

    // --------------------------------------------------------------------

    /**
     * Set cache file path
     *
     * @access public
     * @param  string $path
     * @return void
     */
    public function setCacheFilePath($path)
    {
        $this->cacheFilePath = $path;
    }

    // --------------------------------------------------------------------

    /**
     * Set cache ttl
     *
     * @access public
     * @param  string $ttl
     * @return void
     */
    public function setCacheTTL($ttl)
    {
        $this->cacheTTL = $ttl;
    }

    // --------------------------------------------------------------------

    /**
     * Set cache time key
     *
     * @access public
     * @param  string $key
     * @return void
     */
    public function setCacheTimeKey($key)
    {
        $this->cacheTimeKey = $key;
    }
}
