# 🪶 Laravel Barekey

> **Authenticate everything – without users.**  
> A minimal, stateless API key authentication guard for Laravel.  
> No sessions. No Sanctum. No users. Just pure, verifiable keys.

---

## 🚀 Features

- 🧩 **Stateless API key guard** – powered by `Auth::viaRequest()`
- 🔐 **Secure hashing (SHA-256)** and prefix lookup for fast validation
- 🎯 **Abilities / Scopes** with wildcard support (`invoices:*`)
- 🧠 **Enum-friendly design** for type-safe permission checks
- ⚡ **No database overhead** beyond a single `api_keys` table
- 🧱 **Works with Laravel Gates**, `Auth::check()`, and `auth:apikey`
- 🛡️ Optional **rate limits**, `revoked_at`, and `expires_at` fields

---

## 📦 Installation

```bash
composer require mcgo/laravel-barekey
```

Then publish and run the migration:

```bash
php artisan vendor:publish --tag="barekey-migrations"
php artisan migrate
```

> You can optionally publish the config if you’d like to tweak the defaults:
>
> ```bash
> php artisan vendor:publish --tag="barekey-config"
> ```

---

## ⚙️ Setup

Register the guard in your `config/auth.php`:

```php
'guards' => [
    'apikey' => [
        'driver' => 'apikey',
        'provider' => null,
    ],
],
```

Barekey automatically registers its guard in your `AuthServiceProvider`
via `Auth::viaRequest('apikey', ...)`.

---

## 🧱 Model

Barekey ships with a simple `ApiKey` model and migration:

```php
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $fillable = [
        'name', 'prefix', 'hash', 'abilities',
        'expires_at', 'revoked_at', 'rate_limit_per_min',
    ];
}
```

To generate new keys:

```bash
php artisan barekey:make "Backend Service" --abilities=invoices:read,reports:read
```

Output example:

```
API Key (keep this secret!):
bare_AbC123xy_KJHSDfksja9sd823JKjd9sdlks

Name: Backend Service
Abilities: invoices:read, reports:read
```

---

## 🔑 Usage

Protect routes using the built-in middleware:

```php
Route::middleware('auth:apikey')->group(function () {
    Route::get('/status', fn() => ['ok' => true]);
});
```

You can also layer `can:` for ability-based checks:

```php
Route::middleware(['auth:apikey', 'can:invoices:read'])
    ->get('/invoices', [InvoiceController::class, 'index']);
```

Inside your controller, you can access the authenticated key:

```php
$key = request()->user(); // GenericUser with ->id, ->name, ->abilities
```

---

## 🧠 Abilities & Gates

Define abilities as strings or Enums – both work:

```php
Gate::before(function ($user, string $ability) {
    $abilities = (array) $user->abilities;
    return in_array('*', $abilities, true)
        || in_array($ability, $abilities, true)
        || str($abilities)->contains(fn($a) => str($ability)->isMatch($a));
});
```

Or use the included Enum helper:

```php
use App\Enums\Ability;

Gate::before(fn($user, $ability) => Ability::granted($user->abilities, $ability));
```

---

## 🧮 Example Enum

```php
namespace App\Enums;

enum Ability: string
{
    case InvoicesRead  = 'invoices:read';
    case InvoicesWrite = 'invoices:write';
    case ReportsRead   = 'reports:read';
    case Admin         = 'admin';
}
```

---

## 🧰 Rate Limiting (optional)

Each key may have its own `rate_limit_per_min` value.
Barekey automatically uses Laravel’s `RateLimiter` facade:

```php
if (RateLimiter::tooManyAttempts("apikey:{$key->id}", $key->rate_limit_per_min)) {
    abort(429, 'Too many requests');
}
```

---

## 🧼 Commands

| Command | Description |
|----------|--------------|
| `php artisan barekey:make` | Create a new API key |
| `php artisan barekey:list` | List all active API keys |
| `php artisan barekey:revoke <id>` | Revoke a key immediately |

---

## 🔒 Security Notes

- Always use HTTPS
- Never expose API keys in frontend code
- Rotate keys regularly
- Use `revoked_at` + `expires_at` to enforce lifecycle policies

---

## 🧪 Testing

```bash
php artisan test
```

Example:

```php
it('authenticates with valid API key', function () {
    $key = ApiKey::factory()->create([...]);

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$key->plain}",
    ])->getJson('/api/status');

    $response->assertOk()->assertJson(['ok' => true]);
});
```

---

## 💬 Punchline

> 🪶 **Laravel Barekey** — Authenticate everything, without users.

---

## 🧡 Credits

- Inspired by [Laravel Sanctum](https://laravel.com/docs/sanctum),  
  stripped to the essentials for user-free, machine-to-machine auth.
- Crafted by [McGo](https://github.com/McGo)

---

## 🪪 License

MIT © Mirko Haaser
