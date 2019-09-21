<?php

namespace App;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Container
{
    private $services;

    public function __construct(string $baseUrl)
    {
        $this->services = [
            'client' => new \Goutte\Client(),
            'bbUrlHelper' => new \App\BbUrlHelper($baseUrl),
            'logger' => new Logger('default', [new StreamHandler('php://stderr', Logger::INFO)])
        ];
    }

    public function get(string $service)
    {
        return $this->services[$service];
    }
}