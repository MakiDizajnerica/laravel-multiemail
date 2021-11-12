<?php

namespace MakiDizajnerica\MultiEmail\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface HasMultipleEmails
{
    /**
     * Get all of the emails for the User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function emails() : HasMany;

    /**
     * Add new email address.
     *
     * @param  array $email
     * @param  bool $sendVerification
     * @return \MakiDizajnerica\MultiEmail\Contracts\Email
     */
    public function addNewEmail(array $email, $sendVerification = true);

    /**
     * Find email address.
     *
     * @param  mixed $email
     * @param  string $field
     * @return \MakiDizajnerica\MultiEmail\Contracts\Email|null
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

    /**
     * Get the "verified_emails" attribute.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVerifiedEmailsAttribute();

    /**
     * Get the "email" attribute.
     * 
     * @param  string|null $email
     * @return string
     */
    public function getEmailAttribute($email);

    /**
     * Get the "recovery_email" attribute.
     * 
     * @return string|null
     */
    public function getRecoveryEmailAttribute();
}
