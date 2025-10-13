# 🪶 Laravel Barekey

> **Authenticate everything – without users.**  
> A minimal, stateless API key authentication guard for Laravel.  
> No sessions. No Sanctum. No users. Just pure, verifiable keys.

---

## 🚀 Features

- 🧩 **Stateless API key guard** – powered by `Auth::viaRequest()`
- 🔐 **Secure hashing (SHA-256)** and prefix lookup for fast validation
- 🎯 **Abilities / Scopes** with wildcard support (`invoices:*`) - even as route middleware
- 🧠 **Enum-friendly design** for type-safe permission checks
- ⚡ **No database overhead** beyond a single table for all your api keys
- 🧱 **Works with Laravel Gates**, `Auth::check()`, and `auth:apikey` middleware

---

## 📦 Installation

```bash
composer require mcgo/laravel-barekey
```

Then run the migration:

```bash
php artisan migrate
```

## ⚙️ Setup

Register the guard in your `config/auth.php`. You can provide your custom Abilities Enum, see packages DefaultAbilities
as example.

```php
'guards' => [
    'barekey' => [
        'driver' => 'apikey',
        'provider' => null,
        // 'abilities' => YourAbilitiesEnum::class 
    ],
],
```

Barekey automatically registers its guard in your `AuthServiceProvider`
via `Auth::viaRequest('barekey', ...)`.

---

## 🔐 Keys

To generate new keys:

```bash
php artisan barekey:make "My Service api key" --abilities=invoices:read,reports:read
```

Output example:

```

API Key generated, please use it as the following header:
Authorization: Bearer  593acec5-d9c2-43dd-9155-d93bad8c49e4:CJalcoa3ukYpkHa2ZfTWnRi0s4q8JPslSiqKbWXkls1suHMkJ8Ya6ggOKEBoEFje
Or as custom header:
X-Barekey-Token:  593acec5-d9c2-43dd-9155-d93bad8c49e4:CJalcoa3ukYpkHa2ZfTWnRi0s4q8JPslSiqKbWXkls1suHMkJ8Ya6ggOKEBoEFje

```

---

## 🔑 Usage

Protect routes using the built-in middleware:

```php
Route::middleware('auth:barekey')->group(function () {
    Route::get('/status', fn() => ['ok' => true]);
});
```

You can also layer `can:` for ability-based checks:

```php
Route::middleware(['auth:barekey', 'can:invoices:read'])
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

use McGo\Barekey\Contracts\AbilitiesEnumContract;

enum Ability: string implements AbilitiesEnumContract
{
    case InvoicesRead  = 'invoices:read';
    case InvoicesWrite = 'invoices:write';
    case ReportsRead   = 'reports:read';
    case Admin         = 'admin';
    
    // Implement the needed methods.
}
```


---

## 🧼 Commands

| Command | Description |
|----------|--------------|
| `php artisan barekey:make` | Create a new API key |

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

## 📋 Roadmap

- Implement Commands to list and revoke key
- Implement rate limiting per key
- Add some events for created, revoked, used key and a rate limit that had hit 

---

## 🧡 Credits

- Inspired by [Laravel Sanctum](https://laravel.com/docs/sanctum),  
  stripped to the essentials for user-free, machine-to-machine auth.
- Crafted by [McGo](https://github.com/McGo)

---

## 🪪 License

MIT © Mirko Haaser
