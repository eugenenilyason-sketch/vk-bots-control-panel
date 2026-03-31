<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    protected $table = 'bots';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'name',
        'status',
        'config',
        'webhook_url',
        'messages_sent',
        'messages_received',
    ];

    protected $casts = [
        'config' => 'array',
        'messages_sent' => 'integer',
        'messages_received' => 'integer',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
