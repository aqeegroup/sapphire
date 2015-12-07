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
 * Template Class
 *
 * @author  Lorne Wang < post@lorne.wang >
 * @package Sapphire
 */
class Template
{
    /**
     * Template ID
     *
     * @var string
     */
    public $templateId = '';

    /**
     * Options
     *
     * @var array
     */
    private $options = [];

    /**
     * Tags
     *
     * @var array
     */
    public $tags = [];

    /**
     * View Path
     *
     * @var string
     */
    private $viewPath = '';

    /**
     * Layout Path
     *
     * @var string
     */
    private $layoutPath = '';

    /**
     * Assign Variables
     *
     * @var array
     */
    private $variables = [];

    /**
     * Constructor
     *
     * @access public
     * @param  array $options
     */
    public function __construct($options)
    {
        $this->templateId = md5(json_encode($options));
        $this->options = $options;
        $this->initTags();
        TemplateManager::add($this);
    }

    // --------------------------------------------------------------------

    /**
     * Define Tag
     *
     * @access public
     * @param  string $tag
     * @param  mixed  $mixed
     * @return void
     */
    public function define($tag, $mixed)
    {
        $this->tags[] = [$tag, $mixed];
    }

    // --------------------------------------------------------------------

    /**
     * Assign Variables
     *
     * @access public
     * @param  mixed $mixed
     * @param  mixed $value
     * @return void
     */
    public function assign($mixed, $value = NULL)
    {
        if (is_object($mixed))
        {
            $mixed = (array) $mixed;
        }

        if (is_array($mixed))
        {
            $this->variables = array_merge($this->variables, $mixed);
        }
        elseif (is_object($this->variables))
        {
            $this->variables->$mixed = $value;
        }
        else
        {
            $this->variables[$mixed] = $value;
        }
    }

    // --------------------------------------------------------------------

    /**
     * Display View
     *
     * @access public
     * @param  string $view View file path
     * @return void
     */
    public function render($view = '')
    {
        $this->viewPath = $view;

        @extract($this->variables);

        unset($key, $value, $view);

        if ($this->layoutPath)
        {
            $this->viewPath = $this->viewPath();
            include $this->compiledPath($this->viewPath($this->layoutPath));
        }
        else
        {
            include $this->compiledPath($this->viewPath($this->viewPath));
        }
    }

    // --------------------------------------------------------------------

    /**
     * Layout view
     *
     * @access public
     * @param  string $layoutPath
     * @return void
     */
    public function layout($layoutPath)
    {
        $this->layoutPath = $layoutPath;
    }

    // --------------------------------------------------------------------

    /**
     * Placeholder
     *
     * @access public
     * @return void
     */
    public function holder()
    {
        $this->layoutPath = NULL;
        $this->render($this->viewPath);
    }

    // --------------------------------------------------------------------

    /**
     * View path
     *
     * @access protected
     * @param  string $view View file path
     * @return string
     */
    protected function viewPath($view = '')
    {
        return $this->options['template_directory'] . DIRECTORY_SEPARATOR . "{$view}.{$this->options['file_suffix']}";
    }

    // --------------------------------------------------------------------

    /**
     * Compiled path
     *
     * @access protected
     * @param  string $path
     * @return string
     */
    protected function compiledPath($path)
    {
        if ( ! $this->options['compiler_enable'])
        {
            return $path;
        }

        $compiledPath = $this->options['compiled_directory'] . DIRECTORY_SEPARATOR . md5($this->templateId . $path) . '.php';

        if ( ! is_file($compiledPath) || ($this->options['debug_enabled'] && filemtime($compiledPath) < filemtime($path)))
        {
            if ( ! is_file($path))
            {
                die($path . ' view page file can not found.');
            }

            $code = file_get_contents($path);
            $code = $this->parser($code);

            file_put_contents($compiledPath, $code);
        }

        return $compiledPath;
    }

    // --------------------------------------------------------------------

    /**
     * Tags Parser
     *
     * @access protected
     * @param  string $code Template source code
     * @return string
     */
    protected function parser($code)
    {
        $code = preg_replace_callback('/' . $this->options['left_delimiter'] . '(.+?)' . $this->options['right_delimiter'] . '/', function ($match)
        {
            $_code = $match[1];

            foreach ($this->tags as $exp)
            {
                if (is_callable($exp[1]))
                {
                    $_code = preg_replace_callback($exp[0], $exp[1], $_code);
                }
                else
                {
                    $_code = preg_replace($exp[0], $exp[1], $_code);
                }
            }

            return $_code == $match[1] ? $match[0] : $_code;
        }, $code);

        return $code;
    }

    // --------------------------------------------------------------------

    /**
     * Initialize Tags
     *
     * @access protected
     * @return void
     */
    protected function initTags()
    {
        $this->define("/^loop\s+(\S+)\s+(\S+)\s*\|(\S+),(\S+)\|\s*$/", "<?php $3=-1;$4=count((array)$1);foreach((array) $1 as $2) { $3++; ?>");
        $this->define("/^loop\s+(\S+)\s+(\S+)\s*\|(\S+)\|\s*$/", "<?php $3=-1;foreach((array) $1 as $2) { $3++; ?>");
        $this->define("/^loop\s+(\S+)\s+(\S+)\s*$/", "<?php foreach((array) $1 as $2) { ?>");
        $this->define("/^loop\s+(\S+)\s+(\S+)\s+(\S+)\s*$/", "<?php foreach($1 as $2 => $3) { ?>");
        $this->define("/^\/loop$/", "<?php } ?>");
        $this->define("/^if\s+([^\}]+)$/", "<?php if ($1) { ?>");
        $this->define("/^elseif\s+([^\}]+)$/", "<?php } elseif ($1) { ?>");
        $this->define("/^else$/", "<?php } else { ?>");
        $this->define("/^\/if$/", "<?php } ?>");
        $this->define("/^=\s*(.+)$/", "<?php echo $1; ?>");
        $this->define("/^(\\$\w+(?:(?:\[.+?\])|(?:->\w+))*)(?:\|(.+))?$/", "<?php echo isset($1) ? $1 : '$2'; ?>");
        $this->define("/^template\s+[\"']?([^\}\"']+)[\"']?$/", "<?php Sapphire\\Template\\TemplateManager::get('{$this->templateId}')->render('$1'); ?>");
        $this->define("/^yield$/", "<?php Sapphire\\Template\\TemplateManager::get('{$this->templateId}')->holder(); ?>");
    }
}
