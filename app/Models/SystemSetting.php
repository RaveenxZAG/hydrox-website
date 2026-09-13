<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        try {
            return Cache::rememberForever("system-setting:{$key}", function () use ($key, $default): ?string {
                try {
                    return static::query()->where('key', $key)->value('value') ?? $default;
                } catch (\Throwable) {
                    return $default;
                }
            });
        } catch (\Throwable) {
            try {
                return static::query()->where('key', $key)->value('value') ?? $default;
            } catch (\Throwable) {
                return $default;
            }
        }
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("system-setting:{$key}");
    }

    public static function businessInformation(): array
    {
        $defaults = [
            'company_name' => 'Hydrox Facility Management Cleaning Services Pty. Ltd',
            'abn' => '35670676785',
            'website' => 'https://hydrox.au',
            'email' => 'admin@hydrox.au',
            'billing_email' => 'admin@hydrox.au',
            'phone' => '0418 222 477',
            'mobile_primary' => '0418 222 477',
            'mobile_secondary' => '',
            'address_line_1' => '',
            'address_line_2' => '',
            'address_line_3' => '',
            'city' => '',
            'state' => '',
            'postcode' => '',
            'country' => '',
        ];

        $stored = json_decode((string) static::getValue('business_information', ''), true);

        $stored = is_array($stored) ? array_intersect_key($stored, $defaults) : [];

        return array_replace_recursive($defaults, $stored);
    }
}
