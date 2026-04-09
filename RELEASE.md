# Release Guide

## Versioning

This ERP now reads its release string from `APP_VERSION`.

Example:

```env
APP_VERSION=1.1.0
```

The version is displayed in the admin footer via `config('app.version')`.

## Release Steps

1. Set the target version in `.env` on the release environment.
2. Record the release notes in `CHANGELOG.md` under a tagged section like `## [1.1.0] - 2026-04-07`.
3. Run the verification suite you want to gate the release on.
4. Build frontend assets with `npm run build`.
5. Clear and rebuild Laravel caches on the target environment.
6. Tag the release in git, for example `v1.1.0`.

## Recommended Verification

```bash
php artisan test --filter='test_goods_receipt_posts_stock_entry_and_goods_receipt_movement|test_production_confirm_posts_consumption_and_output_movements|test_order_reservation_and_delivery_post_stock_movements|test_customer_return_reuses_batched_available_entry_and_logs_movement|test_stock_transfer_requires_destination_rules_and_preserves_location'
```

## Deployment Notes

- Ensure the target environment has the correct `APP_VERSION`.
- Run database migrations before making the release live if schema changes are included.
- Rebuild cached config after updating `.env`:

```bash
php artisan optimize:clear
php artisan config:cache
```
