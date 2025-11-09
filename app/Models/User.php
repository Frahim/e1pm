<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // add this
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // helper checks
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager' || $this->isAdmin(); // admin implies manager power
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    // optional: relationship if you want quick projects owned
    public function ownedProjects()
    {
        return $this->hasMany(Project::class, 'owner_id');
    }
}
