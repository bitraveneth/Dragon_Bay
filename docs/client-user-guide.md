# A-ERP Client Manual (Detailed Table Version)

This guide is for business users, not developers.
All sections are written in simple operational language.

---

## 1) What A-ERP does

| Area | What the system handles | Final outcome |
|---|---|---|
| Procurement | Purchase Order, GRN, Supplier Bill | Materials available and payable tracked |
| Production | BOM, Batch, Production Run, QC, Stock Confirm | Finished goods added to stock |
| Sales | Agent order, pricing, stock reservation | Confirmed order ready for dispatch |
| Delivery | Route, vehicle, POD, exception handling | Delivery truth captured |
| Finance | Invoice, VAT, withholding, receipt, credit note | Receivable and ledger updated |
| Inventory | Transfers, write-off, stock audit | Stock accuracy and traceability |

---

## 2) User roles and who does what

| Role | Main responsibility | Must check daily |
|---|---|---|
| Purchase Executive | Create PO, post GRN, supplier bill follow-up | Pending approvals, partial receipts |
| Warehouse Officer | Receive stock, transfer, write-off, audit | Low stock, damaged stock, variance |
| Production Officer | Create runs, update batch, coordinate QC | Pending QC and stock confirmation |
| Sales Officer | Create agent orders, coordinate dispatch | Unconfirmed orders, stock issues |
| Delivery Coordinator | Assign route/vehicle, update POD | In-transit and exception deliveries |
| Accounts Officer | Invoice, receipts, credit notes, expenses | Overdue receivables, unreconciled items |
| Admin/Super Admin | Permissions, setup, policy control | Notification health, master data quality |

---

## 3) Daily operating routine (start to end)

| Sequence | Task | Done by | Screen |
|---|---|---|---|
| 1 | Check system alerts and pending actions | All leads | `/admin` <a href="/admin" target="_blank" rel="noopener">↗</a> |
| 2 | Review PO approvals and GRN backlog | Purchase | `/admin/purchase-orders` <a href="/admin/purchase-orders" target="_blank" rel="noopener">↗</a> |
| 3 | Process material receiving and QC lines | Warehouse | `/admin/goods-receipts` <a href="/admin/goods-receipts" target="_blank" rel="noopener">↗</a> |
| 4 | Review production pending QC/confirm stock | Production | `/admin/production` <a href="/admin/production" target="_blank" rel="noopener">↗</a> |
| 5 | Process new sales orders | Sales | `/admin/orders` <a href="/admin/orders" target="_blank" rel="noopener">↗</a> |
| 6 | Schedule dispatch and update POD | Delivery | `/admin/deliveries` <a href="/admin/deliveries" target="_blank" rel="noopener">↗</a> |
| 7 | Validate invoice and post receipts | Accounts | `/admin/finance` <a href="/admin/finance" target="_blank" rel="noopener">↗</a> |
| 8 | End-day stock and receivable review | Warehouse + Accounts | `/admin/stock/audit`, `/admin/finance` |

---

## 4) Master data setup (one-time + maintenance)

## 4.1 Critical setup order

| Priority | Setup item | Why this is first | Screen |
|---|---|---|---|
| 1 | Materials | Needed for BOM and costing | `/admin/materials/create` <a href="/admin/materials/create" target="_blank" rel="noopener">↗</a> |
| 2 | Products | Needed for sales and production output | `/admin/products/create` <a href="/admin/products/create" target="_blank" rel="noopener">↗</a> |
| 3 | Packaging types | Needed for packing/picking labels | `/admin/packaging` <a href="/admin/packaging" target="_blank" rel="noopener">↗</a> |
| 4 | Tax classes | Needed for invoice VAT calculation | `/admin/tax-classes` <a href="/admin/tax-classes" target="_blank" rel="noopener">↗</a> |
| 5 | Suppliers | Needed for PO and bill | `/admin/suppliers/create` <a href="/admin/suppliers/create" target="_blank" rel="noopener">↗</a> |
| 6 | Agents | Needed for sales order and pricing | `/admin/agents/create` <a href="/admin/agents/create" target="_blank" rel="noopener">↗</a> |
| 7 | Warehouses + bins | Needed for stock posting | `/admin/warehouses/create`, `/admin/warehouse-locations` |
| 8 | Vehicles + routes | Needed for dispatch planning | `/admin/vehicles/create`, `/admin/delivery-routes` |

## 4.2 Material screen detailed fields

Screen: `/admin/materials/create` <a href="/admin/materials/create" target="_blank" rel="noopener">↗</a>

| Field label on screen | Required | Business meaning | Example |
|---|---|---|---|
| Material Name | Yes | Material/service name used in BOM or costing | PET Resin |
| Material Type | Yes | Category of material usage | Raw / Service / In-house |
| Description | No | Internal notes for team | Food-grade bottle material |
| Unit of Measure (UOM) | No | Counting/usage unit | Piece, Liter, Carton |
| Material Code (SKU) | Yes | Unique material code | RM-PET-001 |
| Standard Cost per Unit | No | Default cost per unit | 12.50 |
| Supplier Name | No | Preferred supplier text | ABC Packaging Ltd |
| Active | No | If OFF, hidden from future selections | ON |

## 4.3 Product screen minimum quality standard

Screen: `/admin/products/create` <a href="/admin/products/create" target="_blank" rel="noopener">↗</a>

| Field group | Minimum required | Recommended |
|---|---|---|
| Identity | Name, SKU, Base Price | Add size and volume |
| Tax | Tax class | Add HSN/local code via tax class |
| Packaging | Packaging type | Add conversion setup |
| Traceability | Barcode/QR | Add image and certifications |

---

## 5) Procurement flow (step by step)

## 5.1 Purchase Order (PO)

| Step | What to enter | Rule | Screen |
|---|---|---|---|
| 1 | Supplier | Must be existing supplier | `/admin/purchase-orders/create` <a href="/admin/purchase-orders/create" target="_blank" rel="noopener">↗</a> |
| 2 | Order date + expected date | Expected date should be same or after order date | same |
| 3 | Add item lines | Description + quantity required | same |
| 4 | Save | Status becomes Draft | same |
| 5 | Approve | Only approved PO should move to GRN | `/admin/purchase-orders` <a href="/admin/purchase-orders" target="_blank" rel="noopener">↗</a> |

## 5.2 GRN posting

| Step | What to enter | System behavior | Screen |
|---|---|---|---|
| 1 | Supplier + Warehouse | Required | `/admin/goods-receipts/create` <a href="/admin/goods-receipts/create" target="_blank" rel="noopener">↗</a> |
| 2 | Optional PO link | Helps PO receive tracking | same |
| 3 | Item qty + unit cost + QC status | Required qty, status per line | same |
| 4 | Save GRN | Only Approved QC lines add stock | same |

## 5.3 Purchase Bill and payment

| Step | Action | Output | Screen |
|---|---|---|---|
| 1 | Create supplier bill with lines | AP liability created | `/admin/bills/create` <a href="/admin/bills/create" target="_blank" rel="noopener">↗</a> |
| 2 | Post payment (partial/full) | Bill status open -> part_paid -> paid | `/admin/bills` <a href="/admin/bills" target="_blank" rel="noopener">↗</a> |

---

## 6) Production flow (BOM to stock)

## 6.1 BOM setup

| Step | Action | Why needed | Screen |
|---|---|---|---|
| 1 | Select finished product | Define output item | `/admin/boms/create` <a href="/admin/boms/create" target="_blank" rel="noopener">↗</a> |
| 2 | Add component lines | Define material requirement | same |
| 3 | Set active BOM | Used in production consumption logic | same |

## 6.2 Batch and production run

| Step | Action | Output | Screen |
|---|---|---|---|
| 1 | Create/maintain batch | Batch code + expiry traceability | `/admin/batches` <a href="/admin/batches" target="_blank" rel="noopener">↗</a> |
| 2 | Create production run | Planned run recorded | `/admin/production/create` <a href="/admin/production/create" target="_blank" rel="noopener">↗</a> |
| 3 | QC approval | Run can move to stock confirm | `/admin/production` <a href="/admin/production" target="_blank" rel="noopener">↗</a> |
| 4 | Confirm stock | FG stock posted and run completed | same |

---

## 7) Sales and delivery flow

## 7.1 Sales order

| Step | User action | System check | Screen |
|---|---|---|---|
| 1 | Select agent | Agent exists and active | `/admin/orders/create` <a href="/admin/orders/create" target="_blank" rel="noopener">↗</a> |
| 2 | Add products and qty | Product and qty valid | same |
| 3 | Save order | Price list can override item price | same |
| 4 | Stock reserve | System reserves available stock | auto |

## 7.2 Delivery and POD

| Step | User action | Result | Screen |
|---|---|---|---|
| 1 | Create delivery | Route/vehicle can be assigned | `/admin/deliveries/create` <a href="/admin/deliveries/create" target="_blank" rel="noopener">↗</a> |
| 2 | Update POD | Receiver, photo, qty delivered/short/damaged | `/admin/deliveries` <a href="/admin/deliveries" target="_blank" rel="noopener">↗</a> |
| 3 | Mark delivered | Order becomes delivered; reserved stock consumed | same |

## 7.3 Delivery exception handling

| Exception type | What to record | Where |
|---|---|---|
| Short delivery | Delivered qty and short qty | Delivery edit form |
| Damage | Damaged qty and note | Delivery edit form |
| Reject | Exception note + final qty | Delivery edit form |

---

## 8) Inventory control details

## 8.1 Stock transfer

| Input | Rule | Screen |
|---|---|---|
| Source stock entry | Must exist and available | `/admin/stock/transfers` <a href="/admin/stock/transfers" target="_blank" rel="noopener">↗</a> |
| Destination warehouse | Required | same |
| Transfer quantity | Cannot exceed available qty | same |

## 8.2 Stock write-off

| Input | Rule | Screen |
|---|---|---|
| Stock entry | Must exist | `/admin/stock/write-off` <a href="/admin/stock/write-off" target="_blank" rel="noopener">↗</a> |
| Quantity | Cannot exceed available qty | same |
| Reason | Required (expired/wasted/etc.) | same |

## 8.3 Stock audit

| Input | System calculation | Screen |
|---|---|---|
| Warehouse + product (+ optional batch) | Finds system quantity | `/admin/stock/audit` <a href="/admin/stock/audit" target="_blank" rel="noopener">↗</a> |
| Counted quantity | Variance = counted - system | same |

---

## 9) Finance flow details

## 9.1 Invoice

| Rule | Meaning | Screen |
|---|---|---|
| Created from delivered order | No delivered order = no invoice | `/admin/finance` <a href="/admin/finance" target="_blank" rel="noopener">↗</a> |
| VAT from product tax class | Correct tax class is important | same |
| Withholding by agent rate | Auto applied if agent has rate | same |

## 9.2 Receipt posting

| Field | Rule | Screen |
|---|---|---|
| Amount | Required and > 0 | `/admin/finance` <a href="/admin/finance" target="_blank" rel="noopener">↗</a> |
| Payment method | Optional | same |
| Received date | Optional (defaults to today) | same |

## 9.3 Credit note

| Rule | Meaning | Screen |
|---|---|---|
| Credit amount limit | Cannot exceed remaining invoice value | `/admin/finance` <a href="/admin/finance" target="_blank" rel="noopener">↗</a> |
| Use case | Return/adjustment | same |

## 9.4 Supporting accounting screens

| Screen | Use |
|---|---|
| `/admin/accounts/create` <a href="/admin/accounts/create" target="_blank" rel="noopener">↗</a> | Add chart of accounts |
| `/admin/expenses/create` <a href="/admin/expenses/create" target="_blank" rel="noopener">↗</a> | Record expenses |

---

## 10) Calculation summary (simple examples)

## 10.1 Sales order amount

| Item | Qty | Unit price | Line total |
|---|---:|---:|---:|
| 1L Bottle | 100 | 45.00 | 4500.00 |
| 500ml Bottle | 200 | 25.00 | 5000.00 |
| **Order total** |  |  | **9500.00** |

Formula: `line total = qty x unit price` and `order total = sum of line totals`.

## 10.2 VAT example

| Net amount | VAT rate | VAT amount | Gross |
|---:|---:|---:|---:|
| 10,000.00 | 15% | 1,500.00 | 11,500.00 |

## 10.3 Withholding example

| Gross invoice | Agent withholding rate | Withholding | Expected cash collection |
|---:|---:|---:|---:|
| 11,500.00 | 3% | 345.00 | 11,155.00 |

## 10.4 Outstanding formula

| Formula | Meaning |
|---|---|
| `(Net + VAT - Withholding) - Receipts` | Current receivable |

---

## 11) Status reference

## 11.1 Order statuses

| Status | Meaning | Next normal step |
|---|---|---|
| Draft | Created only | Confirm |
| Confirmed | Accepted | Pick/Pack |
| Picked | Stock picked | Pack |
| Packed | Ready for dispatch | Dispatch |
| Dispatched | Out for delivery | Deliver |
| Delivered | Completed | Invoice/collection |

## 11.2 Delivery statuses

| Status | Meaning |
|---|---|
| Scheduled | Planned |
| In transit | On the road |
| Delivered | Finished with POD |
| Exception | Issue logged |

## 11.3 Purchase Order statuses

| Status | Meaning |
|---|---|
| Draft | Created |
| Approved | Ready for receive |
| Partial received | Some lines received |
| Received | Fully received |

---

## 12) Notifications

| What user sees | What it means | Action |
|---|---|---|
| Red count on bell | Unread alerts exist | Open bell and read |
| Read button | Mark one alert read | Click Read |
| 0 active | No unread alerts | No action needed |

Screens:

- `/admin/notifications` <a href="/admin/notifications" target="_blank" rel="noopener">↗</a>

---

## 13) Common problems and solutions

| Problem | Likely reason | Quick fix |
|---|---|---|
| Menu not visible | Missing permission | Ask admin to assign role permission |
| Cannot create order | Not enough available stock | Check inventory and transfer/replenish |
| Delivered but no invoice | Delivery not finalized as delivered | Update delivery status correctly |
| GRN not increasing stock | QC line not approved | Approve QC line and repost correctly |
| Notification count looks wrong | Alert not marked read | Mark read and refresh |

---

## 14) Month-end checklist

| Team | Checklist |
|---|---|
| Purchase | All open PO reviewed, pending GRN cleared |
| Warehouse | Write-off and audit variance reviewed |
| Production | Pending run confirmations completed |
| Sales | Dispatch backlog closed |
| Accounts | Invoice, receipt, credit note, outstanding reviewed |
| Management | P&L, stock summary, receivable aging reviewed |

---

## 15) Support handover format

| Required info | Example |
|---|---|
| Module + URL | Orders - `/admin/orders` |
| Document number | SO-000123 |
| Screenshot | Full page and error area |
| Exact action | Clicked Save after adding items |
| Expected result | Order should confirm |
| Actual result | Validation error shown |
| Time | 2026-03-02 11:20 AM |

---

Version: `v5.0`  
Last updated: `2026-03-02`
