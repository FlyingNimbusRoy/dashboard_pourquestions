<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * `modifiers.effects` is stored by the game as a JSON string that itself contains pretty-printed JSON
 * with \r\n line breaks (i.e. double-encoded). Reads decode to an array; writes re-create that format so
 * live data stays compatible. A value equal to what is stored keeps its original raw text.
 *
 * @implements CastsAttributes<array<mixed>|null, array<mixed>|string|null>
 */
class GameEffects implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        return self::decode($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (is_string($value)) {
            $value = self::decode($value);
        }
        if (! is_array($value) || $value === []) {
            return null;
        }
        $raw = $attributes[$key] ?? null;
        if ($raw !== null && self::decode($raw) === $value) {
            return $raw;
        }
        $inner = str_replace("\n", "\r\n", json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return json_encode($inner, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /** @return array<mixed>|null */
    public static function decode(mixed $value): ?array
    {
        for ($i = 0; $i < 3 && is_string($value) && $value !== ''; $i++) {
            $value = json_decode($value, true);
        }

        return is_array($value) ? $value : null;
    }
}
