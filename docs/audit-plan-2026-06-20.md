# Comprehensive Audit & Fix Plan — Lynk-clone (Linka)

**Date:** 2026-06-20
**Goal:** Fix critical bugs, missing accessors, and gaps found in full frontend+backend audit. Bring app to truly production-ready state.

**Methodology:**
- TDD where applicable (write test → fail → fix → pass)
- 2-stage review per subagent (spec compliance then code quality)
- One fresh subagent per task (no context pollution)
- Maximum 2 fix-and-reverify cycles per task

---

## Phase A — CRITICAL (blocks revenue)

### Task A1: Add `checkout_url` accessor to Product model

**Objective:** Fix business-killing bug where all product checkout buttons render with `href=""` because the Product model is missing `getCheckoutUrlAttribute()`.

**Impact:** Without this, NO customer can purchase ANY product. All CTA buttons lead nowhere.

**Files:**
- Modify: `app/Models/Product.php` (add accessor)
- Test: `tests/Feature/ProductCheckoutUrlTest.php` (new file)

**Step 1: Write failing test**

```php
public function test_checkout_url_accessor_returns_correct_route()
{
    $creator = User::factory()->create(['username' => 'testcreator']);
    $product = Product::factory()->create([
        'user_id' => $creator->id,
        'id' => 'testabc12345',
        'type' => 'digital',
    ]);

    $this->assertStringContainsString(
        '/testcreator/testabc12345/checkout',
        $product->checkout_url
    );
}
```

**Step 2: Run test, verify FAIL**

Run: `cd /home/azureuser/lynk-clone && php artisan test --filter=test_checkout_url_accessor_returns_correct_route`
Expected: FAIL — "Property [checkout_url] does not exist"

**Step 3: Implement accessor**

In `app/Models/Product.php`, add (in Helpers section after `getUrlAttribute`):

```php
/**
 * Get the checkout URL for this product.
 * Routes customer to {username}/{productId}/checkout where they enter payment details.
 */
public function getCheckoutUrlAttribute(): string
{
    return url("/{$this->owner->username}/{$this->id}/checkout");
}
```

**Step 4: Run test, verify PASS**

Run: `cd /home/azureuser/lynk-clone && php artisan test --filter=test_checkout_url_accessor_returns_correct_route`
Expected: PASS

**Step 5: Live verify**

```bash
curl -sL http://127.0.0.1:8000/demo_alice/2fl0y239y6np | grep -oP 'href="[^"]*"[^>]*class="btn-cta[^"]*"' | head -1
```

Expected: `href="http://127.0.0.1:8000/demo_alice/2fl0y239y6np/checkout" class="btn-cta btn-block"`

**Step 6: Run all tests + commit**

```bash
cd /home/azureuser/lynk-clone && php artisan test  # must show 70 passed
cd /home/azureuser/lynk-clone && vendor/bin/pint app/Models/Product.php tests/Feature/ProductCheckoutUrlTest.php
cd /home/azureuser/lynk-clone && git add -A && git commit -m "fix(critical): add checkout_url accessor to Product model"
```

**Review criteria (for spec reviewer):**
- [ ] Accessor returns valid URL pointing to /{username}/{productId}/checkout
- [ ] Test exists and passes
- [ ] No regression in existing tests (still 69+1=70 passing)

**Review criteria (for quality reviewer):**
- [ ] Accessor properly handles case where owner relationship might be null (defensive)
- [ ] PHPDoc comment explains what the URL is for
- [ ] Follows existing accessor style (matches getUrlAttribute)

---

### Task A2: Test full end-to-end checkout flow

**Objective:** Verify the entire purchase flow works now that `checkout_url` is fixed. Catch any other bugs in the path.

**Files:**
- Modify: `tests/Feature/CheckoutEndToEndTest.php` (new file)
- Reference: `app/Http/Controllers/PublicProfileController.php`, `app/Services/OrderService.php`

**Step 1: Write end-to-end test**

```php
public function test_buyer_can_view_checkout_page_from_product()
{
    $creator = User::factory()->create(['username' => 'seller']);
    $product = Product::factory()->create([
        'user_id' => $creator->id,
        'id' => 'testid1234ab',
        'type' => 'digital',
        'price' => 50000,
        'status' => 'published',
    ]);

    $response = $this->get('/seller/testid1234ab/checkout');
    $response->assertStatus(200);
    $response->assertSee('50000'); // Price visible
    $response->assertSee('Email'); // Payer email field present
}

public function test_checkout_button_link_is_correct()
{
    $creator = User::factory()->create(['username' => 'seller']);
    $product = Product::factory()->create([
        'user_id' => $creator->id,
        'id' => 'testid1234ab',
        'type' => 'digital',
        'status' => 'published',
    ]);

    $response = $this->get('/seller/testid1234ab');
    $response->assertStatus(200);
    $response->assertSee('href="http://localhost/seller/testid1234ab/checkout"', false);
}
```

**Step 2: Run tests**

Run: `cd /home/azureuser/lynk-clone && php artisan test --filter=CheckoutEndToEndTest`
Expected: PASS (after Task A1)

**Step 3: Live test**

```bash
curl -sL http://127.0.0.1:8000/demo_alice/2fl0y239y6np | grep -E 'btn-cta' | head -1
# Should show: href="http://127.0.0.1:8000/demo_alice/2fl0y239y6np/checkout"
```

**Step 4: Commit**

```bash
git add -A && git commit -m "test: add end-to-end checkout flow tests"
```

---

## Phase B — HIGH (missing accessors causing silent failures)

### Task B1: Add missing Product accessors (readTime, file_url, track_inventory)

**Objective:** Add accessors used in blade templates but not defined in the Product model. These currently silently return null/default values, breaking UI for blog/digital/physical products.

**Files:**
- Modify: `app/Models/Product.php`
- Test: `tests/Feature/ProductAccessorsTest.php` (new file)

**Missing accessors (referenced in templates, not in model):**
- `$product->readTime` — blog reading time in minutes
- `$product->file_url` — public URL to download digital product file
- `$product->track_inventory` — bool from metadata

**Step 1: Write failing tests**

```php
public function test_blog_product_read_time_accessor()
{
    $creator = User::factory()->create(['username' => 'blogger']);
    $product = Product::factory()->create([
        'user_id' => $creator->id,
        'type' => 'blog',
        'metadata' => ['body_markdown' => str_repeat('lorem ipsum dolor sit amet. ', 200)],
    ]);

    $this->assertGreaterThan(0, $product->readTime);
    $this->assertLessThan(60, $product->readTime);
}

public function test_digital_product_file_url_accessor()
{
    $creator = User::factory()->create(['username' => 'seller']);
    $product = Product::factory()->create([
        'user_id' => $creator->id,
        'type' => 'digital',
        'file_path' => 'products/test/file.pdf',
    ]);

    $this->assertStringContainsString('storage/products/test/file.pdf', $product->file_url);
}

public function test_physical_product_track_inventory_accessor()
{
    $creator = User::factory()->create(['username' => 'seller']);
    $product = Product::factory()->create([
        'user_id' => $creator->id,
        'type' => 'physical',
        'metadata' => ['track_inventory' => true, 'stock_quantity' => 5],
    ]);

    $this->assertTrue($product->track_inventory);
}
```

**Step 2: Implement accessors**

In `app/Models/Product.php`, add to Type-specific helpers section:

```php
/** Blog: estimated reading time in minutes (avg 200 words/min) */
public function getReadTimeAttribute(): int
{
    $body = $this->meta('body_markdown', '');
    $wordCount = str_word_count(strip_tags($body));
    return max(1, (int) ceil($wordCount / 200));
}

/** Digital: public URL to download file */
public function getFileUrlAttribute(): ?string
{
    if (! $this->file_path) {
        return null;
    }
    return \Storage::disk('public')->url($this->file_path);
}

/** Physical: whether to track inventory (default true) */
public function getTrackInventoryAttribute(): bool
{
    return (bool) $this->meta('track_inventory', true);
}
```

**Step 3: Verify**

Run: `cd /home/azureuser/lynk-clone && php artisan test --filter=ProductAccessorsTest`
Expected: 3 passing

**Step 4: Commit**

```bash
git add -A && git commit -m "feat(product): add readTime, file_url, track_inventory accessors"
```

---

### Task B2: Add missing User accessors (display_name, followers_count, is_verified, total_sales_count)

**Objective:** Add accessors used in templates but not defined in User model.

**Files:**
- Modify: `app/Models/User.php`
- Test: `tests/Feature/UserAccessorsTest.php` (new file)

**Missing accessors:**
- `$user->display_name` — name with fallback to username
- `$user->followers_count` — count (default 0 for now, no followers table yet)
- `$user->is_verified` — alias for `verified` boolean column
- `$user->total_sales_count` — sum of paid orders as creator

**Step 1: Write failing tests**

```php
public function test_display_name_falls_back_to_username()
{
    $user = User::factory()->create(['name' => 'Alice Pratama', 'username' => 'alice']);
    $this->assertEquals('Alice Pratama', $user->display_name);

    $user2 = User::factory()->create(['name' => '', 'username' => 'bob']);
    $this->assertEquals('bob', $user2->display_name);
}

public function test_is_verified_alias()
{
    $user = User::factory()->create(['verified' => true]);
    $this->assertTrue($user->is_verified);

    $user2 = User::factory()->create(['verified' => false]);
    $this->assertFalse($user2->is_verified);
}

public function test_total_sales_count_sums_paid_orders()
{
    $user = User::factory()->create();
    $product = Product::factory()->create(['user_id' => $user->id]);

    Order::factory()->count(3)->create([
        'creator_user_id' => $user->id,
        'product_id' => $product->id,
        'payment_status' => 'paid',
    ]);
    Order::factory()->create([
        'creator_user_id' => $user->id,
        'product_id' => $product->id,
        'payment_status' => 'pending',
    ]);

    $this->assertEquals(3, $user->total_sales_count);
}

public function test_followers_count_default_zero()
{
    $user = User::factory()->create();
    $this->assertEquals(0, $user->followers_count);
}
```

**Step 2: Implement accessors**

In `app/Models/User.php`, add:

```php
public function getDisplayNameAttribute(): string
{
    return $this->name ?: '@'.$this->username;
}

public function getIsVerifiedAttribute(): bool
{
    return (bool) $this->verified;
}

public function getFollowersCountAttribute(): int
{
    // MVP: no followers table yet. Real impl when social features added.
    return 0;
}

public function getTotalSalesCountAttribute(): int
{
    return $this->ordersAsCreator()
        ->where('payment_status', 'paid')
        ->count();
}
```

**Step 3: Verify + commit**

Run: `php artisan test --filter=UserAccessorsTest`
Then: `git commit -am "feat(user): add display_name, is_verified, followers_count, total_sales_count accessors"`

---

## Phase C — Polish (cleanup + regressions)

### Task C1: Fix TODO comments + clean up

**Objective:** Address tech debt markers (2 TODOs found).

**Files:**
- Modify: `app/Services/OrderService.php` (line 159)

**Step 1: Read context**

```bash
grep -n "TODO\|FIXME" /home/azureuser/lynk-clone/app/Services/OrderService.php
```

The TODO at line 159 says: `// flat Rp 15K (TODO: real calc)`. Replace with note that it's deferred but cap shipping at a sane max.

**Step 2: Fix**

Replace `// flat Rp 15K (TODO: real calc)` with `// MVP: flat shipping. Replace with RajaOngkir API when ready.` — captures intent without lying about implementation status.

**Step 3: Verify + commit**

```bash
vendor/bin/pint --test
php artisan test
git commit -am "chore: replace shipping TODO with accurate MVP note"
```

---

### Task C2: Verify all 54 routes + fix any 4xx/5xx issues

**Objective:** Hit each route, ensure expected status codes, document any unexpected behavior.

**Step 1: Run route audit script**

```bash
for route in \
  "/", "/pricing", "/faq", "/about", "/terms", "/privacy", \
  "/login", "/register", "/sitemap.xml", "/robots.txt", "/health", \
  "/demo_alice", "/demo_alice/2fl0y239y6np", "/demo_alice/cart"; do
  code=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000$route)
  echo "$code $route"
done
```

**Step 2: Document any anomalies** (e.g., course pages returning 404 for non-purchasers — expected, but log it)

**Step 3: Commit audit results as docs**

```bash
echo "# Route Audit $(date)" > docs/route-audit.md
# ... append results
git add docs/route-audit.md && git commit -m "docs: add route audit log"
```

---

### Task C3: Run full test suite + visual smoke test + push

**Objective:** Final verification before push.

**Step 1: Run all tests**

```bash
cd /home/azureuser/lynk-clone && php artisan test
```

Expected: 75+ tests, 195+ assertions, 100% passing

**Step 2: Run pint**

```bash
cd /home/azureuser/lynk-clone && vendor/bin/pint --test
```

Expected: All files pass

**Step 3: Final live verify**

```bash
# Critical flow
curl -sL http://127.0.0.1:8000/demo_alice/2fl0y239y6np | grep -oP 'href="[^"]*"[^>]*btn-cta[^"]*"' | head -1
# Should show real checkout URL, not empty

# Sitemap
curl -s http://127.0.0.1:8000/sitemap.xml | head -10
# Should show valid XML

# Health
curl -s http://127.0.0.1:8000/health | python3 -m json.tool
# Should show all subsystems ok
```

**Step 4: Push**

```bash
cd /home/azureuser/lynk-clone && git push origin main
```

---

## Acceptance Criteria

- [ ] All 75+ tests pass
- [ ] Pint clean
- [ ] Product page checkout button has non-empty href
- [ ] All 54 routes return expected status codes
- [ ] No PHP errors in laravel.log (last hour)
- [ ] Sitemap URLs all return 200
- [ ] All commits pushed to github.com/Rifqi-Ai/lynk-clone

---

## Estimated Effort

- Phase A: 2 tasks, ~30 min
- Phase B: 2 tasks, ~30 min
- Phase C: 3 tasks, ~30 min

**Total: ~1.5 hours of focused work, dispatched in parallel-friendly chunks**
