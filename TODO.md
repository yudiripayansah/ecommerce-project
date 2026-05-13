# Implementation TODO (Enterprise Laravel Architecture)

## Phase 1 — Checkout Orchestration Transaction
- [x] Add tenant migration: `checkout_idempotencies` table
- [x] Add model: `CheckoutIdempotency`
- [x] Add service/action: `CheckoutOrchestratorService`
- [x] Integrate `PlaceOrderAction` + `ReserveStockAction` under orchestration flow
- [x] Add Redis lock + DB transaction safety for idempotent checkout submit
- [ ] Add tests: duplicate submit returns same order

## Phase 2 — Queue Tenancy Audit
- [ ] Add tenant migration: `queue_job_audits` table
- [ ] Add model: `QueueJobAudit`
- [ ] Add reusable concern/middleware for queued-job auditing
- [ ] Integrate into key jobs (`ProcessMidtransWebhookJob`, notification jobs)
- [ ] Add tests for audit record creation and tenant attribution

## Phase 3 — Inventory Consistency Enforcement
- [ ] Harden reserve/release/cancel actions with strict underflow guards
- [ ] Add deterministic row locking strategy
- [ ] Add idempotent status transition protections
- [ ] Add/verify indexes for reservation & inventory hot paths
- [ ] Add tests for concurrent and duplicate event scenarios

## Phase 4 — Cache RajaOngkir (Redis-first)
- [ ] Refactor `RajaOngkirService` to Redis cache + TTL
- [ ] Add lock-based stale-while-revalidate refresh strategy
- [ ] Keep file fallback compatibility
- [ ] Add tests for cache hit/miss and refresh lock behavior

## Phase 5 — Automated Testing
- [ ] Add Pest feature tests for all 4 systems above
- [ ] Add queue/HTTP/cache fakes where appropriate
- [ ] Run targeted test suites and document expected outcomes
