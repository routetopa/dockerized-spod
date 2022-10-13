<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * An OAuth2 Client.
 *
 * @package App
 */
class Client extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id', 'client_secret', 'redirect_uri', 'grant_types', 'scope', 'user_id',
    ];

    /**
     * The primary key field.
     *
     * @var array
     */
    protected $primaryKey = 'client_id';

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'oauth_clients';

    /**
     * Tells Eloquent that our key is non-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

}