<?php
return [
    /**
     * --------------------------------------------------------------------------
     * CACHE ADAPTER
     * --------------------------------------------------------------------------
     *
     * This option controls the default cache adapter.
     * By default, we will use the dummy adapter but you may specify any
     * of the other wonderful adapters provided here.
     *
     * Supported: "dummy", "file", "memcached"
     */
    'adapter' => 'file',

    /**
     * --------------------------------------------------------------------------
     * CACHE KEY PREFIX
     * --------------------------------------------------------------------------
     *
     * When utilizing a RAM based store such as APC or Memcached, there might
     * be other applications utilizing the same cache. So, we'll specify a
     * value to get prefixed to all our keys so we can avoid collisions.
     */
    'key_prefix' => '',

    /**
     * --------------------------------------------------------------------------
     * CACHE DIRECTORY PATH
     * --------------------------------------------------------------------------
     *
     * Leave this BLANK unless you would like to set something
     * other than the default path/to/cache/ directory. Use
     * a full server path with trailing slash.
     */
    'cache_path' => APP_PATH . 'temp'
];