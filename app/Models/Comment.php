<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Comment.php
class Comment extends Model
{
    protected $fillable = ['task_id','user_id','body'];
    public function task() { return $this->belongsTo(Task::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function attachments() { return $this->morphMany(Attachment::class, 'attachable'); }
}
