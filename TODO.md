# Implementation TODO (Enterprise Checkout Orchestration)

## Phase 1 — Architecture Hardening (Current Task)

### 1) Baseline & Analysis
- [x] Analyze existing checkout architecture
- [x] Identify concurrency/idempotency/transaction/webhook weaknesses
- [x] Define clean-architecture refactor boundaries (no overengineering)

### 2) Data Model & Persistence
- [ ] Add tenant migration: `checkout_processes` (lightweight saga state)
- [ ] Add tenant migration: `midtrans_webhook_receipts` (replay protection ledger)
- [ ] Extend tenant migration: `checkout_idempotencies` with `error_code`, `error_message`
- [ ] Add indexes for hot paths (idempotency/process status/webhook dedupe)

### 3) Domain Models
- [ ] Add model: `CheckoutProcess`
- [ ] Add model: `MidtransWebhookReceipt`
- [ ] Update model: `CheckoutIdempotency` fillable/casts/scopes
- [ ] Keep models minimal and explicit

### 4) Checkout Orchestration (Saga-style, lightweight)
- [ ] Refactor `CheckoutOrchestratorService` to explicit step transitions:
  - [ ] initiated
  - [ ] order_created
  - [ ] stock_reserved (if async payment methods)
  - [ ] payment_initiated / completed
  - [ ] failed (with reason)
- [ ] Keep Redis distributed lock (tenant-scoped lock key)
- [ ] Keep strict request hash validation per idempotency key
- [ ] Add compensation hook for failed steps
- [ ] Remove unnecessary nested transactions where safe

### 5) Action Layer Refactor
- [ ] Refactor `PlaceOrderAction` to avoid internal transaction when orchestrated
- [ ] Refactor `ReserveStockAction` with deterministic lock ordering + single transaction per order
- [ ] Add underflow guards when decrementing/reserving/releasing
- [ ] Keep idempotent behavior for re-entry/retries

### 6) Checkout Controller Integration
- [ ] Refactor `CheckoutController@process` to delegate orchestration + payment initiation flow
- [ ] Remove unsafe order delete on payment initiation failure
- [ ] Return stable retry-safe response for failed payment initiation
- [ ] Preserve UX behavior for COD/bank transfer/midtrans success paths

### 7) Midtrans Webhook Hardening
- [ ] Add replay protection in webhook processing using receipt ledger unique key
- [ ] Refactor `ProcessMidtransWebhookJob` queue safety:
  - [ ] Add overlap prevention middleware keying by tenant+transaction/order
- [ ] Ensure `HandleMidtransWebhookAction` remains idempotent for state transitions
- [ ] Prevent duplicate stock release/cancel side effects across replays

### 8) Reservation Expiration & Race Safety
- [ ] Harden `ReleaseExpiredReservationsCommand`:
  - [ ] chunked processing
  - [ ] lock-safe cancellation
  - [ ] idempotent status checks
- [ ] Ensure only payable-pending orders are auto-cancelled
- [ ] Verify no double-cancel / no negative reserved quantity

### 9) Tests (Feature-focused)
- [ ] Add checkout idempotency tests:
  - [ ] same key + same payload => same order
  - [ ] same key + different payload => validation/runtime error
- [ ] Add duplicate-submit/concurrency safety test (single order outcome)
- [ ] Add payment retry safety test (no duplicate order/payment init)
- [ ] Add webhook replay ledger test (duplicate event no-op)
- [ ] Add stock reservation lock-order/idempotent side-effect tests
- [ ] Add reservation expiry idempotency tests

### 10) Verification
- [ ] Run focused tests:
  - [ ] `tests/Feature/Checkout/*`
  - [ ] `tests/Feature/Payment/*`
- [ ] Fix regressions and finalize architectural notes
