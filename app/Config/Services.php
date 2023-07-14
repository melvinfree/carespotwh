<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use CodeIgniter\CORS\CORS;

class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */

    // Add the following code

    public static $aliases = [
        // ...
        'cors' => CORS::class,
    ];

    public static $middlewares = [
        // ...
        'cors',
    ];
}