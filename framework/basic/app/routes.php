<?php
use Sapphire\Support\Facades\Route;

/**
 *---------------------------------------------------------------
 * APPLICATION ROUTES
 *---------------------------------------------------------------
 *
 * Here is where you can register all of the routes for an 
 * application. 
 * 
 * It's a breeze. Simply tell Lighter the URIs it should
 * respond to and give it the Closure to execute when that 
 * URI is requested.
 *
 * This route indicates which controller class should be
 * loaded if the URI contains no data. In the below
 * example, the "Home" class would be loaded.
 */
Route::get('/', 'Controller\Home::index');

/**
 *---------------------------------------------------------------
 * ROUTE MISSING
 *---------------------------------------------------------------
 *
 * This route will tell the Router which action or Closure
 * to use if those provided in the URL cannot be matched to
 * a valid route.
 */
Route::missing(function(){
    die('Request Not Found.');
});
