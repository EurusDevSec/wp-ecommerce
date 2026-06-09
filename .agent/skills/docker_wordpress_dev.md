---
name: docker_wordpress_dev
description: Running local development for WordPress and WooCommerce with Docker Compose and interacting with WP-CLI
---

# Docker WordPress Development Skill

Local development environment for WordPress and WooCommerce containerized via Docker Compose.

## 1. Service Architecture
A standard WordPress Docker Compose stack has three services:
1.  **`db`**: MySQL or MariaDB database storage.
2.  **`wordpress`**: Apache/Nginx web server hosting PHP and the WP source code.
3.  **`cli`**: A short-lived container containing WP-CLI for executing system operations.

Ensure ports are correctly mapped (e.g., local port `8000` or `8080` to container port `80`).

## 2. Running WP-CLI Commands
Never execute WP commands directly on your local terminal. Run them inside the context of the `cli` container using `docker-compose run`.

*   **List Plugins**:
    ```bash
    docker-compose run --rm cli plugin list
    ```
*   **Search and Replace URLs**:
    When changing site ports or domains (e.g., from custom DNS to `localhost:8000`):
    ```bash
    docker-compose run --rm cli search-replace "http://staging.com" "http://localhost:8000" --all-tables
    ```
*   **Flush Permalinks / Rewrite Rules**:
    ```bash
    docker-compose run --rm cli rewrite flush
    ```
*   **Clear WooCommerce Transients**:
    ```bash
    docker-compose run --rm cli transient delete --all
    ```

## 3. Database Operations
*   **Access DB CLI**:
    ```bash
    docker-compose exec db mysql -u root -proot_password wordpress
    ```
*   **Import Database Dump**:
    Place the SQL file in the container context and pipe it:
    ```bash
    docker-compose exec -T db mysql -u root -proot_password wordpress < backup.sql
    ```
*   **Export Database**:
    ```bash
    docker-compose run --rm cli db export /var/www/html/backup.sql
    ```
    This creates the SQL file in the mounted workspace root.

## 4. Troubleshooting Local Mounts
If code edits in the child theme do not reflect in the browser:
1.  **Check Volume Mounting**: Verify `volumes` key in `docker-compose.yaml` maps the local directories to container directories:
    ```yaml
    volumes:
      - ./src:/var/www/html
    ```
2.  **Permissions Match**: Ensure the local host user files match permissions needed by `www-data` in the container.
3.  **Opcode Caching**: If PHP OPCache is enabled, restarts may be needed:
    ```bash
    docker-compose restart wordpress
    ```
