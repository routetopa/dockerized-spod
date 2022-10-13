<?php

namespace App\Notifications;

use \Illuminate\Auth\Notifications\ResetPassword as LaravelResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends LaravelResetPassword
{
	/**
	 * Build the mail representation of the notification.
	 *
	 * @param  mixed  $notifiable
	 * @return \Illuminate\Notifications\Messages\MailMessage
	 */
	public function toMail( $notifiable )
	{
		return (new MailMessage)
            ->subject( trans( 'notifications.reset.subject' ) )
			->line( trans( 'notifications.reset.line1' ) )
			->action( trans( 'notifications.reset.action_button' ) , url( 'password/reset', $this->token ) )
			->line( trans( 'notifications.reset.line2' ) );
	}
}
