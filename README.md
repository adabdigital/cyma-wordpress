# CYMA WordPress

WordPress theme converted from Webflow for CYMA Systems. Runs locally with Docker.

> **GitHub Pages:** https://adabdigital.github.io/cyma-wordpress/  
> Pages only serves static files and cannot run WordPress/PHP/MySQL. This repo publishes a **static HTML preview** (from `.img-scan` snapshots) via GitHub Actions. Production WordPress stays on [cymasys.com](https://cymasys.com/). The GitHub repo is **public** because GitHub Free org plans only support Pages on public repositories.

## Quick start

```bash
git clone https://github.com/adabdigital/cyma-wordpress.git
cd cyma-wordpress
docker compose up -d
```

Open **http://localhost:8081**

Rebuild the static Pages preview locally:

```bash
python3 scripts/build-static-preview.py
```

## Edit content in WordPress

1. Open **http://localhost:8081/wp-admin**
2. Go to **Pages → All Pages** and edit any page
3. Edit the **entire page** in the main content editor, then **Update**

Optional re-import from design files: **Tools → CYMA Content Seed**

## Full local setup

See **[LOCAL-WORDPRESS.md](./LOCAL-WORDPRESS.md)** for:

- First-time WordPress and theme setup
- Creating pages from JSON content
- Theme development workflow
- Editing page content from WP admin
- Public preview links (Cloudflare tunnel)
- Troubleshooting
