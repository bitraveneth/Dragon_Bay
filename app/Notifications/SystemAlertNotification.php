<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly array $payload
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'category' => 'system',
            'title' => $this->payload['title'] ?? 'System alert',
            'message' => $this->payload['message'] ?? '',
            'type' => $this->payload['variant'] ?? 'info',
            'source' => $this->payload['source'] ?? 'System',
            'sender_name' => $this->payload['sender_name'] ?? ($this->payload['source'] ?? 'System'),
            'link' => $this->payload['link'] ?? route('admin.notifications.index'),
            'dedupe_key' => $this->payload['key'] ?? null,
            'context' => $this->payload['context'] ?? [],
            'created_by' => auth()->id(),
        ];
    }
}
