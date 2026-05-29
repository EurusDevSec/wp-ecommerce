# wp-ecommerce

![WordPress](https://img.shields.io/badge/WordPress-%23117AC9.svg?style=for-the-badge&logo=WordPress&logoColor=white)
![WooCommerce](https://img.shields.io/badge/WooCommerce-%23FF690F.svg?style=for-the-badge&logo=WooCommerce&logoColor=white)
![Flatsome](https://img.shields.io/badge/Flatsome-%232ECC71.svg?style=for-the-badge&logo=Flatsome&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-%232496ED.svg?style=for-the-badge&logo=Docker&logoColor=white)
![Antigravity](https://img.shields.io/badge/Antigravity-%2300D063.svg?style=for-the-badge&logo=Antigravity&logoColor=white)
![Stitch MCP](https://img.shields.io/badge/Stitch%20MCP-%2347A996.svg?style=for-the-badge&logo=Stitch%20MCP&logoColor=white)


## 1. Git clone

```bash
git clone https://github.com/EurusDevSec/wp-ecommerce.git
```

## 2. Staging/Production Hosting

| Name | URL | Credentials |
| --- | --- | --- |
| Staging | https://ecommerce-staging.eurustudio.com/ | - |
| Production | https://ecommerce-production.eurustudio.com/ | - |


## 3. add wordpress-7.0 folder to src


## 4. Docker Setup

```bash
cd wp-ecommerce

# Start Docker containers
docker-compose up -d

# Install WooCommerce
docker compose run --rm cli wp plugin install woocommerce --activate

# Activate Flatsome Child Theme
docker compose run --rm cli wp theme activate flatsome-child

# View logs
docker-compose logs -f wordpress

# Access phpMyAdmin (if enabled)
docker-compose up -d phpmyadmin

# Access WordPress admin
http://localhost:8000/wp-admin

# Access WordPress site
http://localhost:8000

# Access database client
http://localhost:8080

# Access wp-cli
docker-compose run --rm cli wp ...

# Stop Docker containers
docker-compose down

# Stop Docker containers and remove volumes
docker-compose down -v

# View logs
docker-compose logs -f wordpress

# View logs for specific service
docker-compose logs -f wordpress
```



