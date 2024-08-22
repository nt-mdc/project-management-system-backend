<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilePhoto extends Model
{
    use HasFactory;
    public $table = "profile_photo";
    public $timestamps = false;

    public $fillable = [
        'user_id',
        'url'
    ];
}
