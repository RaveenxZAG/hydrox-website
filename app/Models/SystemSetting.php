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
        return Cache::rememberForever("system-setting:{$key}", function () use ($key, $default): ?string {
            return static::query()->where('key', $key)->value('value') ?? $default;
        });
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
            'abn' => '89669467261',
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
