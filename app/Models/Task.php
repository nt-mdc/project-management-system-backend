<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    public $table = 'tasks';
    public $timestamps = "false";
    protected $fillable = [
        'user_id',
        'project_id',
        'title',
        'description',
        'start_at',
        'end_at',
        'priority',
        'status',
        'assigned_email'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'assigned_email');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }
}
