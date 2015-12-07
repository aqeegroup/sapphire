<?php
/**
 * --------------------------------------------------------------------------
 * DATABASE CONNECTIVITY SETTINGS
 * --------------------------------------------------------------------------
 * This file will contain the settings needed to access your database.
 *
 *   'driver'   The database driver. e.g.: mysql
 *   'hostname' The hostname of your database server.
 *   'username' The username used to connect to the database
 *   'password' The password used to connect to the database
 *   'database' The name of the database you want to connect to
 *   'pconnect' TRUE/FALSE - Whether to use a persistent connection
 *   'encoding' The character set used in communicating with the database
 *
 * The array key variable lets you choose which connection group to
 * make active.  By default there is only one group (the 'default' group).
 */
return [
    'default' => [
        'driver'   => 'mysql',
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => '',
        'encoding' => 'utf8',
        'pconnect' => FALSE
    ]
];