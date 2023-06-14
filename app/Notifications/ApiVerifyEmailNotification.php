<?php
namespace App\Notifications;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class ApiVerifyEmailNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Email Address')
            ->line('Please click the button below to verify your email address.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('If you did not create an account, no further action is required.');
    }

    /**
     * Get the email verification URL.
     *
     * @param mixed $notifiable
     * @return string
     */
    protected function verificationUrl(mixed $notifiable): string
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Update the base URL to your API endpoint
        $apiBaseUrl = config('app.url');

        return $apiBaseUrl . '/api/email/verify/' . $notifiable->getKey() . '/' . sha1($notifiable->getEmailForVerification()) . '?expires=' . now()->addMinutes(config('auth.verification.expire', 60))->timestamp . '&signature=' . sha1($verificationUrl);
    }
}
