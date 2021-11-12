<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | Define user model that will be used for emails relation.
    | If you are writing your own email model you won't need this option, because
    | you will define user relation inside your email model class.
    |
    */

    'user_model' => App\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Password Resets
    |--------------------------------------------------------------------------
    |
    | Here you may configure additional settings around password resets.
    |
    | If "allow_resets" is set to false, every time user tries to reset their
    | password they will get "passwords.blocked" status as respone.
    |
    | "reset_with_primary_email" property defines if users will be able to request
    | password reset using their primary email address, if set to false only
    | recovery email address will be used for resets. Be sure to remind users to
    | set their recovery emails if this option is set to false.
    |
    */

    'passwords' => [
        'allow_resets' => true,
        'reset_with_primary_email' => true,
    ],
];
