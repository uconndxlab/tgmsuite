<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvitationNotification extends Notification
{
    use Queueable;

    public string $token;
    public string $email;
    public bool $isNewUser;

    public function __construct(string $token, string $email, bool $isNewUser = true)
    {
        $this->token = $token;
        $this->email = $email;
        $this->isNewUser = $isNewUser;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $this->email,
        ]);

        if ($this->isNewUser) {
            return (new MailMessage)
                ->subject('Welcome to TGM Suite - Activate Your Account')
                ->greeting('Hello ' . ($notifiable->name ?? '') . '!')
                ->line('An administrator has created an account for you on the Athletic Field Assessment Tool (TGM Suite).')
                ->line('Please click the button below to set your password and activate your account:')
                ->action('Set Password & Activate Account', $url)
                ->line('This activation link will expire in 60 minutes.')
                ->line('If you were not expecting this invitation, no further action is required.');
        }

        return (new MailMessage)
            ->subject('Reset Your TGM Suite Password')
            ->greeting('Hello ' . ($notifiable->name ?? '') . '!')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $url)
            ->line('This password reset link will expire in 60 minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
