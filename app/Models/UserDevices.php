<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\SoftDeletes;

class UserDevices extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'device_token',
        'device_type',
    ];

    protected $table = 'user_devices';
    protected $softDeletes = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
