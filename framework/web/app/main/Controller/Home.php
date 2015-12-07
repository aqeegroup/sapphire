<?php
namespace Controller;
use Helper\Boot;

/**
 *---------------------------------------------------------------
 * Default Home Controller
 *---------------------------------------------------------------
 *
 * You may wish to use controllers instead of, or in addition 
 * to, Closure based routes. That's great! Here is an example 
 * controller method to get you started. To route to this 
 * controller, just add the route:
 *
 * Route::get('/', 'Controller\Home::index');
 */
class Home
{
    public function index()
    {
        Boot::welcome();
    }
}
