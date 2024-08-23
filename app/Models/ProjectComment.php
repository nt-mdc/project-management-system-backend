<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectComment extends Model
{
    use HasFactory;
    public $table = 'project_comments';
    protected $fillable = [
        'user_id',
        'project_id',
        'content'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
