# A-ERP Configuration Guide

This guide is for live/server configuration of the Laravel ERP project.

## 1) Environment

Set these values in `.env`:

```env
APP_NAME="A-ERP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_password

QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=file
```

Notes:
- `APP_NAME` controls brand text in header/title/footer where app config is used.
- Keep `APP_DEBUG=false` in production.

## 2) Build + Deploy Sequence

Run in project directory:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan down || true
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link || true
php artisan up
```

## 3) Scheduler (required for automated alerts)

Add server cron:

```cron
* * * * * cd /var/www/saferpv1 && php artisan schedule:run >> /dev/null 2>&1
```

Scheduler runs:
- `erp:notifications:sync` from `app/Console/Kernel.php`.

## 4) Queue Worker (recommended)

If notifications/jobs are queued, run worker via supervisor/systemd:

```bash
php artisan queue:work --tries=3 --timeout=120
```

## 5) Notifications Flow

1. ERP checks/builders create alerts (`ErpNotificationService`).
2. Alerts are stored in `notifications` table.
3. Header unread count is fetched via AJAX:
   - `GET /admin/notifications/header-data`
4. Mark read/unread actions are AJAX-enabled:
   - `POST /admin/notifications/{id}/mark-read`
   - `POST /admin/notifications/{id}/mark-unread`

## 6) Smoke Checks After Deploy

```bash
php artisan migrate:status
php artisan tinker --execute="echo config('app.name');"
php artisan tinker --execute="echo auth()->check() ? 'auth' : 'guest';" || true
```

In UI:
- Open `/admin/help` and verify System Configuration section appears.
- Open notification bell dropdown and verify unread count updates after mark-read.

## 7) Help Guide Screenshot Links

Place guide screenshots in these paths (inside `public`):

- Control overview:
  - `public/images/help/control/control-overview.png`
- Tax & VAT classes:
  - `public/images/help/tax/tax-vat-classes.png`
- Packaging types:
  - `public/images/help/products/packaging-types.png`
- Manufacturing overview:
  - `public/images/help/manufacturing/manufacturing-overview.png`
- Inventory overview:
  - `public/images/help/inventory/inventory-overview.png`
- Sales overview:
  - `public/images/help/sales/sales-overview.png`
- Employees overview:
  - `public/images/help/employees/employees-overview.png`
- Accounting overview:
  - `public/images/help/accounting/accounting-overview.png`
- CRM overview:
  - `public/images/help/crm/crm-overview.png`
- System overview:
  - `public/images/help/system/system-overview.png`

Public URL mapping:
- `/images/help/control/control-overview.png`
- `/images/help/tax/tax-vat-classes.png`
- `/images/help/products/packaging-types.png`
- `/images/help/manufacturing/manufacturing-overview.png`
- `/images/help/inventory/inventory-overview.png`
- `/images/help/sales/sales-overview.png`
- `/images/help/employees/employees-overview.png`
- `/images/help/accounting/accounting-overview.png`
- `/images/help/crm/crm-overview.png`
- `/images/help/system/system-overview.png`

## 8) ERP Process Reference

For full start-to-end process flow, see:

- `docs/system-cycle.md`
