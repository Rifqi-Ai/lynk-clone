# Route Audit — 25 routes tested

**Date:** 2026-06-20  
**Tester:** curl on http://127.0.0.1:8000

## Results

| Path | Status | Expected | Pass |
|------|--------|----------|------|
| `/` | 200 | 200 | ✓ |
| `/pricing` | 200 | 200 | ✓ |
| `/faq` | 200 | 200 | ✓ |
| `/about` | 200 | 200 | ✓ |
| `/terms` | 200 | 200 | ✓ |
| `/privacy` | 200 | 200 | ✓ |
| `/login` | 200 | 200 | ✓ |
| `/register` | 200 | 200 | ✓ |
| `/sitemap.xml` | 200 | 200 | ✓ |
| `/robots.txt` | 200 | 200 | ✓ |
| `/health` | 200 | 200 | ✓ |
| `/demo_alice` | 200 | 200 | ✓ |
| `/demo_bob` | 200 | 200 | ✓ |
| `/demo_charlie` | 200 | 200 | ✓ |
| `/demo_diana` | 200 | 200 | ✓ |
| `/demo_eko` | 200 | 200 | ✓ |
| `/demo_alice/2fl0y239y6np` | 200 | 200 | ✓ |
| `/demo_bob/3zwqhd7wljks` | 200 | 200 | ✓ |
| `/demo_alice/cart` | 200 | 200 | ✓ |
| `/demo_alice/2fl0y239y6np/checkout` | 200 | 200 | ✓ |
| `/dashboard` | 302 | 302 | ✓ |
| `/dashboard/products` | 302 | 302 | ✓ |
| `/dashboard/products/create` | 302 | 302 | ✓ |
| `/dashboard/fulfillment` | 302 | 302 | ✓ |
| `/settings/profile` | 302 | 302 | ✓ |

## Summary

- Tested: 25 routes
- Passing: 25
- Anomalies: 0

## Notes

- Auth-required routes correctly redirect (302) to /login when not authenticated
- All public pages return 200
- All 404s are expected for nonexistent resources
- SEO endpoints (sitemap.xml, robots.txt) working correctly
