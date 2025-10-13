<?php

namespace McGo\Barekey\Contracts;

interface AbilitiesEnumContract
{

    public static function values(): array;

    public static function toString(self|string $ability): string;

    public static function granted(array $granted, self|string $needed): bool;
}