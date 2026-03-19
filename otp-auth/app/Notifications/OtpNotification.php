<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The OTP code.
     */
    protected string $code;

    /**
     * The delivery channel.
     */
    protected string $channel;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $code, string $channel = 'email')
    {
        $this->code = $code;
        $this->channel = $channel;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $expiry = config('otp.code.expiry', 10);

        return (new MailMessage)
            ->subject(config('otp.email.subject', 'Your verification code'))
            ->greeting('Hello!')
            ->line('Your verification code is:')
            ->line("**{$this->code}**")
            ->line("This code will expire in {$expiry} minutes.")
            ->line('If you did not request this code, please ignore this email.')
            ->salutation('Thanks, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'code' => $this->code,
            'channel' => $this->channel,
        ];
    }
}
