<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverAssignmentLog extends Model
{
    protected $fillable = [
        'user_request_id',
        'dispatcher_id',
        'assignment_mode',
        'provider_id',
        'status'
    ];

    public function request()
    {
        return $this->belongsTo(UserRequests::class, 'user_request_id');
    }

    public function dispatcher()
    {
        return $this->belongsTo(Dispatcher::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
