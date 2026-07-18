<?php

namespace Modules\Identity\App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Modules\Identity\App\Models\Invitation;

final class InvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Invitation $invitation,
        private readonly string $plainToken,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'invitations.accept',
            $this->invitation->expires_at,
            ['invitation' => $this->invitation->getKey(), 'token' => $this->plainToken]
        );

        return (new MailMessage)
            ->subject('دعوة للانضمام إلى مساحة العمل')
            ->greeting('مرحباً')
            ->line('تمت دعوتك للانضمام إلى '.$this->invitation->tenant->name.' بدور مدير.')
            ->action('قبول الدعوة', $url)
            ->line('تنتهي هذه الدعوة في '.$this->invitation->expires_at->format('Y-m-d H:i'));
    }
}
