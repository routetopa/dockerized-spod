<?php

namespace LucaVicidomini\BladeMaterialize\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class FormFacade
 * @package LucaVicidomini\BladeMaterialize\Facades
 */
class FormFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'mform';
    }

}