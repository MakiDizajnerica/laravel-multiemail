<?php

namespace MakiDizajnerica\MultiEmail\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface Email
{
    /**
     * Get the user that owns the Email.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() : BelongsTo;

    /**
     * Check if Email is primary.
     *
     * @return bool
     */
    public function isPrimary();

    /**
     * Check if Email is recovery.
     *
     * @return bool
     */
    public function isRecovery();

    /**
     * Set Email as primary.
     *
     * @return bool
     */
    public function setAsPrimary();

    /**
     * Set Email as recovery.
     *
     * @return bool
     */
    public function setAsRecovery();

    /**
     * Remove Email type.
     *
     * @return bool
     */
    public function removeType();
}
