<?php

return [
    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    'reset_password' => [
        'subject' => 'Reset Your Password',
        'greeting' => 'Hello!',
        'line_1' => 'You are receiving this email because we received a password reset request for your account.',
        'action' => 'Reset Password',
        'line_2' => 'This password reset link will expire in :count minutes.',
        'line_3' => 'If you did not request a password reset, no further action is required.',
        'salutation' => 'Regards, Laravel Team',
        'reset_password_send' => 'A password reset link has been sent to your email!',
    ],
    'verify_email' => [
        'subject' => 'Verify Your Email Address',
        'greeting' => 'Hello!',
        'line_1' => 'Please click the button below to verify your email address.',
        'action' => 'Verify Email Address',
        'line_2' => 'If you did not create an account, no further action is required.',
        'salutation' => 'Regards',
        'fresh_resend' => 'A fresh verification link has been sent to your email address.',
        'before_proceeding' => 'Before proceeding, please check your email for a verification link.',
        'you_didnt_receive' => 'If you did not receive the email you can request another verification email',
        'resend' => 'click here to request another.',
    ],
    'verification_sent' => 'A fresh verification link has been sent to your email.',
];
