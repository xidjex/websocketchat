<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    const STATUS_BANNED = 0;
    const STATUS_ACTIVE = 1;

    const MUTE_ON = 1;
    const MUTE_OFF = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'state', 'mute'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function getIsExists($name, $email) {
        return User::where(['name' => $name, 'email' => $email])->first();
    }

    public static function isEmailExists($email) {
        return User::where('email', $email)->first();
    }

    public static function isNameExists($name) {
        return User::where('name', $name)->first();
    }
}
