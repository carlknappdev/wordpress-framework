<?php

namespace App\Templates;

class Index extends Template
{
    protected string|array $templatePaths = [
        'index.twig'
    ];

    public function __construct()
    {
        if (is_home()) {
            array_unshift($this->templatePaths, 'front-page.twig', 'home.twig');
        }

        $this->setContext([
            'foo' => 'bar',
        ]);
    }
}
