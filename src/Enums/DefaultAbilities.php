<?php

namespace McGo\Barekey\Enums;

use McGo\Barekey\Contracts\AbilitiesEnumContract;

/**
 * Usage example:
 *
 * Gate::authorize(Ability::SampleCreate->value);
 *
 * as middleware:
 * Route::middleware(['can:' . Ability::SampleCreate->value])
 *
 * as validation:
 * $request->validate([
 *   'abilities'   => ['array'],
 *   'abilities.*' => [Rule::in(Ability::values())],
 * ]);
 */
enum DefaultAbilities: string implements AbilitiesEnumContract
{
    // Core / Admin
    case Admin = 'admin';

    // Sample
    // case SampleCreate = 'sample:create';

    public static function values(): array
    {
        return array_map(fn(self $c) => $c->value, self::cases());
    }


    public static function toString(self|string|AbilitiesEnumContract $ability): string
    {
        return $ability instanceof self ? $ability->value : $ability;
    }

    public static function granted(array $granted, self|string|AbilitiesEnumContract $needed): bool
    {
        $neededStr = self::toString($needed);

        foreach ($granted as $rule) {
            $ruleStr = self::toString($rule);

            if ($ruleStr === $neededStr) {
                return true;
            }

            if ($ruleStr === self::Admin->value) {
                return true;
            }

            if (str_contains($ruleStr, '*')) {
                $pattern = '/^' . str_replace('\*', '.*', preg_quote($ruleStr, '/')) . '$/i';
                if (preg_match($pattern, $neededStr)) {
                    return true;
                }
            }
        }

        return false;
    }
}
