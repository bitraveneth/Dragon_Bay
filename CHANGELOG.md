# Changelog

All notable ERP changes should be recorded here.

## [Unreleased]

## [1.1.0] - 2026-04-07

### Inventory
- Added consistent `stock_movements` logging for goods receipts, production confirmation, stock reservations, deliveries, customer returns, and transfers.
- Fixed customer returns so they reuse a compatible batch-linked available stock entry when possible instead of creating a stray unbatched balance row.
- Improved stock movement and batch timeline UI so newer movement types render with readable labels and correct inbound/outbound summaries.

### Operations
- Added or exposed a clearer Stock Movements entry in the inventory menu fallback structure.
- Improved transfer notes to show warehouse and location names instead of raw IDs.
- Added GRN reversal handling that preserves movement history instead of silently deleting stock history.

### Verification
- Added regression coverage for GRN posting, production posting, reservation and delivery movement creation, customer return batch reuse, and transfer movement logging.
