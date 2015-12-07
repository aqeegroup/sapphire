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
namespace Sapphire\Template;

/**
 * Template Manager
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class TemplateManager
{
    private static $instances = [];

    /**
     * Add the template to the template manager
     *
     * @access public
     * @param  Template $template A template instance
     */
    public static function add(Template $template)
    {
        self::$instances[$template->templateId] = $template;
    }


    // --------------------------------------------------------------------

    /**
     * Get the template from the template manager
     *
     * @access public
     * @param  string $id Optional id of a template
     * @return Template
     */
    public static function get($id)
    {
        return isset(self::$instances[$id]) ? self::$instances[$id] : FALSE;
    }
}
