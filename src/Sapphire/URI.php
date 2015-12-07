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
 * URI Class
 *
 * Parses URIs and determines routing
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class URI
{
    /**
     * Current URI string
     *
     * @var string
     */
    public $uriString = '';

    /**
     * List of URI segments
     *
     * Starts at 1 instead of 0.
     *
     * @var array
     */
    public $segments = [];

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        $this->setUriString(
            PHP_SAPI === 'cli'
                ? $this->parseArgv()
                : $this->parseRequestUri()
        );
    }

    // --------------------------------------------------------------------

    /**
     * Get URI string
     *
     * @access public
     * @return string
     */
    public function getUriString()
    {
        return $this->uriString;
    }

    // --------------------------------------------------------------------

    /**
     * Set URI string
     *
     * @access public
     * @param  string $str
     * @return void
     */
    public function setUriString($str)
    {
        // Filter out control characters and trim slashes
        $this->uriString = trim($this->removeInvisibleCharacters($str, FALSE), '/');

        if ($this->uriString)
        {
            $this->segments[0] = NULL;

            // Populate the segments array
            foreach (explode('/', trim($this->uriString, '/')) as $val)
            {
                $val = trim($val);

                if ($val !== '')
                {
                    $this->segments[] = $val;
                }
            }

            unset($this->segments[0]);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Parse REQUEST_URI
     *
     * Will parse REQUEST_URI and automatically detect the URI from it,
     * while fixing the query string if necessary.
     *
     * @access public
     * @return string
     */
    protected function parseRequestUri()
    {
        if ( ! isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']))
        {
            return '';
        }

        // parse_url() returns FALSE if no host is present, but the path or query string
        // contains a colon followed by a number
        $urlInfo = parse_url('http://dummy' . $_SERVER['REQUEST_URI']);
        $query = isset($urlInfo['query']) ? $urlInfo['query'] : '';
        $uri = isset($urlInfo['path']) ? $urlInfo['path'] : '';

        if (isset($_SERVER['SCRIPT_NAME'][0]))
        {
            if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
            {
                $uri = (string) substr($uri, strlen($_SERVER['SCRIPT_NAME']));
            }
            elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
            {
                $uri = (string) substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
            }
        }

        // This sec
        //tion ensures that even on servers that require the URI to be in the query string (Nginx) a correct
        // URI is found, and also fixes the QUERY_STRING server var and $_GET array.
        if (trim($uri, '/') === '' && strncmp($query, '/', 1) === 0)
        {
            $query = explode('?', $query, 2);
            $uri = $query[0];
            $_SERVER['QUERY_STRING'] = isset($query[1]) ? $query[1] : '';
        }
        else
        {
            $_SERVER['QUERY_STRING'] = $query;
        }

        parse_str($_SERVER['QUERY_STRING'], $_GET);

        // Do some final cleaning of the URI and return it
        return ($uri === '/' OR $uri === '') ? '/' : $this->removeRelativeDirectory($uri);
    }

    // --------------------------------------------------------------------

    /**
     * Parse CLI arguments
     *
     * Take each command line argument and assume it is a URI segment.
     *
     * @access public
     * @return string
     */
    protected function parseArgv()
    {
        $args = array_slice($_SERVER['argv'], 1);
        return $args ? implode('/', $args) : '';
    }

    // --------------------------------------------------------------------

    /**
     * Remove relative directory (../) and multi slashes (///)
     *
     * Do some final cleaning of the URI and return it.
     *
     * @access public
     * @param  string $uri
     * @return string
     */
    protected function removeRelativeDirectory($uri)
    {
        $uris = [];
        $tok = strtok($uri, '/');

        while ($tok !== FALSE)
        {
            if (( ! empty($tok) OR $tok === '0') && $tok !== '..')
            {
                $uris[] = $tok;
            }
            $tok = strtok('/');
        }

        return implode('/', $uris);
    }

    // --------------------------------------------------------------------

    /**
     * Remove Invisible Characters
     *
     * This prevents sandwiching NULL characters
     * between ascii characters, like Java\0script.
     *
     * @param  string  $str
     * @param  boolean $urlEncoded
     * @return string
     */
    public function removeInvisibleCharacters($str, $urlEncoded = TRUE)
    {
        $nonDisplayable = [];

        // Every control character except newline (dec 10),
        // carriage return (dec 13) and horizontal tab (dec 09)
        if ($urlEncoded)
        {
            $nonDisplayable[] = '/%0[0-8bcef]/'; // url encoded 00-08, 11, 12, 14, 15
            $nonDisplayable[] = '/%1[0-9a-f]/'; // url encoded 16-31
        }

        $nonDisplayable[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'; // 00-08, 11, 12, 14-31, 127

        do
        {
            $str = preg_replace($nonDisplayable, '', $str, -1, $count);
        } while ($count);

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch URI Segment or Segments
     *
     * @access public
     * @param  integer $index
     * @param  mixed   $default
     * @return mixed
     */
    public function segment($index = NULL, $default = NULL)
    {
        return isset($this->segments[$index]) ? $this->segments[$index] : $default;
    }

    // --------------------------------------------------------------------

    /**
     * Routed Segment Array
     *
     * @access public
     * @return array
     */
    public function segmentArray()
    {
        return $this->segments;
    }
}
