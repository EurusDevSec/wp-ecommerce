---
name: github_actions_deploy
description: Designing automated CI/CD pipelines to build and deploy Flatsome child theme changes via Rsync to VPS over SSH
---

# GitHub Actions Deployment Skill

Automating deployment workflow using GitHub Actions to sync code changes directly from repository to target DigitalOcean VPS server.

## 1. Pipeline Triggering Strategy
Run deployments automatically only on pushes to specific branch configurations (like `main` or `staging`).

```yaml
on:
  push:
    branches:
      - main
    paths:
      - 'src/wp-content/themes/flatsome-child/**'
```

## 2. YAML Deployment Pipeline Template
Create a configuration file at `.github/workflows/deploy.yml` with the following structure:

```yaml
name: Deploy Theme to VPS

on:
  push:
    branches:
      - main

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
    # 1. Checkout repository code
    - name: Checkout Code
      uses: actions/checkout@v3

    # 2. Set up SSH agent and load deployment key
    - name: Install SSH Key
      uses: shimataro/ssh-key-action@v2
      with:
        key: ${{ secrets.VPS_SSH_PRIVATE_KEY }}
        known_hosts: ${{ secrets.VPS_SSH_KNOWN_HOSTS }}

    # 3. Synchronize local files with remote theme directory using Rsync
    - name: Sync Child Theme to VPS
      run: |
        rsync -avz --delete \
          --exclude '.git*' \
          --exclude '.github*' \
          --exclude 'node_modules' \
          ./src/wp-content/themes/flatsome-child/ \
          root@167.172.91.249:/var/www/wp-ecommerce/src/wp-content/themes/flatsome-child/

    # 4. Run post-deploy operations (reset permissions, flush cache)
    - name: Post-Deploy Commands
      run: |
        ssh root@167.172.91.249 "
          chown -R www-data:www-data /var/www/wp-ecommerce/src/wp-content/themes/flatsome-child/ &&
          redis-cli flushall &&
          wp cache flush --path=/var/www/wp-ecommerce/src
        "
```

## 3. GitHub Secrets Management
Before activating the pipeline, configure variables in the GitHub repository Settings → Secrets and variables → Actions:
1.  **`VPS_SSH_PRIVATE_KEY`**: The content of the private key (`id_rsa` or similar) authorized to access the VPS.
2.  **`VPS_SSH_KNOWN_HOSTS`**: The output of running `ssh-keyscan -t rsa 167.172.91.249` locally, to prevent pipeline failures from server host verification warnings.
