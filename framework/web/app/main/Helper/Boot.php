<?php
namespace Helper;
use Sapphire\Support\Facades\Template;
use Sapphire\Support\Facades\Output;
use Sapphire\Support\Facades\Input;

class Boot
{
    /**
     * Template render
     *
     * @param  string $path
     * @return void
     */
    public static function display($path)
    {
        Template::render((Input::isCliRequest() ? 'cli' : 'html') . DIRECTORY_SEPARATOR . $path);
    }

    // ------------------------------------------------------------------------

    /**
     * Show welcome page
     *
     * @return void
     */
    public static function welcome()
    {
        self::display('index');
    }

    // ------------------------------------------------------------------------

    /**
     * Show 404 page
     *
     * @return void
     */
    public static function page404()
    {
        Input::isCliRequest() OR Output::setStatusHeader(404);
        self::display('404');
    }
}
