<?php

namespace MakiDizajnerica\MultiEmail;

trait HasMultipleEmails
{
    /**
     * Add new email address.
     *
     * @param  array $email
     * @param  bool $sendVerification
     * @return \MakiDizajnerica\MultiEmail\Contracts\Email
     */
    public function addNewEmail(array $email, $sendVerification = true)
    {
        if (isset($email['type'])) {
            if (($email['type'] == 'primary' && ! is_null($this->email)) ||
                ($email['type'] == 'recovery' && $this->hasRecoveryEmail())) {
                unset($email['type']);
            }
        }
        elseif (! is_null($this->email)) {
            $email['type'] = 'primary';
        }

        return tap($this->emails()->create($email), function ($email) use ($sendVerification) {
            if ($sendVerification) {
                $email->sendEmailVerificationNotification();
            }
        });
    }

    /**
     * Find email address.
     *
     * @param  mixed $email
     * @param  string $field
     * @return \MakiDizajnerica\MultiEmail\Contracts\Email|null
     */
    public function findMyEmail($email, $field = 'email')
    {
        return $this->emails->firstWhere($field, $email);
    }

    /**
     * Determine if user is the owner of the provided email address.
     *
     * @param  string $email
     * @return bool
     */
    public function isMyEmail($email)
    {
        return $this->emails->pluck('email')->contains($email);
    }

    /**
     * Remove all user's email addresses.
     *
     * @return void
     */
    public function removeAllEmails()
    {
        $this->emails()->delete();
    }

    /**
     * Get the user's primary email address.
     *
     * @return \MakiDizajnerica\MultiEmail\Contracts\Email
     */
    private function primaryEmail()
    {
        return $this->findMyEmail('primary', 'type');
    }

    /**
     * Get the user's recovery email address.
     *
     * @return \MakiDizajnerica\MultiEmail\Contracts\Email|null
     */
    private function recoveryEmail()
    {
        return $this->findMyEmail('recovery', 'type');
    }

    /**
     * Check if the provided email address is verified.
     *
     * @param  string $email
     * @return bool
     */
    public function isVerifiedEmail($email)
    {
        return $this->verified_emails
            ->pluck('email')
            ->contains($email);
    }

    /**
     * Determine if provided email address is primary.
     *
     * @param  string $email
     * @return bool
     */
    public function isPrimaryEmail($email)
    {
        return $this->email == $email;
    }

    /**
     * Change primary email address.
     *
     * @param  string $email
     * @return void
     */
    public function setEmailAsPrimary($email)
    {
        if (! $this->isPrimaryEmail($email)) {
            $this->primaryEmail()->removeType();
            $this->findMyEmail($email)->setAsPrimary();
        }
    }

    /**
     * Determine if user has recovery email address.
     *
     * @return bool
     */
    public function hasRecoveryEmail()
    {
        return ! is_null($this->recovery_email);
    }

    /**
     * Determine if provided email address is recovery.
     *
     * @param  string $email
     * @return bool
     */
    public function isRecoveryEmail($email)
    {
        return $this->recovery_email == $email;
    }

    /**
     * Change recovery email address.
     *
     * @param  string $email
     * @return void
     */
    public function setEmailAsRecovery($email)
    {
        if (! $this->isRecoveryEmail($email)) {
            optional($this->recoveryEmail())->removeType();
            $this->findMyEmail($email)->setAsRecovery();
        }
    }



    /**
     * Get the "verified_emails" attribute.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVerifiedEmailsAttribute()
    {
        return $this->emails->whereNotNull('verified_at');
    }

    /**
     * Get the "email" attribute.
     * 
     * @param  string|null $email
     * @return string
     */
    public function getEmailAttribute($email)
    {
        if ($email) {
            return $email;
        }

        return optional($this->emails->firstWhere('type', 'primary'))->email;
    }

    /**
     * Get the "recovery_email" attribute.
     * 
     * @return string|null
     */
    public function getRecoveryEmailAttribute()
    {
        return optional($this->emails->firstWhere('type', 'recovery'))->email;
    }
}
