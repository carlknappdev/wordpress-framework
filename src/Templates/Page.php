<?php

namespace App\Templates;

class Page extends Template
{
    protected string|array $templatePaths = [
        'page.twig'
    ];

    public function __construct() {}
}
