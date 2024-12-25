<?php

namespace App\Templates;

class NotFound extends Template
{
    protected string|array $templatePaths = [
        '404.php'
    ];

    public function __construct() {}
}
