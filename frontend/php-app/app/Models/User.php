<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'vk_id',
        'email',
        'username',
        'password_hash',
        'role',
        'balance',
        'is_active',
        'is_blocked',
    ];

    protected $hidden = ['password_hash', 'remember_token'];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getAuthPassword() {
        return $this->password_hash;
    }

    public function getAuthPasswordName() {
        return 'password_hash';
    }

    public function bots() {
        return $this->hasMany(Bot::class, 'user_id');
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function isAdmin() {
        return in_array($this->role, ['admin', 'superadmin']);
    }
}
