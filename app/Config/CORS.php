<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class CORS extends BaseConfig
{
    public $allowedOrigins = [
        'http://whf.ybomedia.ro:3000',
    ];

    public $allowedMethods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ];

    public $allowedHeaders = [
        'DNT',
        'Keep-Alive',
        'User-Agent',
        'X-Requested-With',
        'If-Modified-Since',
        'Cache-Control',
        'Content-Type',
        'YBO-Token',
    ];

    public $exposedHeaders = [];

    public $maxAge = 0;

    public $supportsCredentials = true;
}