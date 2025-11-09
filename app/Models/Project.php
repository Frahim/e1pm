<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Project.php
class Project extends Model
{
    protected $fillable = ['owner_id','name','description','status','start_date','end_date'];

    public function owner() { return $this->belongsTo(User::class,'owner_id'); }
    //public function members() { return $this->belongsToMany(User::class)->withTimestamps()->withPivot('role'); }
   // public function tasks() { return $this->hasMany(Task::class); }
    public function activity() { return $this->hasMany(ActivityLog::class); }
    public function attachments() { return $this->morphMany(Attachment::class, 'attachable'); }


public function members()
{
    return $this->belongsToMany(\App\Models\User::class)->withPivot('role')->withTimestamps();
}

/**
 * Return role string for a user id or null if not a member.
 */
public function roleFor($userId)
{
    $member = $this->members()->where('user_id', $userId)->first();
    return $member ? $member->pivot->role : null;
}

/**
 * Convenience checks
 */
public function isOwner($userId)
{
    return $this->owner_id === $userId;
}

public function isManager($userId)
{
    return $this->roleFor($userId) === 'manager';
}

public function isMember($userId)
{
    return $this->members()->where('user_id', $userId)->exists();
}

public function tasks()
{
    return $this->hasMany(\App\Models\Task::class);
}
}
