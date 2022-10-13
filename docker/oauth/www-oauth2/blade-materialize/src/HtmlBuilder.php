<?php

namespace LucaVicidomini\BladeMaterialize;

use BadMethodCallException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Routing\UrlGenerator;

class HtmlBuilder
{
    use Macroable {
        Macroable::__call as macroCall;
    }

    /**
     * The URL generator instance.
     *
     * @var \Illuminate\Contracts\Routing\UrlGenerator
     */
    protected $url;

    /**
     * The View Factory instance.
     *
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;

    /**
     * Create a new HTML builder instance.
     *
     * @param \Illuminate\Contracts\Routing\UrlGenerator $url
     * @param \Illuminate\Contracts\View\Factory         $view
     */
    public function __construct(UrlGenerator $url = null, Factory $view)
    {
        $this->url = $url;
        $this->view = $view;
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  string $method
     * @param  array  $parameters
     *
     * @return \Illuminate\Contracts\View\View|mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        try {
            return $this->componentCall($method, $parameters);
        } catch (BadMethodCallException $e) {
            //
        }
        try {
            return $this->macroCall($method, $parameters);
        } catch (BadMethodCallException $e) {
            //
        }
        throw new BadMethodCallException("Method {$method} does not exist.");
    }

}

