<?php
return [
    /**
     * --------------------------------------------------------------------------
     * TEMPLATE DIRECTORY
     * --------------------------------------------------------------------------
     *
     * The template view files storage directory.
     */
    'template_directory' =>  ENTRY_PATH . 'assets/tpl',

    /**
     * --------------------------------------------------------------------------
     * TEMPLATE FILE SUFFIX
     * --------------------------------------------------------------------------
     *
     * The template view files default suffix.
     */
    'file_suffix' => 'phtml',

    /**
     * --------------------------------------------------------------------------
     * TEMPLATE COMPILER ENABLE
     * --------------------------------------------------------------------------
     *
     * Whether to open the template compiler engine.
     */
    'compiler_enable' => FALSE,

    /**
     * --------------------------------------------------------------------------
     * TEMPLATE DEBUG ENABLE
     * --------------------------------------------------------------------------
     *
     * Whether to open the template compiler debug mode.
     */
    'debug_enabled' => TRUE,

    /**
     * --------------------------------------------------------------------------
     * TEMPLATE COMPILED DIRECTORY
     * --------------------------------------------------------------------------
     *
     * The template compiled files storage directory.
     */
    'compiled_directory' => APP_PATH . 'temp',

    /**
     * --------------------------------------------------------------------------
     * TEMPLATE LEFT DELIMITER
     * --------------------------------------------------------------------------
     *
     * The template left delimiter.
     */
    'left_delimiter' => '{',

    /**
     * --------------------------------------------------------------------------
     * TEMPLATE RIGHT DELIMITER
     * --------------------------------------------------------------------------
     *
     * The template right delimiter.
     */
    'right_delimiter' => '}'
];
