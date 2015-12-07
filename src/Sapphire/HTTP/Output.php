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
namespace Sapphire\Http;

/**
 * Output Class
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Output
{
    /**
     * Set HTTP Status Header
     *
     * @param  integer $code status code
     * @param  string  $text
     * @return void
     */
    public function setStatusHeader($code = 200, $text = '')
    {
        $stat = [
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',

            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',

            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            422 => 'Unprocessable Entity',

            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        ];

        if (empty($code) OR ! is_numeric($code))
        {
            die('Status codes must be numeric');
        }

        is_int($code) OR $code = (int) $code;

        if (empty($text))
        {
            if (isset($stat[$code]))
            {
                $text = $stat[$code];
            }
            else
            {
                die('No status text available. Please check your status code number or supply your own message text.');
            }
        }

        if (strpos(PHP_SAPI, 'cgi') === 0)
        {
            header('Status: ' . $code . ' ' . $text, TRUE);
        }
        else
        {
            $serverProtocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
            header($serverProtocol . ' ' . $code . ' ' . $text, TRUE, $code);
        }
    }
}
