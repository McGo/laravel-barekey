<?php

namespace McGo\Barekey\Models;

use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use McGo\Barekey\Factories\ApiKeyFactory;

class ApiKey extends Model implements UserContract
{
    use Authenticatable;
    use HasFactory;

    protected $table = 'mcgo_barekey_apikeys';
    protected $fillable = [
        'uuid',
        'token',
        'abilities',
        'email',
        'expires_at',
        'revoked_at',
        'last_used_at'
    ];
    public $timestamps = true;

    protected $hidden = [
        'token'
    ];

    protected function casts(): array
    {
        return [
            'abilities' => AsArrayObject::class,
            'token' => 'encrypted',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        if ($this->revoked_at && now()->greaterThan($this->revoked_at)) {
            return false;
        }
        if ($this->expires_at && now()->greaterThan($this->expires_at)) {
            return false;
        }
        return true;
    }

    public function can(string $ability): bool
    {
        if (empty($this->abilities)) {
            return false;
        }
        return in_array($ability, (array)$this->abilities, true);
    }

    public static function newFactory()
    {
        return new ApiKeyFactory();
    }
}
