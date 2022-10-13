<?php

namespace LucaVicidomini\BladeMaterialize;

use Illuminate\Support\Facades\Blade;

class BladeExtender
{

    protected static function extractParameter( $expression ) {
        $expression = trim( $expression );

        // Laravel 5.2 compatibility
        if ( substr( $expression, 0, 1) == '(' )
        {
            $expression = substr( $expression, 1 , -1 );
        }

        $params = explode( ',', $expression );
        $params = array_map( function( $v ) {
            return trim($v, '\'');
        }, $params );
        return $params;
    }

    public static function extend()
    {
        Blade::directive( 'row', function( $expression ) {
            $cssClasses = self::extractParameter( $expression )[ 0 ];
            return "<div class=\"row {$cssClasses}\">";
        } );

        Blade::directive( 'endrow', function( $expression ) {
            return '</div>';
        } );

        Blade::directive( 'col', function( $expression ) {
            $cssClasses = self::extractParameter( $expression )[ 0 ];
            return "<div class=\"col {$cssClasses}\">";
        } );

        Blade::directive( 'endcol', function( $expression ) {
            return '</div>';
        } );
    }

}