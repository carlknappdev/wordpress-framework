<?php

namespace App\Templates;

class Author extends Template
{
    protected string|array $templatePaths = [
        'author.twig',
        'archive.twig'
    ];

    public function __construct()
    {
        $context = $this->getContext();

        if (isset($context['author'])) {
            $title = sprintf(__('Archive of %s', 'theme'), $context['author']->name());

            $this->setContext([
                'title' => $title,
            ]);
        }
    }
}
