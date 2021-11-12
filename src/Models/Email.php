<?php

namespace MakiDizajnerica\MultiEmail\Models;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MakiDizajnerica\MultiEmail\Contracts\Email as EmailContract;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;

class Email extends Model implements MustVerifyEmailContract, EmailContract
{
    use MustVerifyEmail,
    Notifiable;

    protected $table = 'emails';
    protected $primaryKey = 'id';

    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'email',
        'type',
        'verified_at',
    ];

    protected $hidden = [
        //
    ];

    protected $appends = [
        //
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification $notification
     * @return array
     */
    public function routeNotificationForMail($notification)
    {
        return [$this->email];
    }



    /**
     * Check if Email is primary.
     *
     * @return bool
     */
    public function isPrimary()
    {
        return $this->type == 'primary';
    }

    /**
     * Check if Email is recovery.
     *
     * @return bool
     */
    public function isRecovery()
    {
        return $this->type == 'recovery';
    }

    /**
     * Set Email as primary.
     *
     * @return bool
     */
    public function setAsPrimary()
    {
        return $this->forceFill(['type' => 'primary'])->save();
    }

    /**
     * Set Email as recovery.
     *
     * @return bool
     */
    public function setAsRecovery()
    {
        return $this->forceFill(['type' => 'recovery'])->save();
    }

    /**
     * Remove Email type.
     *
     * @return bool
     */
    public function removeType()
    {
        return $this->forceFill(['type' => null])->save();
    }



    /**
     * Get the user that owns the Email.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(config(
            'multiemail.user_model',
            App\Models\User::class
        ));
    }
}
