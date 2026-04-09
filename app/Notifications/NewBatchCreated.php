<?php

namespace App\Notifications;

use App\Models\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBatchCreated extends Notification
{
    use Queueable;

    public Batch $batch;

    /**
     * Create a new notification instance.
     */
    public function __construct(Batch $batch)
    {
        $this->batch = $batch;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // For now we only store notifications in the database.
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $product = $this->batch->product;

        return [
            'title' => 'New batch recorded',
            'message' => sprintf(
                'Batch %s recorded for %s.',
                $this->batch->batch_code,
                $product?->name ?? 'unknown product'
            ),
            'batch_id' => $this->batch->id,
            'batch_code' => $this->batch->batch_code,
            'product_id' => $this->batch->product_id,
            'product_name' => $product?->name,
            'production_date' => $this->batch->production_date,
            'created_by' => auth()->id(),
        ];
    }
}

