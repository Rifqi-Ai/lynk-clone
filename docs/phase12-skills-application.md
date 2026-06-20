# Phase 12 — Apply Skills (linka-design-system + backend-audit-hardening)

**Date:** 2026-06-20  
**Goal:** Apply remaining items from Hermes skills (linka-design-system, backend-audit-hardening) to close UX/security gaps found in dogfood-style audit.

**Methodology:** TDD per task + 2-stage review + verify + push

---

## Found Gaps

### Frontend (UI/UX from linka-design-system)

1. **Trust signal icons multi-colored** — `text-success` (green) + `text-brand-600` (orange) + `text-blue-600` (blue) on same product page. Violates monochromatic palette rule.
2. **Avatar fallback is GREEN** (`background=2AB57D`) — clashes with warm cream + orange brand.

### Backend (from backend-audit-hardening)

3. **No honeypot on login/register** — bots can submit forms freely.
4. **No failed login logging** — can't see who's attacking.
5. **No progressive delay on failed attempts** — rate limit alone may not deter persistent attacks.

---

## Tasks

### Task F1: Unify trust signal icons + warm avatar fallback

**Files:**
- `resources/views/public/product.blade.php` (unify 3 icons to brand-600)
- `app/Models/User.php` (change avatar fallback from `2AB57D` green to `FF5722` brand orange)

**Verification:**
- All 3 trust signal icons use `text-brand-600`
- Avatar fallback URL contains `background=FF5722`
- Live curl shows updated colors

### Task B1: Honeypot field on login + register

**Objective:** Block simple bots that fill all form fields. Add hidden "website" field that's empty for humans but bots fill it.

**Files:**
- `resources/views/auth/login.blade.php` (add hidden input)
- `resources/views/auth/register.blade.php` (add hidden input)
- `app/Http/Controllers/AuthController.php` (reject if filled)

**Verification:**
- Form rendered has `<input name="website" type="text" style="display:none">`
- POST with `website=test` returns 422 / error
- POST without `website` works normally

### Task B2: Failed login logging with security observability

**Objective:** Log every failed login attempt with email + IP + user_agent + request_id for threat detection.

**Files:**
- `app/Http/Controllers/AuthController.php` (add Log::warning on failure)
- `tests/Feature/SecurityAuditLogTest.php` (verify log written)

**Verification:**
- Failed login creates log entry with structured context
- Includes `event: login.failed`, `email`, `ip`, `user_agent`, `request_id`
- Test passes

---

## Acceptance Criteria

- [ ] All 3 tasks complete
- [ ] All 91+ existing tests still pass
- [ ] New tests added for honeypot + logging
- [ ] Live verification shows changes
- [ ] Pint clean
- [ ] All commits pushed
