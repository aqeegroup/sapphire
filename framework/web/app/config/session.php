<?php
return [
    /**
     * --------------------------------------------------------------------------
     * SESSION HANDLER
     * --------------------------------------------------------------------------
     *
     * This option controls the default session "handler" that will be used on
     * requests. By default, we will use the lightweight native handler but
     * you may specify any of the other wonderful handlers provided here.
     *
     * Supported: "file", "database", "memcached", "redis"
     */
    'handler' => 'file',

    /**
     * --------------------------------------------------------------------------
     * SESSION SAVE PATH
     * --------------------------------------------------------------------------
     *
     * The location to save sessions to, driver dependent.
     *
     * For the 'files' driver, it's a path to a writable directory.
     * WARNING: Only absolute paths are supported!
     *
     * For the 'database' handler, it's a table name.
     * Please read up the manual for the format with other session drivers.
     *
     * IMPORTANT: You are REQUIRED to set a valid save path!
     */
    'save_path' => NULL,

    /**
     * --------------------------------------------------------------------------
     * SESSION EXPIRATION
     * --------------------------------------------------------------------------
     *
     * The number of SECONDS you want the session to last.
     * Setting to 0 (zero) means expire when the browser is closed.
     */
    'expiration' => 7200,

    /**
     * --------------------------------------------------------------------------
     * SESSION MATCH IP
     * --------------------------------------------------------------------------
     *
     * Whether to match the user's IP address when reading the session data.
     * WARNING: If you're using the database driver, don't forget to update
     * your session table's PRIMARY KEY when changing this setting.
     */
    'match_ip' => FALSE,

    /**
     * --------------------------------------------------------------------------
     * SESSION UPDATE
     * --------------------------------------------------------------------------
     *
     * How many seconds between regenerating the session ID.
     */
    'time_to_update' => 300,

    /**
     * --------------------------------------------------------------------------
     * SESSION DESTROY
     * --------------------------------------------------------------------------
     *
     * Whether to destroy session data associated with the old session ID
     * when auto-regenerating the session ID. When set to FALSE, the data
     * will be later deleted by the garbage collector.
     */
    'regenerate_destroy' => FALSE,

    /**
     * --------------------------------------------------------------------------
     * SESSION COOKIE NAME
     * --------------------------------------------------------------------------
     *
     * Here you may change the name of the cookie used to identify a session
     * instance by ID. The name specified here will get used every time a
     * new session cookie is created by the framework for every driver.
     */
    'cookie_name' => 'php_session',

    /**
     * --------------------------------------------------------------------------
     * SESSION COOKIE PATH
     * --------------------------------------------------------------------------
     *
     * The session cookie path determines the path for which the cookie will
     * be regarded as available. Typically, this will be the root path of
     * your application but you are free to change this when necessary.
     */
    'cookie_path' => '/',

    /**
     * --------------------------------------------------------------------------
     * SESSION COOKIE DOMAIN
     * --------------------------------------------------------------------------
     *
     * Here you may change the domain of the cookie used to identify a session
     * in your application. This will determine which domains the cookie is
     * available to in your application. A sensible default has been set.
     */
    'cookie_domain' => '',

    /**
     * --------------------------------------------------------------------------
     * SESSION COOKIE SECURE
     * --------------------------------------------------------------------------
     *
     * By setting this option to true, session cookies will only be sent back
     * to the server if the browser has a HTTPS connection. This will keep
     * the cookie from being sent to you if it can not be done securely.
     */
    'cookie_secure' => FALSE
];
