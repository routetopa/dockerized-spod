<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * A setting that can be customized by an administrator.
 *
 * @package App
 */
class Setting extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'value',
    ];

    /**
     * The primary key field.
     *
     * @var array
     */
    protected $primaryKey = 'key';

    /**
     * Tells Eloquent that our key is non-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

	/**
	 * Retrieve a setting from cache.
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public static function retrieve( $key )
	{
		return Cache::remember(
			"settings.{$key}",
			10000, // 1 week
			function() use( $key ) { return self::find( $key )->value; } );
	}

	/**
	 * Remove this setting from the cache.
	 */
	public function purge()
	{
		if ( $this->key )
		{
			Cache::forget( "settings.{$this->key}" );
		}
	}
}