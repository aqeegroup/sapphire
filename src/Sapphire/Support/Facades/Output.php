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
namespace Sapphire\Support\Facades;

use Sapphire\Http\Output as _Output;

/**
 * Output Facade
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */

/**
 * Allows the class to know which magic methods are callable.
 *
 * @method static void setStatusHeader($code = 200, $text = '')
 */
class Output extends Facade
{
    public static function getFacadeAccessor()
    {
        return new _Output;
    }
}
