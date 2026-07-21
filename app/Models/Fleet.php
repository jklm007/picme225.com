<?php

namespace App\Models;

use App\Notifications\FleetResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Fleet extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'email',
        'password',
        'company',
        'logo',
        'mobile',
        'user_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new FleetResetPassword($token));
    }

    /**
     * Get the companies owned by this fleet owner.
     */
    public function companies()
    {
        return $this->hasMany(InterurbanCompany::class);
    }
}
