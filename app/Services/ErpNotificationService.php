<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\NotificationDispatchLog;
use App\Models\Order;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ErpNotificationService
{
    public function buildSystemAlerts(): array
    {
        $alerts = [];

        if (Schema::hasTable('batches')) {
            $expiringSoonCount = Batch::whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->copy()->addDays(30)])
                ->count();

            if ($expiringSoonCount > 0) {
                $alerts[] = [
                    'key' => 'expiring_batches_' . Carbon::today()->toDateString() . '_' . $expiringSoonCount,
                    'message' => "{$expiringSoonCount} batches expiring within 30 days",
                    'variant' => 'error',
                    'source' => 'Inventory',
                    'title' => 'Inventory alert',
                    'context' => ['expiring_batches' => $expiringSoonCount],
                ];
            }
        }

        if (Schema::hasTable('deliveries')) {
            $exceptionDeliveriesToday = Delivery::where('status', 'exception')
                ->whereDate('updated_at', Carbon::today())
                ->count();

            if ($exceptionDeliveriesToday > 0) {
                $alerts[] = [
                    'key' => 'delivery_exceptions_' . Carbon::today()->toDateString() . '_' . $exceptionDeliveriesToday,
                    'message' => "{$exceptionDeliveriesToday} deliveries marked as exception today",
                    'variant' => 'error',
                    'source' => 'Delivery',
                    'title' => 'Delivery alert',
                    'context' => ['delivery_exceptions_today' => $exceptionDeliveriesToday],
                ];
            }
        }

        if (Schema::hasTable('orders')) {
            $todayOrders = Order::whereDate('delivery_date', Carbon::today())->count();

            if ($todayOrders > 0) {
                $alerts[] = [
                    'key' => 'orders_due_' . Carbon::today()->toDateString() . '_' . $todayOrders,
                    'message' => "{$todayOrders} orders scheduled for delivery today",
                    'variant' => 'success',
                    'source' => 'Sales',
                    'title' => 'Sales update',
                    'context' => ['orders_due_today' => $todayOrders],
                ];
            }
        }

        if (Schema::hasTable('invoices') && Schema::hasTable('receipts')) {
            $openInvoices = Invoice::with(['receipts', 'creditNotes', 'advanceApplications'])
                ->whereIn('status', ['issued', 'adjusted'])
                ->get();

            $outstandingReceivables = $openInvoices->sum(function (Invoice $invoice) {
                return (float) $invoice->outstanding;
            });

            if ($outstandingReceivables > 0) {
                $formatted = number_format($outstandingReceivables, 2);
                $alerts[] = [
                    'key' => 'receivables_' . Carbon::today()->toDateString() . '_' . number_format($outstandingReceivables, 2, '.', ''),
                    'message' => 'Outstanding receivables of BDT ' . $formatted,
                    'variant' => 'error',
                    'source' => 'Finance',
                    'title' => 'Finance alert',
                    'context' => ['outstanding_receivables' => $outstandingReceivables],
                ];
            }
        }

        return $alerts;
    }

    public function publishSystemAlerts(array $alerts, array $roles = ['super_admin', 'admin']): void
    {
        if (empty($alerts) || !Schema::hasTable('notifications')) {
            return;
        }

        $recipientQuery = User::query();

        if (Schema::hasTable('user_roles')) {
            $recipientQuery->with('userRoles');
        }

        $recipients = $recipientQuery
            ->get()
            ->filter(fn (User $user) => $user->hasAnyRole($roles))
            ->values();

        foreach ($recipients as $user) {
            foreach ($alerts as $alert) {
                $dedupeKey = (string) ($alert['key'] ?? '');
                if ($dedupeKey === '') {
                    continue;
                }

                $source = $alert['source'] ?? 'System';
                $alert['title'] = $alert['title'] ?? ($source . ' alert');
                $alert['sender_name'] = $alert['sender_name'] ?? $source;

                $dispatchLog = NotificationDispatchLog::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'dedupe_key' => $dedupeKey,
                    ],
                    [
                        'sent_at' => now(),
                    ]
                );

                if (!$dispatchLog->wasRecentlyCreated) {
                    continue;
                }

                $user->notify(new SystemAlertNotification($alert));
            }
        }
    }

    public function unreadSystemNotificationsForUser(User $user, int $limit = 10): Collection
    {
        return $user->unreadNotifications()
            ->where('type', SystemAlertNotification::class)
            ->latest()
            ->take($limit)
            ->get();
    }
}
