<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description'
    ];

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value
     */
    public static function set($key, $value, $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description
            ]
        );
    }

    /**
     * Get next budget number
     */
    public static function getNextBudgetNumber()
    {
        $currentNumber = (int) self::get('budget_counter', 0);
        $nextNumber = $currentNumber + 1;
        
        // Update counter
        self::set('budget_counter', $nextNumber, 'Contador de orçamentos');
        
        return str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Reset budget counter
     */
    public static function resetBudgetCounter()
    {
        return self::set('budget_counter', 0, 'Contador de orçamentos');
    }
}
