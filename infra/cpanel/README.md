# Phase O5 — Automatic release for the EXISTING live cPanel installation

**Recommended for the currently working carfolkltd.com:** use the cPanel pull-and-Cron integration added in .github/workflows/cpanel-release.yml and infra/cpanel/deploy.sh. It targets the actual existing paths /home2/carfolkl/carfolk-api and /home2/carfolkl/public_html, and needs no external SSH access or Node.js on cPanel.

**Activation status: not yet enabled on HostPinnacle.** Source automation is committed, but deployment will not occur until you run the private server setup below and deliberately opt in. First verify a restorable, securely stored PostgreSQL and vehicle-image backup.

GitHub workflow: every main push runs React tests/build and PostgreSQL-backed Laravel tests; only green results publish cpanel-release. The cPanel job checks that branch for releases, stages Composer dependencies with PHP 8.4, blocks schema-changing releases until the database is backed up and migrations are approved, preserves .env and uploads, deploys safely, and checks HTTPS endpoints.

From cPanel Terminal:

~~~bash
cd /home2/carfolkl/carfolk-repo
git pull --ff-only origin main
command -v flock rsync tar git composer curl
mkdir -p /home2/carfolkl/.carfolk-deploy
install -m 700 infra/cpanel/deploy.sh /home2/carfolkl/carfolk-deploy.sh
bash -n /home2/carfolkl/carfolk-deploy.sh
~~~

When the GitHub Actions "Carfolk - cPanel Release" run has passed, cpanel-release exists, and backups are verified, do the **first deployment manually**:

~~~bash
touch /home2/carfolkl/.carfolk-deploy/enabled
bash /home2/carfolkl/carfolk-deploy.sh
~~~

After a successful first run, open cPanel → Cron Jobs, select **Every five minutes** (*/5 * * * *), and enter this exact command:

~~~bash
/bin/bash /home2/carfolkl/carfolk-deploy.sh >> /home2/carfolkl/.carfolk-deploy/cron.log 2>&1
~~~

To pause automatic releases immediately:

~~~bash
rm /home2/carfolkl/.carfolk-deploy/enabled
~~~

Deployment logs and code backups are stored privately under /home2/carfolkl/.carfolk-deploy. Production Laravel data and photos are never replaced, and database migrations are never automated. This shared-hosting strategy provides best-effort rollback, not atomic zero-downtime release switching. Confirm a good HTTPS smoke test and admin login before enabling Cron.

**IMPORTANT: alternative integration below is NOT the same system.** The older protected SSH workflow (.github/workflows/hostpinnacle-cpanel-deploy.yml) assumes a separate ~/carfolk/shared + symlink release layout and dedicated GitHub SSH secrets. It is not configured for the already-running /home2/carfolkl/carfolk-api installation. Keep CARFOLK_DEPLOY_ENABLED disabled and do **not** enable that SSH workflow or run its deploy-release.sh alongside this Cron approach. Choose only one deployment strategy.

---

# HostPinnacle Silver cPanel — Carfolk single-host CI/CD

**Status: PREPARED, DISABLED, NOT DEPLOYED.** This replaces the repository's Nginx/VPS deployment method for an Apache/cPanel account. Existing GitHub Pages remains a noindex preview.

## Proposed production layout

- https://carfolkltd.com/ serves the compiled React website.
- https://carfolkltd.com/manage serves Laravel's authenticated dealership administration.
- https://carfolkltd.com/api/v1 serves Laravel's API, same origin as React.
- https://carfolkltd.com/storage serves only public inventory photographs.
- PostgreSQL remains on HostPinnacle, never reachable from the public internet.

Private directories live under /home/<cpanel-user>/carfolk:
- shared/.env: production secrets, 0600
- shared/.pgpass: restricted PostgreSQL backup authentication, 0600
- shared/.deploy-approved: owner-created release approval marker
- shared/storage: durable logs, sessions and uploaded vehicle photos
- shared/backups: private PostgreSQL snapshots; also configure encrypted offsite backups and test restores
- releases/<commit-sha>/apps/api: private Laravel project and Composer dependencies
- current: symlink to the active release

The only files in ~/public_html should be compiled frontend assets, approved Laravel public assets, protected PHP front controller, Apache .htaccess, and public upload symlink. **Never place Laravel .env, vendor, full source, private logs or database backups inside public_html.**

## Hosting checks in cPanel

1. MultiPHP Manager: set PHP 8.3 or 8.4; 8.4 preferred. Confirm the site uses it.
2. Terminal: verify the command-line PHP matches the required runtime and includes pdo_pgsql, mbstring, fileinfo, openssl, gd and xml.
3. Terminal: check composer --version; pg_dump --version; pg_restore --version; command -v rsync.
4. PostgreSQL Databases: create a dedicated Carfolk PostgreSQL database and user (cPanel often prefixes both with the account username); grant app permissions.
5. SSL/TLS Status: enable valid HTTPS on carfolkltd.com and www; select the correct canonical domain.
6. SSH Access: verify that connecting from an external machine works; in-browser Terminal alone is not external SSH permission. Confirm SSH hostname and port with HostPinnacle.

Run these non-secret diagnostics in the cPanel Terminal and review output:

    whoami
    pwd
    php -v
    php -m | grep -Ei 'pdo_pgsql|pgsql|mbstring|openssl|gd|fileinfo|xml'
    composer --version
    pg_dump --version
    command -v rsync
    ls -ld ~/public_html

cPanel may have a different PHP version for CLI than for Apache. If needed, store an absolute compatible PHP binary path in ~/carfolk/shared/php-bin.

## One-time operator setup

- Use only real owner-approved domain, inventory, business contacts and legal policy. **Do not publish synthetic test inventory.**
- Set ~/carfolk/shared/.env from apps/api/.env.production.example, outside the repository, chmod 600; generate one unique APP_KEY and configure actual PostgreSQL credentials. Avoid putting secrets into shell history.
- Initial safe flags: APP_ENV=production, APP_DEBUG=false, SESSION_SECURE_COOKIE=true, APP_URL=https://carfolkltd.com, CARFOLK_PUBLIC_URL=https://carfolkltd.com, DB_CONNECTION=pgsql, CARFOLK_LOCAL_ENQUIRIES_ENABLED=false, CARFOLK_LEADS_ENABLED=false, CARFOLK_SEO_ENABLED=false.
- Create a valid PostgreSQL password file at shared/.pgpass, chmod 600. Test pg_dump and restore to a **separate** scratch database. Establish encrypted offsite backups.
- Back up existing ~/public_html completely BEFORE replacing any files. If it contains a previous site, move it to a protected separate location only with authorization. The automated first deploy refuses to overwrite unrecognized index.html, index.php or asset folders.
- Only when the domain, HTTPS, server, backups and owner approval are verified: create private shared/.deploy-approved with exactly one line: I_APPROVE_CARFOLK_DEPLOY

## GitHub configuration

Repository Settings > Environments > add **carfolk-production**.

Add these *environment secrets* (not in code):
- CARFOLK_SSH_HOST: HostPinnacle-provided SSH hostname
- CARFOLK_SSH_USER: actual account SSH username
- CARFOLK_SSH_PORT: HostPinnacle-provided SSH port
- CARFOLK_SSH_KEY: new dedicated deployment private SSH key, created on trusted operator computer
- CARFOLK_SSH_KNOWN_HOSTS: SSH host key verified out-of-band with HostPinnacle

Authorize ONLY the corresponding public key in cPanel > SSH Access > Manage SSH Keys. Do not use root access, password authentication or disable SSH host verification.

Repository Settings > Secrets and variables > Actions > Variables:
- CARFOLK_DEPLOY_ENABLED = disabled  (initially)
- After successful first manual release and verified owner sign-off: CARFOLK_DEPLOY_ENABLED = enabled

The new workflow file is .github/workflows/hostpinnacle-cpanel-deploy.yml. It runs on completed main-branch browser/PostgreSQL integration CI, checks that Frontend CI, Backend CI and Browser Integration CI all passed on the same CURRENT main commit, builds the frontend with indexing disabled, and sends a private release over verified SSH. An optional manual workflow_dispatch trigger uses the same checks and requires the same deploy-enabled flag. Stale commits are refused; releases are serialized.

## Deployment precautions

Deployment is fail-closed if critical PHP, Composer, PostgreSQL, backup, SSH, permissions or approval checks fail. Before migration, it takes a local restricted PostgreSQL dump and confirms pg_restore can read it. It preserves the private .env and persistent photo store, adds hashed assets without deleting old bundles, updates the Apache web root and runs HTTPS smoke checks (/up, /api/v1/site, /manage/login). On failed smoke tests it attempts a code-only rollback when an earlier release exists; migrations are not reversed.

**Do not enable automatic updates until the first live cPanel release is manually certified.** Shared hosting may disallow certain shell commands, symlinks, Apache rewrite flags or PostgreSQL client binaries even when UI tools exist. Verify actual privileges and test the live environment with HostPinnacle support.

Lead collection and SEO remain DISABLED until separate signed approval and actual production audit, real inventory, verified business details, approved privacy notice, protected backups and a successful live buyer-to-admin test.
