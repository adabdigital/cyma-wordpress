# CYMA WordPress

WordPress theme converted from Webflow for CYMA Systems. Runs locally with Docker.

## Quick start

```bash
git clone https://github.com/adeemad/cyma-wordpress.git
cd cyma-wordpress
docker compose up -d
```

Open **http://localhost:8081**

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
