<?php

namespace MakiDizajnerica\MultiEmail;

use Illuminate\Support\Str;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class EloquentEmailProvider extends EloquentUserProvider
{
    protected $emailModel;

    /**
     * Create a new eloquent user provider.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param  array $models
     * @return void
     */
    public function __construct(HasherContract $hasher, array $models)
    {
        parent::__construct($hasher, $models['user']);

        $this->emailModel = $models['email'];
    }

    /**
     * Retrieve a email by the given credentials.
     *
     * @param  array $credentials
     * @return \MakiDizajnerica\MultiEmail\Contracts\Email|null
     */
    private function retrieveByEmailCredentials(array $credentials)
    {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
            Str::contains($this->firstCredentialKey($credentials), 'password'))) {
            return;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a email, return it in a
        // Eloquent Email "model".
        $query = $this->newEmailModelQuery()->with('user');

        foreach ($credentials as $key => $value) {
            if (Str::contains($key, 'password')) {
                continue;
            }

            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (is_null($email = $this->retrieveByEmailCredentials($credentials))) {
            return;
        }

        // Retrieve email instance and check if it primary then return user that
        // own that email address.
        if ($email->isPrimary()) {
            return $email->user;
        }
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentialsForPasswordReset(array $credentials)
    {
        if (is_null($email = $this->retrieveByEmailCredentials($credentials))) {
            return;
        }

        if (config('multiemail.passwords.reset_with_primary_email') &&
            $email->isPrimary()) {
            // Retrieve email instance and check if it primary or recovery then return
            // user that own that email address.
            return $email->user;
        }
        elseif ($email->isRecovery()) {
            // If the email is recovery we are going to override the defaul primary
            // address by setting users "email" attribute to be recovery email.
            $user = $email->user;
            $user->email = $email->email;
    
            return $user;
        }
    }

    /**
     * Get a new query builder for the model instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newEmailModelQuery($emailModel = null)
    {
        return is_null($emailModel)
            ? $this->createEmailModel()->newQuery()
            : $emailModel->newQuery();
    }

    /**
     * Create a new instance of the model.
     *
     * @return \MakiDizajnerica\MultiEmail\Contracts\Email
     */
    public function createEmailModel()
    {
        $class = '\\' . ltrim($this->emailModel, '\\');

        return new $class;
    }

    /**
     * Gets the name of the Eloquent email model.
     *
     * @return string
     */
    public function getEmailModel()
    {
        return $this->emailModel;
    }

    /**
     * Sets the name of the Eloquent email model.
     *
     * @param  string $model
     * @return $this
     */
    public function setEmailModel($emailModel)
    {
        $this->emailModel = $emailModel;

        return $this;
    }
}
