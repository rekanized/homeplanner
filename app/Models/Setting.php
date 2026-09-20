<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use Auditable;

    private const ENCRYPTED_KEYS = [
        'google_client_secret',
    ];

    protected $fillable = ['key', 'value', 'group'];

    public static function get(string $key, $default = null)
    {
        return self::decodeValue($key, self::where('key', $key)->value('value'), $default);
    }

    public static function getMany(array $defaults): array
    {
        $values = self::whereIn('key', array_keys($defaults))->pluck('value', 'key');

        foreach ($defaults as $key => $default) {
            $defaults[$key] = self::decodeValue($key, $values->get($key), $default);
        }

        return $defaults;
    }

    private static function decodeValue(string $key, $value, $default)
    {
        if ($value === null) {
            return $default;
        }

        if (in_array($key, self::ENCRYPTED_KEYS, true)) {
            try {
                return Crypt::decryptString($value);
            } catch (DecryptException) {
                // Backwards compatibility for credentials stored before encryption was introduced.
                return $value;
            }
        }

        return $value;
    }

    public static function set(string $key, $value, string $group = 'general')
    {
        if (in_array($key, self::ENCRYPTED_KEYS, true) && filled($value)) {
            $value = Crypt::encryptString($value);
        }

        return self::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }

    public function hasSensitiveValue(): bool
    {
        return in_array($this->key, self::ENCRYPTED_KEYS, true);
    }
}
