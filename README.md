<p align="center"><img src="/art/logo.png" alt="Laravel MultiEmail Logo"></p>

# Laravel MultiEmail

Allow users to have more than one email address related to their account.
Let them set their primary and recovery email addresses.

## Installation

```bash
composer require makidizajnerica/laravel-multiemail
```

As for registering Service Provider, it is not necessary,
Laravel will auto load provider using Package Discovery.

### Config

Inside `config/auth.php` add new `provider` like so:

```php
'providers' => [

    // Laravel's default provider
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],

    'emails' => [
        'driver' => 'eloquent.email',
        'models' => [
            'user' => App\Models\User::class,
            'email' => MakiDizajnerica\MultiEmail\Models\Email::class,
        ],
    ],

],
```

After that you need to edit existing or create new `guard`:

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'emails', // <- edit this line
    ],
],
```

Then go down under the `passwords` and edit it like so:

```php
'passwords' => [
    'users' => [
        'provider' => 'emails', // <- edit this line
        'table' => 'password_resets',
        'expire' => 60,
        'throttle' => 60,
    ],
],
```

The last step would be to change Laravel's default `Illuminate\Auth\Passwords\PasswordResetServiceProvider::class` inside `config/app.php` like this:

```php
'providers' => [

    /*
     * Laravel Framework Service Providers...
     */
    Illuminate\Queue\QueueServiceProvider::class,
    Illuminate\Redis\RedisServiceProvider::class,

    Illuminate\Auth\Passwords\PasswordResetServiceProvider::class, // <- remove this line
    MakiDizajnerica\MultiEmail\Providers\PasswordResetServiceProvider::class, // <- add this line

    Illuminate\Session\SessionServiceProvider::class,
    Illuminate\Translation\TranslationServiceProvider::class,
],
```

This part is important if you want your users to be able to reset their passwords.

### Publishing config

If your User model is not in default namespace you are going to need to publish `multiemail.php` config file using:

```bash
php artisan vendor:publish --tag=multiemail-config
```

And then change the model class inside `multiemail.php`:

```php
'user_model' => \Custom\Namespace\User::class,
```

### Migrations

First you are going to need to publish migrations using command:

```bash
php artisan vendor:publish --tag=multiemail-migrations
```

Then run them:

```bash
php artisan migrate
```

After running the migrations new table `emails` will be created.

**Please don't forget to remove `email` field from `users` table!**

## Usage

Go inside your `App\Models\User::class` and add `MakiDizajnerica\MultiEmail\HasMultipleEmails::class` trait and implement `MakiDizajnerica\MultiEmail\Contracts\HasMultipleEmails::class`:

```php
namespace App\Models;

use MakiDizajnerica\MultiEmail\HasMultipleEmails;
use Illuminate\Foundation\Auth\User as Authenticatable;
use MakiDizajnerica\MultiEmail\Contracts\HasMultipleEmails as HasMultipleEmailsContract;

class User extends Authenticatable implement HasMultipleEmailsContract
{
    use HasMultipleEmails;
    
    //
}
```

Then be sure to define `emails()` relation method:

```php
use MakiDizajnerica\MultiEmail\Models\Email;

public function emails() : HasMany
{
    return $this->hasMany(Email::class);
}
```

After that your `User::class` will have some methods available:

```php
/**
 * Add new email address.
 *
 * @param  array $email
 * @param  bool $sendVerification
 * @return \MakiDizajnerica\MultiEmail\Email
 */
public function addNewEmail(array $email, $sendVerification = true);

/**
 * Find email address.
 *
 * @param  mixed $email
 * @param  string $field
 * @return \MakiDizajnerica\MultiEmail\Email|null
 */
public function findMyEmail($email, $field = 'email');

/**
 * Determine if user is the owner of the provided email address.
 *
 * @param  string $email
 * @return bool
 */
public function isMyEmail($email);

/**
 * Remove all user's email addresses.
 *
 * @return void
 */
public function removeAllEmails();

/**
 * Check if the provided email address is verified.
 *
 * @param  string $email
 * @return bool
 */
public function isVerifiedEmail($email);

/**
 * Determine if provided email address is primary.
 *
 * @param  string $email
 * @return bool
 */
public function isPrimaryEmail($email);

/**
 * Change primary email address.
 *
 * @param  string $email
 * @return void
 */
public function setEmailAsPrimary($email);

/**
 * Determine if user has recovery email address.
 *
 * @return bool
 */
public function hasRecoveryEmail();

/**
 * Determine if provided email address is recovery.
 *
 * @param  string $email
 * @return bool
 */
public function isRecoveryEmail($email);

/**
 * Change recovery email address.
 *
 * @param  string $email
 * @return void
 */
public function setEmailAsRecovery($email);
```

And some custom attributes:

```php
// Get all user's verified emails
$user->verified_emails

// Get primary email
$user->email

// Get recovery email if it exists
$user->recovery_email
```

### Adding new email address

```php
use App\Models\User;

$user = User::first();

$email = $user->addNewEmail([
    'email' => 'test@mail.com'
]);
```

If user does not have primary email defined you can do something like this:

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'name' => 'Nick',
    'password' => Hash::make('password'),
]);

$email = $user->addNewEmail([
    'email' => 'test@mail.com',
    'type' => 'primary',
]);
```

Email verification notification will be sent every time new email is added. If you dont want to send notification you can pass second argument to the `addNewEmail()` method like so:

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'name' => 'Nick',
    'password' => Hash::make('password'),
]);

$email = $user->addNewEmail([
    'email' => 'test@mail.com',
    'type' => 'primary',
    'verified_at' => now(),
], false);
```

### Email types

User may only have one primary and one recovery email address, so it is recommended to use already defined methods for changing types of email addresses:

```php
use App\Models\User;

$user = User::first();

if ($user->isMyEmail('test@mail.com')) {

    // Set as primary
    $user->setEmailAsPrimary('test@mail.com');
    
    // Set as recovery
    $user->setEmailAsRecovery('test@mail.com');

}
```

**Email address cannot be primary and recovery at the same time!**

### Password resets

Defaut email address for password resets will be user's primary email. But if there is recovery email defined, user will be able to use that email address also. Laravel's default password reset service will still be usable as normal, to learn more about password resets visit <https://laravel.com/docs/8.x/passwords>.

Inside `multiemail.php` config file you will be able to enable/disable password resets and to specify if primary email should be used for those resets.

```php
'passwords' => [
    'allow_resets' => true,
    'reset_with_primary_email' => true,
],
```

## Author

**Nemanja Marijanovic** (<n.marijanovic@hotmail.com>) 

## Licence

Copyright © 2021, Nemanja Marijanovic <n.marijanovic@hotmail.com>

All rights reserved.

For the full copyright and license information, please view the LICENSE 
file that was distributed within the source root of this package.