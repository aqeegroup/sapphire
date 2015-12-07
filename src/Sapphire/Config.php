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
 * Config Class
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Config
{
    /**
     * Files loaded
     *
     * @var array
     */
    private $loaded = [];

    /**
     * Data cached
     *
     * @var array
     */
    private $cached = [];

    /**
     * Files directory
     *
     * @var string
     */
    private $directory = '';

    /**
     * Files sub directory
     *
     * @var string
     */
    private $subDirectory = '';

    /**
     * Set Directory
     *
     * @access public
     * @param  string $directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    // --------------------------------------------------------------------

    /**
     * Set Sub Directory
     *
     * @access public
     * @param  string $subDirectory
     */
    public function setSubDirectory($subDirectory)
    {
        $this->subDirectory = $subDirectory;
    }

    // --------------------------------------------------------------------

    /**
     * Get item value for key
     *
     * @access public
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = FALSE)
    {
        if (isset($this->cached[$key]))
        {
            return $this->cached[$key];
        }

        $keys = explode('.', $key);
        $file = array_shift($keys);

        if (empty($this->loaded[$file]))
        {
            if (file_exists("{$this->directory}/{$this->subDirectory}/{$file}.php"))
            {
                $this->loaded[$file] = require "{$this->directory}/{$this->subDirectory}/{$file}.php";
            }
            elseif (file_exists("{$this->directory}/{$file}.php"))
            {
                $this->loaded[$file] = require "{$this->directory}/{$file}.php";
            }
            else
            {
                return $default;
            }
        }

        $data = $this->loaded[$file];

        foreach ($keys as $value)
        {
            if (array_key_exists($value, $data))
            {
                $data = $data[$value];
            }
            else
            {
                return $default;
            }
        }

        return ($this->cached[$key] = $data);
    }
}
