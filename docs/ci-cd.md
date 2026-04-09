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
  - Connects to the VPS over SSH.
  - Pulls the latest Git branch on the server.
  - Runs Composer install, frontend build, migrations, cache refresh, and permission fixes on the server.
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
- Git installed and the repository already cloned at the deploy path
- The deploy user must be able to `git fetch` and `git pull` from the repository on the server
- Writable Laravel directories such as `storage` and `bootstrap/cache`
- Database credentials already configured in `.env`
- Node.js and npm installed on the server, because frontend assets are built during deployment
- A valid `.env` file already present in the deploy path, or `.env.example` if you want the workflow to create one on first deploy

## Recommended first-time setup

1. Initialize or reconnect this project to a Git remote.
2. Push the code to GitHub.
3. Add the required repository secrets.
4. Clone the repository on the VPS into `VPS_PATH`.
5. Make sure the deployment user can write to `VPS_PATH` and can pull the `test` branch there.
6. Push to `test` or trigger the deploy workflow manually from the Actions tab.

## Notes

- The deploy workflow intentionally does not overwrite `.env` if it already exists.
- The workflow assumes the server is the build host, so deployment time depends on server Composer and npm performance.
- If your deploy branch is not `test`, update `.github/workflows/deploy.yml`.
