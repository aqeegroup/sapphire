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

use Sapphire\Template\Template as _Template;

/**
 * Template Facade
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */

/**
 * Allows the class to know which magic methods are callable.
 *
 * @method static void define(string $tag, mixed $mixed)
 * @method static void assign(mixed $mixed, mixed $value = '')
 * @method static void render(string $view)
 * @method static void layout(string $layoutPath)
 * @method static void holder()
 */
class Template extends Facade
{
    public static function getFacadeAccessor()
    {
        return new _Template(Config::get('template'));
    }
}
