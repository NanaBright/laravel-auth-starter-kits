<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class MagicLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $token,
        public Carbon $expiresAt
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->generateMagicLinkUrl($notifiable->email, $this->token);
        $expiresInMinutes = $this->expiresAt->diffInMinutes(now());

        return (new MailMessage)
            ->subject('Your Magic Link for ' . config('app.name'))
            ->greeting('Hello!')
            ->line('You are receiving this email because we received a magic link request for your account.')
            ->action('Sign In', $url)
            ->line('This magic link will expire in ' . $expiresInMinutes . ' minutes.')
            ->line('If you did not request a magic link, no further action is required.')
            ->salutation('Regards, ' . config('app.name'));
    }

    /**
     * Generate the magic link URL.
     */
    private function generateMagicLinkUrl(string $email, string $token): string
    {
        return config('app.url') . '/auth/magic-link/verify?' . http_build_query([
            'email' => $email,
            'token' => $token,
        ]);
    }
}
