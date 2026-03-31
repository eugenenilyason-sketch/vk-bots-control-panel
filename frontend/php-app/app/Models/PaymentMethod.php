<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $keyType = 'string';
    
    // Отключаем автоматические timestamps
    public $timestamps = true;
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';
    
    protected $fillable = [
        'name',
        'display_name',
        'type',
        'icon',
        'description',
        'is_enabled',
        'min_amount',
        'max_amount',
        'commission',
        'api_key',
        'api_secret',
        'merchant_id',
        'settings',
    ];
    
    protected $casts = [
        'is_enabled' => 'boolean',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'settings' => 'array',
    ];
    
    protected $hidden = [
        'api_key_encrypted',
        'api_secret_encrypted',
        'merchant_id_encrypted',
    ];
    
    /**
     * Установить API ключ (с хэшированием)
     */
    public function setApiKeyAttribute($value)
    {
        if ($value) {
            $this->attributes['api_key_encrypted'] = Crypt::encryptString($value);
        }
    }
    
    /**
     * Получить API ключ (с расшифровкой)
     */
    public function getApiKeyAttribute($value)
    {
        if ($this->attributes['api_key_encrypted'] ?? null) {
            try {
                return Crypt::decryptString($this->attributes['api_key_encrypted']);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }
    
    /**
     * Установить API секрет (с хэшированием)
     */
    public function setApiSecretAttribute($value)
    {
        if ($value) {
            $this->attributes['api_secret_encrypted'] = Crypt::encryptString($value);
        }
    }
    
    /**
     * Получить API секрет (с расшифровкой)
     */
    public function getApiSecretAttribute($value)
    {
        if ($this->attributes['api_secret_encrypted'] ?? null) {
            try {
                return Crypt::decryptString($this->attributes['api_secret_encrypted']);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }
    
    /**
     * Установить Merchant ID (с хэшированием)
     */
    public function setMerchantIdAttribute($value)
    {
        if ($value) {
            $this->attributes['merchant_id_encrypted'] = Crypt::encryptString($value);
        }
    }
    
    /**
     * Получить Merchant ID (с расшифровкой)
     */
    public function getMerchantIdAttribute($value)
    {
        if ($this->attributes['merchant_id_encrypted'] ?? null) {
            try {
                return Crypt::decryptString($this->attributes['merchant_id_encrypted']);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }
    
    /**
     * Получить активные методы
     */
    public static function getActiveMethods()
    {
        return static::where('is_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Получить метод по имени
     */
    public static function getByName($name)
    {
        return static::find($name);
    }
}
