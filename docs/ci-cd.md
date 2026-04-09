# CI/CD Setup

This project is configured for GitHub Actions CI/CD.

## What the workflows do

- `.github/workflows/ci.yml`
  - Runs on every push and pull request.
  - Installs PHP and Node dependencies.
  - Prepares a SQLite test database.
  - Runs `php artisan test`.
  - Runs `npm run build`.

- `.github/workflows/deploy.yml`
  - Runs on pushes to `test` and on manual dispatch.
  - Validates the app in GitHub Actions before deployment.
  - Builds frontend assets in GitHub Actions.
  - Connects to the VPS over SSH.
  - Uploads the application to the VPS with `rsync`.
  - Runs Composer install, migrations, cache refresh, and permission fixes on the server.
  - Ensures the Laravel scheduler cron entry exists.

## Required GitHub secrets

Add these repository secrets in GitHub:

- `VPS_HOST`: deployment server hostname or IP
- `VPS_USER`: SSH user with write access to the deploy path
- `SSH_PRIVATE_KEY`: private key for that SSH user
- `VPS_PATH`: absolute path to the app on the server, for example `/var/www/erp.dragonbay.org`

## Server requirements

The target server needs:

- PHP 8.1 or newer
- Composer installed and available in `PATH`
- Writable Laravel directories such as `storage` and `bootstrap/cache`
- Database credentials already configured in `.env`
- `rsync` available on the server
- A valid `.env` file already present in the deploy path, or `.env.example` if you want the workflow to create one on first deploy

## Recommended first-time setup

1. Initialize or reconnect this project to a Git remote.
2. Push the code to GitHub.
3. Add the required repository secrets.
4. Make sure the deployment user can write to `VPS_PATH`.
5. Make sure the VPS has PHP, Composer, and `rsync` installed.
6. Push to `test` or trigger the deploy workflow manually from the Actions tab.

## Notes

- The deploy workflow intentionally does not overwrite `.env` if it already exists.
- The VPS does not need a `.git` directory for deployment.
- Frontend assets are built in GitHub Actions, so Node.js is not required on the VPS.
- If your deploy branch is not `test`, update `.github/workflows/deploy.yml`.
