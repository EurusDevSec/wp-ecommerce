---
name: vps_deploy
description: Deploying child theme files to DigitalOcean VPS, resetting permissions, and clearing remote caches
---

# VPS Deployment and Operations Skill

Deploying updates to a DigitalOcean VPS and managing remote server resources.

## 1. Manual Code Deployment (SCP/Rsync)
*   **Rsync (Recommended)**: Minimizes data transfers by sending only modified diffs.
    ```bash
    rsync -avz --exclude 'node_modules' --exclude '.git' ./src/wp-content/themes/flatsome-child/ root@167.172.91.249:/var/www/wp-ecommerce/src/wp-content/themes/flatsome-child/
    ```
*   **SCP (Individual File/Directory Copy)**:
    ```bash
    scp -r ./src/wp-content/themes/flatsome-child/inc/checkout-customizer.php root@167.172.91.249:/var/www/wp-ecommerce/src/wp-content/themes/flatsome-child/inc/
    ```

## 2. Remote VPS File Permissions
After transferring files, reset ownership to the web server process user (usually `www-data` on Ubuntu/Nginx) to avoid write-block issues or update failures.

```bash
# SSH into the VPS
ssh root@167.172.91.249

# Reset ownership to web server user
chown -R www-data:www-data /var/www/wp-ecommerce/src/wp-content/themes/flatsome-child/

# Secure directories and files permissions
find /var/www/wp-ecommerce/src/ -type d -exec chmod 755 {} \;
find /var/www/wp-ecommerce/src/ -type f -exec chmod 644 {} \;
```

## 3. Remote Cache Clearing
If CSS or backend behavior does not update after deployment, flush server-side caches.

*   **Redis Object Cache**:
    ```bash
    redis-cli flushall
    ```
*   **WP-CLI Remote Flush (if globally configured)**:
    ```bash
    wp cache flush --path=/var/www/wp-ecommerce/src
    ```
*   **Nginx FastCGI Cache (if enabled)**:
    ```bash
    rm -rf /var/run/nginx-cache/*
    systemctl reload nginx
    ```

## 4. Local Media Fallback Config
To avoid downloading large `uploads/` folders locally, add proxy redirect rules to your local `.htaccess` file. This tells Apache/Nginx to serve media assets from the live staging server if they do not exist locally.

```apache
# In local src/.htaccess
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
# If request is inside wp-content/uploads/
RewriteCond %{REQUEST_URI} ^/wp-content/uploads/(.*)$
# And the file does not exist locally
RewriteCond %{REQUEST_FILENAME} !-f
# Redirect proxy target to VPS staging/live URL
RewriteRule ^(.*)$ http://167.172.91.249/$1 [L,R=301]
</IfModule>
```
