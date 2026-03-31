<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'key',
        'value',
    ];
    
    protected $casts = [
        'value' => 'string',
    ];
    
    /**
     * Получить значение настройки
     */
    public static function get($key, $default = null)
    {
        $setting = static::find($key);
        
        if (!$setting) {
            return $default;
        }
        
        // Декодируем JSONB значение
        $value = json_decode($setting->value, true);
        
        // Если это JSON, возвращаем как есть, иначе как строку
        return json_last_error() === JSON_ERROR_NONE ? $value : $setting->value;
    }
    
    /**
     * Установить значение настройки
     */
    public static function set($key, $value)
    {
        // Кодируем boolean и другие типы в JSON
        $jsonValue = json_encode($value);
        
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $jsonValue]
        );
    }
    
    /**
     * Проверить включена ли регистрация
     */
    public static function isRegistrationEnabled()
    {
        $value = static::get('registration_enabled', 'true');
        
        // Проверяем различные форматы true
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            return strtolower($value) === 'true' || $value === '1';
        }
        
        return (bool) $value;
    }
}
