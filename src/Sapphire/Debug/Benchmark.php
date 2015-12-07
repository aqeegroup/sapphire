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
namespace Sapphire\Debug;

/**
 * Benchmark Class
 *
 * This class enables you to mark points and calculate
 * the time difference between them. Memory consumption
 * can also be displayed.
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Benchmark
{
    /**
     * Elapsed times
     *
     * @var array
     */
    private $times = [];

    /**
     * Memory usages
     *
     * @var array
     */
    private $sizes = [];

    /**
     * Set a benchmark marker
     *
     * Multiple calls to this function can be made so
     * that several execution points can be timed.
     *
     * @access public
     * @param  string $point point name
     * @return void
     */
    public function mark($point)
    {
        $this->times[$point] = microtime(TRUE);
        $this->sizes[$point] = memory_get_usage();
    }

    // --------------------------------------------------------------------

    /**
     * Elapsed time
     *
     * Calculates the time difference between two marked points.
     * If the first parameter is empty this function instead returns the
     * empty string pseudo-variable. This permits the full system
     * execution time to be shown in a template. The output class will
     * swap the real value for this variable.
     *
     * @param  string  $point1   A particular marked point
     * @param  string  $point2   A particular marked point
     * @param  integer $decimals Number of decimal places
     * @return string
     */
    public function elapsedTime($point1 = '', $point2 = '', $decimals = 5)
    {
        if ($point1 === '' && count($this->times) > 0)
        {
            $time1 = current($this->times);
        }
        elseif ($point1 !== '' && isset($this->times[$point1]))
        {
            $time1 = $this->times[$point1];
        }
        else
        {
            return '';
        }

        if ($point2 === '' || empty($this->times[$point2]))
        {
            $time2 = microtime(TRUE);
        }
        else
        {
            $time2 = $this->times[$point2];
        }

        return number_format($time2 - $time1, $decimals);
    }

    // --------------------------------------------------------------------

    /**
     * Memory usage
     *
     * Calculates the time difference between two marked points.
     * If the first parameter is empty this function instead returns the
     * empty string pseudo-variable. This permits the full system
     * execution time to be shown in a template. The output class will
     * swap the real value for this variable.
     *
     * @param  string $point1 A particular marked point
     * @param  string $point2 A particular marked point
     * @return string
     */
    public function memoryUsage($point1 = '', $point2 = '')
    {
        if ($point1 === '' && count($this->sizes) > 0)
        {
            $size1 = current($this->sizes);
        }
        elseif ($point1 !== '' && isset($this->sizes[$point1]))
        {
            $size1 = $this->sizes[$point1];
        }
        else
        {
            return '';
        }

        if ($point2 === '' || empty($this->sizes[$point2]))
        {
            $size2 = memory_get_usage();
        }
        else
        {
            $size2 = $this->sizes[$point2];
        }

        return self::unitConvert($size2 - $size1);
    }

    // --------------------------------------------------------------------

    /**
     * Storage unit conversion
     *
     * @access public
     * @param  integer $size
     * @return string
     */
    public static function unitConvert($size)
    {
        $units = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $units[$i];
    }
}
