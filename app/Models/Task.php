<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'creator_id',
        'assignee_id',
        'title',
        'description',
        'status',
        'priority',
        'due_at',
        'position',
    ];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class,'creator_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class,'assignee_id');
    }
}
