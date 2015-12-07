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
 * Number Class
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Number
{
    /**
     * Translates a number to a short alphanumeric version
     *
     * Translated any number up to 9007199254740992
     * to a shorter version in letters e.g.:
     * 9007199254740989 --> PpQXn7COf
     *
     * specifying the second argument true, it will
     * translate back e.g.:
     * PpQXn7COf --> 9007199254740989
     *
     * this function is based on any2dec && dec2any by
     * framer[at]mail[dot]ru
     * see: http://nl3.php.net/manual/en/function.base-convert.php#52450
     *
     * If you want the alphaID to be at least 3 letter long, use the
     * $padUp = 3 argument
     *
     * In most cases this is better than totally random ID generators
     * because this can easily avoid duplicate ID's.
     * For example if you correlate the alpha ID to an auto incrementing ID
     * in your database, you're done.
     *
     * The reverse is done because it makes it slightly more cryptic,
     * but it also makes it easier to spread lots of IDs in different
     * directories on your filesystem. Example:
     * $part1 = substr($alpha_id,0,1);
     * $part2 = substr($alpha_id,1,1);
     * $part3 = substr($alpha_id,2,strlen($alpha_id));
     * $destindir = "/".$part1."/".$part2."/".$part3;
     * // by reversing, directories are more evenly spread out. The
     * // first 26 directories already occupy 26 main levels
     *
     * more info on limitation:
     * - http://blade.nagaokaut.ac.jp/cgi-bin/scat.rb/ruby/ruby-talk/165372
     *
     * if you really need this for bigger numbers you probably have to look
     * at things like: http://theserverpages.com/php/manual/en/ref.bc.php
     * or: http://theserverpages.com/php/manual/en/ref.gmp.php
     * but I haven't really dugg into this. If you have more info on those
     * matters feel free to leave a comment.
     *
     * @access public
     * @param  mixed   $in       String or long input to translate
     * @param  boolean $toNumber Reverses translation when true
     * @param  mixed   $padUp    Number or boolean padds the result up to a specified length
     * @param  string  $passKey  Supplying a password makes it harder to calculate the original ID
     * @return mixed   string or long
     */  
    public static function alpha($in, $toNumber = false, $padUp = false, $passKey = null)
    {
        $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

        if ($passKey !== null)
        {
            // Although this function's purpose is to just make the  
            // ID short - and not so much secure,  
            // with this patch by Simon Franz (http://blog.snaky.org/)  
            // you can optionally supply a password to make it harder  
            // to calculate the corresponding numeric ID  
            for ($n = 0; $n < strlen($index); $n++)
            {
                $i[] = substr($index, $n, 1);
            }

            $passhash = hash('sha256', $passKey);
            $passhash = (strlen($passhash) < strlen($index)) ? hash('sha512', $passKey) : $passhash;

            for ($n = 0; $n < strlen($index); $n++)
            {
                $p[] = substr($passhash, $n, 1);
            }

            array_multisort($p, SORT_DESC, $i);
            $index = implode($i);
        }

        $base = strlen($index);

        if ($toNumber)
        {
            // Digital number  <<--  alphabet letter code  
            $in = strrev($in);
            $out = 0;
            $len = strlen($in) - 1;
            for ($t = 0; $t <= $len; $t++)
            {
                $bcpow = bcpow($base, $len - $t);
                $out = $out + strpos($index, substr($in, $t, 1)) * $bcpow;
            }

            if (is_numeric($padUp))
            {
                $padUp--;
                if ($padUp > 0)
                {
                    $out -= pow($base, $padUp);
                }
            }

            $out = sprintf('%F', $out);
            $out = substr($out, 0, strpos($out, '.'));
        }
        else
        {
            // Digital number  -->>  alphabet letter code  
            if (is_numeric($padUp))
            {
                $padUp--;
                if ($padUp > 0)
                {
                    $in += pow($base, $padUp);
                }
            }

            $out = '';
            for ($t = floor(log($in, $base)); $t >= 0; $t--)
            {
                $bcp = bcpow($base, $t);
                $a = floor($in / $bcp) % $base;
                $out = $out . substr($index, $a, 1);
                $in = $in - ($a * $bcp);
            }

            $out = strrev($out); // reverse  
        }

        return $out;
    }
}
