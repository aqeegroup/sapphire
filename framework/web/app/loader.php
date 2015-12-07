<?php
use Sapphire\Support\Facades\Loader;

/**
 *---------------------------------------------------------------
 * AUTOLOAD CACHE ENABLED
 *---------------------------------------------------------------
 *
 * After open cached, all loading paths will be cached.
 * The next request, will save path search time, improve
 * the performance of load.
 */
Loader::setCacheEnabled(FALSE);

/**
 *---------------------------------------------------------------
 * AUTOLOAD CACHE TTL
 *---------------------------------------------------------------
 *
 * When time expires to search path again and update the cache
 * file. The production environment is stable, you can set up
 * a long time which is used to ensure the performance.
 *
 * Load an exception occurs or the original path change from
 * time to time, you can shorten the value, or even make it
 * reset to 0, or close the caching functionality.
 */
Loader::setCacheTTL(60);

/**
 *---------------------------------------------------------------
 * AUTOLOAD CACHE TIME KEY
 *---------------------------------------------------------------
 *
 * A key that exist in the cache file array, it will be used
 * to represent the cache file creation time, please don't try
 * to use the key have the same name.
 */
Loader::setCacheTimeKey('__cache_time');

/**
 *---------------------------------------------------------------
 * AUTOLOAD CACHE FILE
 *---------------------------------------------------------------
 *
 * Used to store the path of the cache file, the directory
 * need to write permissions.
 */
Loader::setCacheFilePath(APP_PATH . 'temp/autoload.php');

/**
 *---------------------------------------------------------------
 * AUTOLOAD ADD PATH
 *---------------------------------------------------------------
 * Need for additional search path, you can use addPath method
 * add new extension path one by one.
 */
Loader::addPath(APP_PATH . 'main');
