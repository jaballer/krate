<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'setting_key', 'setting_value', 'setting_type', 'category',
    'description', 'is_private', 'created_by', 'updated_by',
])]
class Setting extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_private' => 'boolean',
        ];
    }

    /** Look up a setting and return its value cast to its declared type. */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('setting_key', $key)->first();

        return $setting === null ? $default : $setting->typedValue();
    }

    /** The stored value coerced according to `setting_type`. */
    public function typedValue(): mixed
    {
        return match ($this->setting_type) {
            'boolean' => filter_var($this->setting_value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->setting_value,
            'float' => (float) $this->setting_value,
            'json', 'array' => json_decode((string) $this->setting_value, true),
            default => $this->setting_value,
        };
    }
}
