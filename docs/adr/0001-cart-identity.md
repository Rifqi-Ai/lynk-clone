# ADR-001: Cart Identity — Cookie-Based Anonymous Carts

**Status:** Accepted (2026-06-21)
**Deciders:** Leira (solo dev), Hermes Agent (audit pair)
**Context:** Lynk-clone cart implementation

## Context and Problem Statement

Lynk-clone needs to let visitors add products to a cart before they register or log in (friction matters for SaaS conversion). The cart must survive:
- Anonymous browsing (no auth)
- Login transition (cart merges with user account)
- Multiple devices/browsers (eventually)
- 7-day session timeout

How should we identify a cart when the user is not yet authenticated?

## Decision Drivers

- **Conversion:** Friction kills SaaS conversion — forcing login before add-to-cart loses sales
- **Privacy:** No PII should leak across users
- **Simplicity:** Solo dev, minimal complexity
- **Security:** Cart items must not be modifiable by other users

## Considered Options

1. **Session-based cart** (PHP session ID)
2. **Cookie-based cart** (UUID in cookie, stored in DB)
3. **IP + User-Agent fingerprinting**
4. **Force login before add-to-cart**

## Decision Outcome

**Chosen option: 2 — Cookie-based cart with UUID**

```php
// CartController::getOrCreateCart
$cartId = $request->cookie('cart_id_'.$creator->id);
if ($cartId && preg_match('/^[a-f0-9]{8}-...-[a-f0-9]{12}$/i', $cartId)) {
    $cart = Cart::where('id', $cartId)
        ->where('creator_user_id', $creator->id)
        ->where('expires_at', '>', now())
        ->first();
    if ($cart) {
        if (Auth::id() && ! $cart->buyer_user_id) {
            $cart->update(['buyer_user_id' => Auth::id()]);
        }
        return $cart;
    }
}
// Create new cart with UUID + cookie
```

### Positive Consequences

- ✅ Guests can browse and add to cart without friction
- ✅ Cart merges with user account on login (`buyer_user_id` set)
- ✅ Per-creator cart isolation (different cart per store)
- ✅ UUID validation prevents cookie tampering
- ✅ 7-day expiry cleans up abandoned carts

### Negative Consequences

- ❌ Cart cookie is lost if user clears cookies
- ❌ Multi-device sync requires login (acceptable — Phase 16+)
- ❌ Test framework has trouble with cookie persistence (use direct DB seeding in tests)

## Pros and Cons of the Options

### 1. Session-based cart
- Good: Auto-cleared on browser close
- Bad: Lost on session expiry (worse than 7-day cookie)
- Bad: Doesn't survive across browser restarts

### 2. Cookie-based cart (UUID) ✅ CHOSEN
- Good: Survives browser restart (7 days)
- Good: Anonymous + mergeable on login
- Good: Per-creator isolation
- Bad: Lost if cookies cleared (acceptable — user can rebuild)

### 3. IP + UA fingerprinting
- Good: No cookie dependency
- Bad: Privacy concerns (GDPR)
- Bad: Mobile networks share IPs → wrong cart attribution
- Bad: Behind NAT, multiple users share fingerprint

### 4. Force login before add-to-cart
- Good: Simplest implementation
- Bad: Massive conversion killer (typical SaaS loses 30-50% of add-to-cart conversions)
- Bad: Direct competitor with logged-in flow has lower friction

## Validation

- Implemented in `app/Http/Controllers/CartController.php` (Phase 1)
- Tested via `tests/Feature/CartMergeLogicTest.php` (Phase 15)
- Cookie format validation prevents injection (UUID regex)
- Cross-user isolation: `cart_id_{creator_id}` ensures cart belongs to specific creator's store

## Links

- CartController: `app/Http/Controllers/CartController.php`
- Cart model: `app/Models/Cart.php`
- Tests: `tests/Feature/CartMergeLogicTest.php`
- Phase 15 audit: this ADR documents a key architectural decision
