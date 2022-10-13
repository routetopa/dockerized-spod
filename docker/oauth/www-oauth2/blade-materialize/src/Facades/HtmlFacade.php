<?php

namespace LucaVicidomini\BladeMaterialize\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class HtmlFacade
 * @package LucaVicidomini\BladeMaterialize\Facades
 */
class HtmlFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mhtml';
    }

}