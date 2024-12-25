<?php

namespace App\Templates;

class Search extends Template
{
    protected string|array $templatePaths = [
        'search.twig',
        'archive.twig',
        'index.twig'
    ];

    public function __construct()
    {
        $this->setContext([
            'title' => 'Search results for ' . get_search_query(),
        ]);
    }
}
