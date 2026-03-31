<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'amount',
        'provider',
        'status',
        'type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
