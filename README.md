# PSM — Procurement & Supplier Management

Monorepo for the PSM platform.

| Directory | Description |
|---|---|
| `coops-app/` | Laravel backend (PHP 8.3, MySQL, Passport) |
| `coops-ui/` | Vue frontend (Vite) — build output is served by the backend |
| `coops-arbk-scraper/` | Node.js ARBK business-registry scraper microservice (Docker) |
| `coops-installer/` | Server provisioning script + web setup wizard |
| `docs/` | Release notes and test plan |

## Installing on a new server

Requirements: fresh Ubuntu 22.04 / 24.04 or Debian 12, root access, a domain pointed at the server.

```bash
wget -O coops-install.sh https://raw.githubusercontent.com/agonmaloku-bit/psm/main/coops-installer/coops-install.sh
chmod +x coops-install.sh
sudo ./coops-install.sh install \
    --domain psm.yourcompany.com \
    --repo-app  https://github.com/agonmaloku-bit/psm.git --repo-app-subdir  coops-app \
    --repo-ui   https://github.com/agonmaloku-bit/psm.git --repo-ui-subdir   coops-ui \
    --repo-arbk https://github.com/agonmaloku-bit/psm.git --repo-arbk-subdir coops-arbk-scraper
```

If the repository is private, use a token in the URL: `https://<TOKEN>@github.com/agonmaloku-bit/psm.git`.

When the script finishes, open `https://psm.yourcompany.com/install/` in a browser and complete the setup wizard (database, admin account, branding).

### Updating an existing install

```bash
sudo ./coops-install.sh update
```

See [coops-installer/README.md](coops-installer/README.md) for all flags and details.
