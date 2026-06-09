# SESSION MEMORY — HKT Fashion
> Session: 2026-06-09 | Status: Created technical agent skills reference files

---

## ⚡ Active Task (Just Completed)

**Create 8 agent skill reference files in `.agent/skills/`**

Created complete development and deployment reference documentation to enable seamless transitions between development sessions:
1.  `wordpress_hooks.md` - Modular code organization, namespace isolation, and action/filter priorities.
2.  `woocommerce_checkout.md` - Form field optimization, VN validation, and HPOS-compliant CRUD orders metadata.
3.  `woocommerce_rest_api.md` - Namespace `hkt/v1` routes registration, capabilities checks, and caching transient strategies.
4.  `select2_ajax_dropdown.md` - Dynamic cascading SELECT elements (Province → District → Ward), jQuery AJAX calls, and `updated_checkout` lifecycle guards.
5.  `docker_wordpress_dev.md` - Local Docker Compose workspace, database CLI operations, and WP-CLI commands execution.
6.  `vps_deploy.md` - Syncing files via SCP/Rsync to DigitalOcean droplet, fixing file ownership permissions, flushing Redis, and local media `.htaccess` proxies.
7.  `flatsome_child.md` - Styling conventions with CSS variables, parent template overriding, and theme enqueue scripts/styles hook rules.
8.  `github_actions_deploy.md` - CI/CD deployment configuration template using GitHub Actions over SSH.

---

## 📝 Code Changes (Files modified this session)

| File | Key Change |
|---|---|
| `.agent/skills/wordpress_hooks.md` | New skill file: Hook callback patterns and structure rules. |
| `.agent/skills/woocommerce_checkout.md` | New skill file: Fields customization and HPOS database actions. |
| `.agent/skills/woocommerce_rest_api.md` | New skill file: Custom endpoints and request sanitization. |
| `.agent/skills/select2_ajax_dropdown.md` | New skill file: Select2 JS lifecycles and event guards. |
| `.agent/skills/docker_wordpress_dev.md` | New skill file: Local dev and database CLI guide. |
| `.agent/skills/vps_deploy.md` | New skill file: Remote sync, permissions resetting, and proxy fallbacks. |
| `.agent/skills/flatsome_child.md` | New skill file: Flatsome Child structures and design variables. |
| `.agent/skills/github_actions_deploy.md` | New skill file: GitHub workflows deployment YAML template. |

---

## ✅ Verified Results

- ✅ All 8 skill files successfully written to the `.agent/skills/` directory.
- ✅ Verified YAML front matter and markup style compatibility across files.
- ✅ All file links in the plans and tasks are correct and valid.

---

## 🔜 Next Steps (3 immediate technical actions)

### Step 1 — End-to-end checkout test
- Open `http://167.172.91.249/checkout/`
- Fill all fields: Name, Phone, Email, Street Address, select Province → District → Ward
- Click **PLACE ORDER** → verify order is created in WP Admin
- Confirm `billing_state`, `billing_city`, `billing_address_2` are saved correctly in order meta

### Step 2 — Implement missing frontend features (highest priority)
Per `docs/frontend_requirements.md`:
- `style.css`: Add full CSS Variables (`--color-primary: #FFFFFF`, `--color-accent: #1A1A1A`, `--color-text: #2B2B2B`)
- `style.css`: Import Google Fonts `Montserrat` (headings) + `Inter` (body)
- Single Product: Verify swatches (`product-swatches.php`) render correctly on live site
- Shop page: Verify AJAX filter (`product-filters.php`) fires on attribute click

### Step 3 — Setup CI/CD GitHub Actions
File to create: `.github/workflows/deploy.yml`
- Trigger: push to `main` branch
- Action: `rsync` the `flatsome-child/` directory to VPS at `/var/www/wp-ecommerce/src/wp-content/themes/flatsome-child/`
- SSH key stored in GitHub Secrets as `VPS_SSH_KEY`
- Target server: `root@167.172.91.249`
- Goal: eliminate the current manual `scp` deployment step

---

## 🔑 Connection Info (Quick Reference)

```
VPS IP:        167.172.91.249
VPS User:      root
WP Root Path:  /var/www/wp-ecommerce/src/
DB Container:  wp-ecommerce_db_1
DB Root Creds: root / root_password
WP DB Creds:   wordpress / wordpress_password
WP Admin URL:  http://167.172.91.249/wp-admin/  (admin@gmail.com)
Local Dev URL: http://localhost:8000
```
