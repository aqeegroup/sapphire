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

defined('APP_PATH') OR exit('No direct script access allowed');

/**
 * Route Class
 *
 * Parses URIs and determines routing.
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Route
{
    /**
     * Current URI string
     *
     * @var string
     */
    public $uri;

    /**
     * Get routes
     *
     * @var array
     */
    public $gets = [];

    /**
     * Post routes
     *
     * @var array
     */
    public $posts = [];

    /**
     * Put routes
     *
     * @var array
     */
    public $puts = [];

    /**
     * Delete routes
     *
     * @var array
     */
    public $deletes = [];

    /**
     * All requests routes
     *
     * @var array
     */
    public $requests = [];

    /**
     * Missing route
     *
     * @var mixed
     */
    public $missing;

    public function __construct()
    {
        $uri = new URI;
        $this->uri = $uri->getUriString() ?: '/';
    }

    /**
     * Start
     *
     * @access public
     * @return void
     */
    public function start()
    {
        $routes = $this->routes();

        // Is there a literal match?  If so we're done
        if (isset($routes[$this->uri]) && ($value = $routes[$this->uri]))
        {
            if (self::dispatch($value, [$this->uri]) !== FALSE)
            {
                return;
            }
        }

        // Loop through the route array looking for wild-cards
        foreach ($routes as $key => $value)
        {
            // Convert wild-cards to RegEx
            $key = str_replace([':any', ':num'], ['.+', '[0-9]+'], $key);

            // Does the RegEx match?
            if (preg_match('#^' . $key . '$#', $this->uri, $captures))
            {
                // Do we have a back-reference?
                if (is_string($value) && strpos($value, '$') !== FALSE && strpos($key, '(') !== FALSE)
                {
                    $value = preg_replace('#^' . $key . '$#', $value, self::$uri);
                }

                if (self::dispatch($value, $captures) !== FALSE)
                {
                    return;
                }
            }
        }

        // If we got this far it means we didn't encounter a
        // matching route so we'll default set the controller route
        self::dispatch($this->missing);
    }

    // --------------------------------------------------------------------

    /**
     * Auto routing
     *
     * @access public
     * @param  string $directory
     * @param  string $prefix
     * @return void
     */
    public function auto($directory, $prefix = '')
    {
        $basedir = APP_PATH . $directory . '/';
        $this->requests['[0-9a-zA-Z_\-/]+'] = function ($uri) use ($basedir, $prefix)
        {
            $origSegments = explode('/', $uri);
            $humpSegments = array_map(function($segment){
                return preg_replace_callback('/(?:^|_)([a-z])/', function($matches){
                    return strtoupper($matches[1]);
                }, $segment);
            }, $origSegments);

            if (file_exists($basedir . $humpSegments[0] . '.php'))
            {
                $class  = $prefix . '\\' . ucfirst($humpSegments[0]);
                $method =  isset($humpSegments[1]) ? lcfirst($humpSegments[1]) : 'index';

                if (method_exists($class, $method))
                {
                    call_user_func_array([new $class, $method], array_slice($origSegments, 2));
                    return TRUE;
                }
            }
            else
            {
                $path = '';

                for ($i = 0, $l = count($humpSegments); $i < $l; $i++)
                {
                    $class = $prefix . '\\';
                    $path .= ucfirst($humpSegments[$i]) . '/';

                    if (empty($humpSegments[$i + 1]) || ! is_dir($basedir . $path))
                    {
                        break;
                    }

                    $class .= str_replace('/', '\\', $path) . ucfirst($humpSegments[$i + 1]);

                    if (class_exists($class))
                    {
                        $method = isset($humpSegments[$i + 2]) ? lcfirst($humpSegments[$i + 2]) : 'index';

                        if (method_exists($class, $method))
                        {
                            call_user_func_array([new $class, $method], array_slice($origSegments, $i + 3));
                            return TRUE;
                        }
                    }
                }
            }

            return FALSE;
        };
    }

    // --------------------------------------------------------------------

    /**
     * Get route
     *
     * @access public
     * @param  string $uri   URI string
     * @param  mixed  $mixed Closure or string
     * @return void
     */
    public function get($uri, $mixed)
    {
        $this->gets[$uri] = $mixed;
    }

    // --------------------------------------------------------------------

    /**
     * Post route
     *
     * @access public
     * @param  string $uri   URI string
     * @param  mixed  $mixed Closure or string
     * @return void
     */
    public function post($uri, $mixed)
    {
        $this->posts[$uri] = $mixed;
    }

    // --------------------------------------------------------------------

    /**
     * Put route
     *
     * @access public
     * @param  string $uri   URI string
     * @param  mixed  $mixed Closure or string
     * @return void
     */
    public function put($uri, $mixed)
    {
        $this->puts[$uri] = $mixed;
    }

    // --------------------------------------------------------------------

    /**
     * Delete route
     *
     * @access public
     * @param  string $uri   URI string
     * @param  mixed  $mixed Closure or string
     * @return void
     */
    public function delete($uri, $mixed)
    {
        $this->deletes[$uri] = $mixed;
    }

    // --------------------------------------------------------------------

    /**
     * All requests route
     *
     * @access public
     * @param  string $uri   URI string
     * @param  mixed  $mixed Closure or string
     * @return void
     */
    public function all($uri, $mixed)
    {
        $this->requests[$uri] = $mixed;
    }

    // --------------------------------------------------------------------

    /**
     * Route missing
     *
     * @access public
     * @param  mixed $mixed Closure or string
     * @return void
     */
    public function missing($mixed)
    {
        $this->missing = $mixed;
    }

    // --------------------------------------------------------------------

    /**
     * Dispatch to Closure
     *
     * @access protected
     * @param  mixed $mixed
     * @param  array $params
     * @return boolean
     */
    protected function dispatch(&$mixed, $params = [])
    {
        if (is_string($mixed))
        {
            list($class, $method) = explode('::', $mixed);

            return call_user_func_array([new $class, $method], []);
        }
        elseif (is_callable($mixed))
        {
            return call_user_func_array($mixed, $params);
        }

        return FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Fetch Routes
     *
     * @access protected
     * @return array
     */
    protected function routes()
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'get';

        switch ($method)
        {
            case 'get':
                $routes = $this->gets;
                break;
            case 'post':
                $routes = $this->posts;
                break;
            case 'put':
                $routes = $this->puts;
                break;
            case 'delete':
                $routes = $this->deletes;
                break;
            default:
                $routes = $this->gets;
                break;
        }

        return array_merge($this->requests, $routes);
    }
}
