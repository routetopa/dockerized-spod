<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sends a notification in order to verify that user's email is valid.
 *
 * @package App\Notifications
 */
class EmailVerification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
	    $link = route( 'email-verification.check', $notifiable->verification_token )
	            . '?email=' . urlencode( $notifiable->email );

	    return (new MailMessage)
            ->subject( trans( 'notifications.register.subject' ) )
		    ->line( trans( 'notifications.register.line1' ) )
		    ->action( trans( 'notifications.register.action_button' ), $link )
		    ->line( trans( 'notifications.register.line2' ) );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
