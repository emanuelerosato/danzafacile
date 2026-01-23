<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'key',
        'value',
        'type',
        'description'
    ];
    
    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        return static::castValue($setting->value, $setting->type);
    }
    
    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $type = 'string', ?string $description = null)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => static::castToString($value, $type),
                'type' => $type,
                'description' => $description
            ]
        );
        
        return $setting;
    }
    
    /**
     * Cast value to appropriate type
     */
    protected static function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
    
    /**
     * Cast value to string for storage
     */
    protected static function castToString($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'integer':
                return (string) $value;
            case 'json':
                return json_encode($value);
            default:
                return (string) $value;
        }
    }
    
    /**
     * Get all settings as key-value pairs
     */
    public static function getAll(): array
    {
        $settings = [];
        
        foreach (static::all() as $setting) {
            $settings[$setting->key] = static::castValue($setting->value, $setting->type);
        }
        
        return $settings;
    }
}
