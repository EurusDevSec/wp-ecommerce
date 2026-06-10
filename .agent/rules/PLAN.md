---
trigger: always_on
---

# PLAN.md — HKT Fashion E-Commerce Roadmap
> Last updated: 2026-06-09 | Goal: Production-ready VN fashion store on WooCommerce

---

## 🎯 Primary Objective

Build **HKT Fashion** — a minimalist fashion e-commerce site using WordPress + WooCommerce + Flatsome Child Theme, fully satisfying `docs/frontend_requirements.md` and `docs/backend_requirements.md`, deployed on DigitalOcean VPS.

---

## ✅ BACKEND — Implementation Status

### `inc/` Module Files
- [x] `checkout-customizer.php` — Custom checkout form, VN phone validation
- [x] `vietnam-divisions.php` — REST API `/hkt/v1/districts` and `/hkt/v1/wards`
- [x] `payment-gateways.php` — BACS Banking + VietQR payment gateway
- [x] `vietqr-bacs.php` — Dynamic VietQR image per order
- [x] `product-filters.php` — AJAX attribute filter sidebar
- [x] `product-swatches.php` — Color/size swatches + size guide modal
- [x] `ajax-side-cart.php` — Slide-in mini cart
- [x] `order-notifications.php` — Fireworks animation on order success
- [x] `social-login.php` — Google/Facebook OAuth login
- [x] `cron-jobs.php` — Auto-cancel overdue orders + restock
- [x] `smtp-settings.php` — SMTP email configuration
- [x] `shipping-methods.php` — Custom shipping fee rules
- [x] `mobile-navigation.php` — Sticky mobile bottom navigation bar
- [x] HPOS compatibility declared in `functions.php`

### Checkout Address Cascade (Province → District → Ward)
- [x] API `/hkt/v1/districts?province_id=X` working
- [x] API `/hkt/v1/wards?district_id=X` working
- [x] `billing_state` = Province — dropdown shows all 63 VN provinces
- [x] `billing_city` = District — AJAX-loaded after province selection
- [x] `billing_address_2` = Ward — AJAX-loaded after district selection
- [x] WooCommerce default country set to `VN` in database
- [ ] **Verify**: No duplicate district options on `updated_checkout` re-trigger (guard added, needs more testing)
---

## ⬜ FRONTEND — Implementation Status

### Homepage
- [x] `template-custom-home.php` exists
- [x] Sticky header (shrinks on scroll)
- [x] Ajax search (product suggestions on ≥ 3 chars typed)
- [x] Asymmetric UX Banner Grid layout
- [x] New arrivals slider/carousel

### Shop / Catalog Page
- [x] `product-filters.php` — AJAX sidebar filter
- [x] Product card hover → swap to secondary image
- [ ] Quick View popup
- [ ] Wishlist button on product card

### Single Product Page
- [x] `product-swatches.php` — color/size swatches
- [ ] Size guide popup modal
- [ ] Trust badges (guarantee, returns, shipping)
- [ ] Sticky add-to-cart bar on scroll

### Checkout Page
- [x] Province → District → Ward cascade dropdowns working
- [x] VN phone number format validation
- [x] BACS payment with dynamic QR code
- [ ] 2-column minimal layout (responsive)
- [ ] Hide unnecessary fields (company, postcode)

### Mobile UI
- [x] `mobile-navigation.php` — sticky bottom nav bar

### Design System
- [ ] Full CSS Variables in `style.css` (primary `#FFFFFF`, accent `#1A1A1A`, text `#2B2B2B`)
- [x] Google Fonts: `Montserrat` (headings) + `Inter` (body text)
- [ ] Border-radius `2px–4px` consistent across components

---

## 🚀 DevOps / Infrastructure

- [x] Docker Compose (3 services: db, wordpress, cli)
- [x] `setup.sh` automated environment bootstrap
- [x] VPS DigitalOcean deployed (`167.172.91.249`)
- [x] Manual `scp` deployment workflow
- [ ] **CI/CD GitHub Actions** → auto-deploy on push to `main`
- [ ] CloudPanel staging `tt.phung.vn` fully configured
- [ ] `.htaccess` proxy redirect for local media from staging

---

## 🐛 Known Blockers / Bugs

| # | Issue | File | Severity |
|---|---|---|---|
| 1 | Duplicate district options possible when `updated_checkout` re-triggers `devBootstrap` | `checkout-customizer.php` L408 | Medium |
| 2 | `billing_state` won't render if DB default country ≠ VN (DB fix applied, not code-level) | `checkout-customizer.php` | Medium |
| 3 | Ward Select2 widget may not visually refresh immediately after options load | `checkout-customizer.php` L370 | Low |
| 4 | Many frontend features in requirements not yet implemented | `style.css`, templates | High |