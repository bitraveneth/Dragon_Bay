<?php

namespace App\Console\Commands;

use App\Services\ErpNotificationService;
use Illuminate\Console\Command;

class SyncErpNotifications extends Command
{
    protected $signature = 'erp:notifications:sync';

    protected $description = 'Generate and publish ERP system notifications';

    public function handle(ErpNotificationService $erpNotificationService): int
    {
        $alerts = $erpNotificationService->buildSystemAlerts();
        $erpNotificationService->publishSystemAlerts($alerts);

        $this->info('ERP notifications synced. Alerts published: ' . count($alerts));

        return self::SUCCESS;
    }
}
