# AF ERP System Cycle (Start -> End)

## START POINT (Trigger)

A demand happens from either entry point:

1. You need to produce stock (low stock / forecast demand), or
2. An agent places an order and stock must be ready.

So the cycle has 2 entry points, and both connect into one flow.

## Cycle A - Procure -> Produce -> Stock Ready (Factory Side)

### A1) Master Setup (One-time, then ongoing updates)
- Start: new SKU / new supplier / new agent / price update
- End: master data ready for operations

Scope:
- Product SKU + attributes + barcode/QR
- Packaging types + unit conversions
- Tax/VAT/HSN codes
- Supplier master + terms
- BOM for each FG (500ml/1L etc.)
- Warehouses + zones + routes
- Agent master + pricing + credit + commission rules

Output:
- ERP is configured

### A2) Purchasing / Procurement (Supplier cycle)
- Start: RM/PM stock low OR purchase plan created
- End: raw/packaging materials available in store

Flow:
- Purchase requisition / reorder trigger
- PO created & approved
- Supplier delivers RM/PM
- GRN entry + quantity check
- Incoming QC (accept/hold/reject)
- Supplier bill recorded

Output:
- RM/PM inventory increased (store stock ready for production)

### A3) Production & Bottling (BOM consumption cycle)
- Start: production plan / production order created
- End: finished goods (FG) batch ready in warehouse

Flow:
- Production order (FG qty + date)
- System calculates required materials from BOM
- RM/PM reserved
- Store issues RM/PM to production (material issue)
- Bottling run completed
- Batch/lot created (production date, expiry)
- QC update (approved/hold)
- FG received into plant warehouse

Output:
- FG inventory available with batch/expiry

### A4) FG Distribution Stock Preparation (Optional but common)
- Start: plant FG stock available
- End: stock positioned at depot/route level

Flow:
- Transfer FG from plant -> central depot
- Transfer depot -> sub-depot/van stock (if needed)
- FEFO/FIFO tracking stays intact

Output:
- Stock is placed where sales delivery happens fastest

## Cycle B - Sell -> Deliver -> Invoice -> Collect (Market Side)

### B1) Sales Order (Agent cycle)
- Start: agent places order
- End: confirmed order ready for delivery planning

Flow:
- Order entry (agent app/web/backoffice)
- Pricing applied (price list / negotiated)
- Credit limit & outstanding check
- Commission rule tagged
- Order confirmed (or put on hold)

Output:
- Sales order confirmed

### B2) Warehouse Fulfillment (Pick/Pack/Dispatch)
- Start: confirmed order + stock available
- End: order dispatched with correct stock deduction

Flow:
- Stock reserved (reserved vs available)
- Pick list generated (FEFO by batch)
- Packing slip + crate/vehicle plan
- Dispatch (stock moves depot -> van / dispatch location)

Output:
- Dispatched order + inventory updated

### B3) Delivery & POD (Proof of Delivery)
- Start: dispatched order
- End: delivery finalized (success or exception)

Flow:
- Route plan + driver assignment
- Delivery attempt
- POD capture (signature/photo)
- Exceptions recorded if needed:
  - shortage
  - damage
  - reject
  - return

Output:
- Delivered quantity confirmed (truth data)

### B4) Finance Posting (Invoice + VAT)
- Start: delivery confirmed (POD)
- End: accounts updated & invoice issued

Flow:
- Auto-generate invoice from delivered quantity
- VAT calculation (Bangladesh VAT fields)
- AR entry created in ledger
- If return/exception: credit note / adjustment created

Output:
- Invoice + VAT + AR ledger updated

### B5) Collection & Reconciliation
- Start: agent pays / payment collected
- End: agent balance closed + bank matched

Flow:
- Receipt entry (cash/bank/advance adjustment)
- Agent ledger updated (outstanding reduced)
- Bank reconciliation matched to statement

Output:
- Cash/bank position accurate + AR settled

### B6) Commission Settlement
- Start: month-end or commission cycle date
- End: commission paid & accounted

Flow:
- Calculate commissions (SKU %, tiered, fixed)
- Post commission payable
- Payout to agent
- Ledger updated

Output:
- Commission expense + payout completed

## END POINT (Cycle End Definition)

ERP cycle is complete when:
- Stock was produced and available
- Orders were delivered with POD
- Invoice + VAT posted
- Payment received & reconciled
- Commission accounted & paid
- Reports reflect the final truth

That is the full start-to-end AF ERP system cycle.

## One-line Start -> End Summary (Documentation)

Supplier Purchase -> RM/PM Stock -> BOM Issue -> Production Batch/QC -> FG Stock -> Agent Order -> Pick/Pack/Dispatch -> POD Delivery -> Return/Credit Note (if any) -> Invoice/VAT -> Payment + Bank Reco -> Commission Payout -> Reporting
