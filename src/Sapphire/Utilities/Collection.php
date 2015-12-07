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
 * Collection Class
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Collection
{
    /**
     * Multi sort
     *
     * The sorted array is now in the return value of the
     * function instead of being passed by reference.
     *
     * @access public
     * @return string
     */
    public static function multisort()
    {
        $args = func_get_args();
        $data = array_shift($args);

        foreach ($args as $n => $field)
        {
            if (is_string($field))
            {
                $tmp = [];
                foreach ($data as $key => $row)
                {
                    $tmp[$key] = $row[$field];
                }

                $args[$n] = $tmp;
            }
        }

        $args[] = &$data;
        call_user_func_array('array_multisort', $args);

        return array_pop($args);
    }
}
