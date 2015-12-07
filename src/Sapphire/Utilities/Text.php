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
namespace Sapphire\Utilities;

/**
 * Text Class
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Text
{
    /**
     * Calculate the length of the string
     *
     * @access public
     * @param  string $str
     * @return string
     */
    public static function length($str)
    {
        preg_match_all("/./u", $str, $match);

        return count($match[0]);
    }

    // ------------------------------------------------------------------------

    /**
     * String to be truncate
     *
     * @access public
     * @param  string  $str
     * @param  integer $start
     * @param  string  $length
     * @param  string  $suffix
     * @param  string  $charset
     * @return string
     */
    public static function truncate($str, $start = 0, $length = NULL, $suffix = '', $charset = 'utf-8')
    {
        if ($length === NULL)
        {
            $length = $start;
            $start = 0;
        }

        if ($length >= self::length($str))
        {
            $suffix = '';
        }

        if (function_exists("mb_substr"))
        {
            $str = mb_substr($str, $start, $length, $charset) . $suffix;
        }
        elseif (function_exists('iconv_substr'))
        {
            $str = iconv_substr($str, $start, $length, $charset) . $suffix;
        }
        else
        {
            $regex = [
                'utf-8'  => '/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/',
                'gb2312' => '/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/',
                'gbk'    => '/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/',
                'big5'   => '/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/'
            ];

            preg_match_all($regex[$charset], $str, $match);
            $str = join('', array_slice($match[0], $start, $length));
        }

        return $str . $suffix;
    }
}
