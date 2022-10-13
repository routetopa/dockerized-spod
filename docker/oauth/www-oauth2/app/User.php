<?php

namespace App;

use App\Notifications\ResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * A user that will be capable of logging into the authorization server and
 * of authorize OAuth2 clients.
 *
 * @package App
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_users';

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->roles == 'admin';
    }

    /**
     * The policies accepted/refused by the user
     */
    public function policies()
    {
	    return $this->belongsToMany( 'App\Policy' );
    }

    /**
     * @return FacebookLogin
     */
    public function facebook() {
        return $this->hasMany(FacebookLogin::class);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Ensure the email field is lower-case
     *
     * @param string $value
     * @return void
     */
    public function setEmailAttribute( $value )
    {
        $this->attributes[ 'email' ] = strtolower( $value );
    }

}
