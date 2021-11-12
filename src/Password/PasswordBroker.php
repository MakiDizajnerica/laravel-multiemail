<?php

namespace MakiDizajnerica\MultiEmail\Password;

use Closure;
use Illuminate\Support\Arr;
use UnexpectedValueException;
use Illuminate\Auth\Passwords\PasswordBroker as BasePasswordBroker;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class PasswordBroker extends BasePasswordBroker implements PasswordBrokerContract
{
    /**
     * Constant representing a reset request block.
     *
     * @var string
     */
    const RESET_BLOCKED = 'passwords.blocked';

    /**
     * Send a password reset link to a user.
     *
     * @param  array  $credentials
     * @param  \Closure|null  $callback
     * @return string
     */
    public function sendResetLink(array $credentials, Closure $callback = null)
    {
        if ($this->isPasswordResetBlocked()) {
            return static::RESET_BLOCKED;
        }

        return parent::sendResetLink($credentials, $callback);
    }

    /**
     * Reset the password for the given token.
     *
     * @param  array $credentials
     * @param  \Closure $callback
     * @return mixed
     */
    public function reset(array $credentials, Closure $callback)
    {
        if ($this->isPasswordResetBlocked()) {
            return static::RESET_BLOCKED;
        }

        $user = $this->validateReset($credentials);

        // If the responses from the validate method is not a user instance, we will
        // assume that it is a redirect and simply return it from this method and
        // the user is properly redirected having an error message on the post.
        if (! ($user instanceof CanResetPasswordContract)) {
            return $user;
        }

        $password = $credentials['password'];

        // We extract email address used for password reset and then unset
        // it so that user can be updated without exceptions.
        $email = $user->email;
        unset($user->email);

        // Once the reset has been validated, we'll call the given callback with the
        // new password. This gives the user an opportunity to store the password
        // in their persistent storage. Then we'll delete the token and return.
        $callback($user, $password);

        // Here we set email used for password reset back so that token can be deleted.
        $user->email = $email;

        $this->tokens->delete($user);

        return static::PASSWORD_RESET;
    }

    /**
     * Get the user for the given credentials.
     *
     * @param  array $credentials
     * @return \Illuminate\Contracts\Auth\CanResetPassword|null
     *
     * @throws \UnexpectedValueException
     */
    public function getUser(array $credentials)
    {
        $credentials = Arr::except($credentials, ['token']);

        // Get user instance either with primary or with recovery email.
        $user = $this->users->retrieveByCredentialsForPasswordReset($credentials);

        if ($user && ! ($user instanceof CanResetPasswordContract)) {
            throw new UnexpectedValueException('User must implement CanResetPassword interface.');
        }

        return $user;
    }

    /**
     * Determine if the password resets are blocked.
     *
     * @return bool
     */
    private function isPasswordResetBlocked()
    {
        return ! config('multiemail.passwords.allow_resets');
    }
}
