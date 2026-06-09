# CONTEXT.md — HKT Fashion E-Commerce
> Last updated: 2026-06-09 | Environment: Docker Local + DigitalOcean VPS

---

## 🧱 Tech Stack

| Layer | Technology |
|---|---|
| CMS | WordPress (latest, Docker image) |
| E-Commerce | WooCommerce |
| Parent Theme | Flatsome (tracked as `flatsome.zip`, source excluded from Git) |
| Child Theme | `flatsome-child` (all custom code lives here) |
| Database | MySQL 8.0 (Docker container) |
| Server | Nginx (VPS DigitalOcean `167.172.91.249`) |
| Runtime | Docker Compose (local), Docker (VPS) |
| Deployment | Manual `scp` → VPS (no CI/CD yet) |
| Internal API | WordPress REST API, namespace `hkt/v1` |
| Address Data | Static JSON files in `inc/data/` |

---

## 📁 Key Folder Structure

```
wp-ecommerce/
├── docker-compose.yaml          # 3 services: db, wordpress, cli
├── setup.sh                     # Automated environment bootstrap script
├── docs/                        # Project requirement documents
│   ├── frontend_requirements.md
│   ├── backend_requirements.md
│   └── plan.md
└── src/wp-content/themes/flatsome-child/
    ├── functions.php            # Only require_once module files from inc/
    ├── style.css                # CSS Variables + all Flatsome overrides
    ├── template-custom-home.php # Custom homepage template
    ├── inc/                     # ⚡ ALL BUSINESS LOGIC LIVES HERE
    │   ├── checkout-customizer.php  # VN checkout form + phone validation
    │   ├── vietnam-divisions.php    # REST API for VN address hierarchy
    │   ├── payment-gateways.php     # BACS Banking + QR Code gateway
    │   ├── product-filters.php      # AJAX product attribute filters
    │   ├── product-swatches.php     # Color/size swatches + size guide
    │   ├── ajax-side-cart.php       # Slide-in mini cart
    │   ├── order-notifications.php  # Fireworks animation + order alerts
    │   ├── social-login.php         # Google/Facebook OAuth login
    │   ├── vietqr-bacs.php          # Dynamic VietQR per order
    │   ├── cron-jobs.php            # Auto-cancel overdue orders + restock
    │   ├── smtp-settings.php        # SMTP mail configuration
    │   ├── shipping-methods.php     # Custom shipping fee rules
    │   ├── mobile-navigation.php    # Sticky bottom nav bar (mobile)
    │   └── data/                    # Static JSON address data files
    └── templates/               # WooCommerce template overrides
```

---

## 📏 Coding Rules & Conventions

- **Function prefix:** `dev_` (e.g. `dev_custom_cart_button_text`)
- **REST API namespace:** `hkt/v1`
- **No logic in `functions.php`** — only `require_once` module files from `inc/`
- **HPOS compliant:** Use `$order->get_meta()` / `$order->update_meta_data()`, never raw SQL on `wp_postmeta`
- **Address field mapping (checkout):**
  - `billing_state` → Province/City (WooCommerce native state field, auto-filled from registered VN states)
  - `billing_city` → District (custom `select`, AJAX via `/hkt/v1/districts`)
  - `billing_address_2` → Ward (custom `select`, AJAX via `/hkt/v1/wards`)
- **Field priority order:** Province = 50, District = 60, Ward = 65
- **Deployment:** `scp` local file → VPS path `/var/www/wp-ecommerce/src/wp-content/...`

---

## ⚙️ Important Config Files

| File | Notes |
|---|---|
| `docker-compose.yaml` | DB: `root_password`, WP DB: `wordpress/wordpress_password`, local port `8000` |
| `uploads.ini` | PHP upload limit override for Docker |
| `src/.htaccess` | Proxy redirect for media images from staging when missing locally |
| VPS MySQL | DB inside Docker container, DB: `wordpress`, user: `root`, pass: `root_password` |
| `wp_options` (DB) | `woocommerce_default_country = VN` — critical for checkout state field rendering |
