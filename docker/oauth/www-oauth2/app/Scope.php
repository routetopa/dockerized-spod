<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * An OAuth2 Scope.
 *
 * @package App
 */
class Scope extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'scope', 'is_default',
    ];

    /**
     * The primary key field.
     *
     * @var array
     */
    protected $primaryKey = 'scope';

    /**
     * Tells Eloquent that our key is non-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_scopes';

}