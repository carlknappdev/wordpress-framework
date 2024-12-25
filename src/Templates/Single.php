<?php

namespace App\Templates;

use Timber\Timber;

class Single extends Template
{
    protected string|array $templatePaths = [
        'single.twig'
    ];

    public function __construct()
    {
        $context = $this->getContext();
        $post = $context['post'];

        $this->templatePaths = [
            'single-' . $post->post_type . '.twig',
            'single.twig'
        ];

        if (post_password_required($post->ID)) {
            $this->templatePaths = 'single-password.twig';
        }
    }
}
