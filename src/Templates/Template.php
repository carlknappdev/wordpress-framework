<?php

namespace App\Templates;

use Timber\Timber;

abstract class Template
{
    protected string|array $templatePaths;

    public function getContext()
    {
        return Timber::context();
    }

    public function setContext($context)
    {
        Timber::context($context);
    }

    public function render()
    {
        Timber::render($this->templatePaths, Timber::context());
    }
}
