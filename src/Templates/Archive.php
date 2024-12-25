<?php

namespace App\Templates;

class Archive extends Template
{
    protected string|array $templatePaths = [
        'archive.twig',
        'index.twig'
    ];

    public function __construct()
    {
        $title = 'Archive';
        if (is_day()) {
            $title = 'Archive: ' . get_the_date('D M Y');
        } elseif (is_month()) {
            $title = 'Archive: ' . get_the_date('M Y');
        } elseif (is_year()) {
            $title = 'Archive: ' . get_the_date('Y');
        } elseif (is_tag()) {
            $title = single_tag_title('', false);
        } elseif (is_category()) {
            $title = single_cat_title('', false);
        } elseif (is_post_type_archive()) {
            $title = post_type_archive_title('', false);
            array_unshift($templates, 'archive-' . get_post_type() . '.twig');
        }

        $this->setContext([
            'title' => $title
        ]);
    }
}
