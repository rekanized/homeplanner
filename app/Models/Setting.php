<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use Auditable;
    protected $fillable = ['key', 'value', 'group'];

    public static function get(string $key, $default = null)
    {
        return self::where('key', $key)->first()?->value ?? $default;
    }

    public static function set(string $key, $value, string $group = 'general')
    {
        return self::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }
}
