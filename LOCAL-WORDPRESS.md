# Running CYMA WordPress Locally

This project runs WordPress in Docker. The custom theme is **cyma-708003**, converted from Webflow. Page content is loaded from JSON files in the theme — no Udesly plugin is required.

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (includes Docker Compose)
- A free port on your machine (default: **8081**)
- Git (to clone the repo)

## Get the code

```bash
git clone https://github.com/adeemad/cyma-wordpress.git
cd cyma-wordpress
```

## Quick start

From the project root:

```bash
docker compose up -d
```

Wait for both containers to start (about 30–60 seconds on first run), then open:

| URL | Purpose |
|-----|---------|
| http://localhost:8081 | Public site |
| http://localhost:8081/wp-admin | WordPress admin |

Check containers are running:

```bash
docker compose ps
```

You should see `cyma-wordpress` and `cyma-mysql` with status **Up**.

### Stop and start

```bash
# Stop containers (keeps database data)
docker compose down

# Start again
docker compose up -d

# View logs
docker compose logs -f wordpress
```

## Project layout

```
cyma-wordpress/
├── docker-compose.yml          # WordPress + MySQL services
├── wordpress/                  # WordPress install (mounted into container)
│   ├── wp-config.php           # Includes preview-tunnel URL handling
│   └── wp-content/themes/cyma-708003/   # Active theme (edit this)
│       ├── _data/frontend-editor/       # Page content (JSON)
│       ├── assets/                      # CSS, JS, images, videos
│       └── template-parts/              # Page templates
└── LOCAL-WORDPRESS.md          # This file
```

The repo root also contains theme source files that mirror the active theme. **When developing locally, edit files under `wordpress/wp-content/themes/cyma-708003/`** — that is what Docker serves.

After editing root-level theme copies, sync them into the active theme if needed:

```bash
THEME="wordpress/wp-content/themes/cyma-708003"
cp header.php footer.php functions.php "$THEME/"
cp -r assets/css assets/js "$THEME/assets/" 2>/dev/null || true
```

## First-time WordPress setup

If this is a fresh install, complete the WordPress setup wizard at http://localhost:8081:

1. Choose language
2. Set site title, admin username, password, and email
3. Log in to the admin dashboard

### Activate the theme

1. Go to **Appearance → Themes**
2. Activate **cyma-708003**

### Set the homepage

1. Go to **Settings → Reading**
2. Select **A static page**
3. Set **Homepage** to your home page (create one first if needed — see below)
4. Save changes

WordPress will use `front-page.php` for the homepage when a static front page is configured.

## Create required pages

Navigation links and buttons resolve template slugs like `page-about-us` to WordPress pages with slug `about-us`. Each page template in the theme needs a matching published page.

### Option A: Run the import script (basic pages)

```bash
docker exec cyma-wordpress php /var/www/html/wp-content/themes/cyma-708003/import-pages.php
```

This creates a subset of core pages. Extend `import-pages.php` if you need additional slugs.

### Option B: Create all pages from JSON data

Run this once inside the WordPress container to create a page for every JSON file in `_data/frontend-editor/`:

```bash
docker exec cyma-wordpress php -r "
require '/var/www/html/wp-load.php';
\$dir = '/var/www/html/wp-content/themes/cyma-708003/_data/frontend-editor';
foreach (glob(\$dir . '/page-*.json') as \$file) {
    \$key = basename(\$file, '.json');
    \$slug = preg_replace('/^page-/', '', \$key);
    \$title = ucwords(str_replace('-', ' ', \$slug));
    if (!get_page_by_path(\$slug)) {
        \$id = wp_insert_post([
            'post_title'   => \$title,
            'post_name'    => \$slug,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        ]);
        echo \"Created: \$title (\$slug) ID \$id\n\";
    } else {
        echo \"Exists: \$slug\n\";
    }
}
"
```

### Page slug reference

Theme templates use JSON keys like `page-about-us`. WordPress page slugs should be the part after `page-`:

| Template / JSON key | WordPress page slug |
|---------------------|---------------------|
| `page-about-us` | `about-us` |
| `page-business-solutions` | `business-solutions` |
| `page-contact-us` | `contact-us` |
| `page-technology-services` | `technology-services` |
| `page-job-seekers` | `job-seekers` |

See `_data/frontend-editor/` for the full list of available pages.

### Import careers / job listings

The **Explore Careers** page (`/explore-careers/`) and individual job posts are not created by the basic page import. Run:

```bash
docker exec cyma-wordpress php /var/www/html/wp-content/themes/cyma-708003/import-careers.php
```

This creates the `explore-careers` page and imports job listings from `_data/data.json` (e.g. `/explore-careers/data-engineer/`).

## Editing page content in WordPress admin

Each CYMA page is **fully CMS-backed**: the entire page HTML lives in the WordPress page editor (classic editor), not as individual field controls.

1. Open **http://localhost:8081/wp-admin**
2. Go to **Pages → All Pages** and edit any page
3. Edit the full page content in the main editor, then click **Update**

Optional: **Tools → CYMA Content Seed** re-imports design HTML into WordPress (check “Overwrite” to replace existing CMS content).

Job listings under **Careers** remain separate custom posts. On Explore Careers, the shortcode `[cyma_open_roles]` renders the live jobs list.

## Share a public preview link (optional)

To share the local site with someone else without deploying, use a Cloudflare quick tunnel.

### 1. Install cloudflared (macOS)

```bash
brew install cloudflared
```

### 2. Start the tunnel

Make sure Docker is running (`docker compose up -d`), then:

```bash
cloudflared tunnel --url http://localhost:8081
```

Copy the `https://*.trycloudflare.com` URL from the terminal output.

### 3. How it works

- `wordpress/wp-config.php` detects `trycloudflare.com` requests and sets `WP_HOME` / `WP_SITEURL` automatically so WordPress does not redirect to `localhost:8081`.
- The preview link only works while **Docker** and **cloudflared** are running on your machine.
- Each time you restart cloudflared, you get a **new URL**.

## Docker services

| Service | Container | Port | Notes |
|---------|-----------|------|-------|
| WordPress | `cyma-wordpress` | 8081 → 80 | PHP + Apache |
| MySQL | `cyma-mysql` | (internal) | Data persisted in Docker volume `db_data` |

### Database credentials (local only)

| Setting | Value |
|---------|-------|
| Database | `cyma_wordpress` |
| User | `wordpress` |
| Password | `wordpress` |
| Root password | `rootpassword` |

These are defined in `docker-compose.yml` and are suitable for local development only. Do not use them in production.

## Theme development

### How content works

- **`load_page_data('page-slug')`** loads JSON from `_data/frontend-editor/{slug}.json`
- **`_u('key', 'text')`** returns text, links, or other values from that JSON
- **`cyma_get_image(_u('key', 'img'))`** resolves image paths to theme asset URLs
- **`cyma_resolve_link()`** maps template slugs to WordPress permalinks

### Key files

| File | Role |
|------|------|
| `functions.php` | Theme setup, JSON helpers, asset/link resolution |
| `front-page.php` | Homepage template |
| `page-{slug}.php` | Individual page templates |
| `template-parts/content/` | Page HTML/content partials |
| `template-parts/footer/` | Page-specific scripts (nav, Webflow init) |
| `assets/css/animations.css` | Section scroll-reveal and button hover effects |
| `assets/js/section-animations.js` | Scroll-reveal logic |
| `assets/js/webflow.js` | Webflow interactions (sliders, nav, dropdowns) |

Changes to theme files are reflected immediately on refresh — no container rebuild is needed because the `wordpress/` directory is bind-mounted.

Hard-refresh the browser (Cmd+Shift+R) after CSS/JS changes to bypass cache.

### WP-CLI (optional)

Run WordPress CLI commands inside the container:

```bash
docker exec cyma-wordpress wp --info --allow-root
docker exec cyma-wordpress wp theme list --allow-root
docker exec cyma-wordpress wp option get siteurl --allow-root
```

## Troubleshooting

### Port 8081 already in use

Change the host port in `docker-compose.yml`:

```yaml
ports:
  - "8082:80"   # use 8082 instead of 8081
```

Then run `docker compose up -d` and visit http://localhost:8082.

### Site redirects to wrong URL

If WordPress was installed with a different URL, update it via WP-CLI:

```bash
docker exec cyma-wordpress wp option update home 'http://localhost:8081' --allow-root
docker exec cyma-wordpress wp option update siteurl 'http://localhost:8081' --allow-root
```

### Preview tunnel redirects to localhost:8081

Ensure `wordpress/wp-config.php` includes the `trycloudflare.com` block (see **Share a public preview link** above). Restart the tunnel after any wp-config change.

### Buttons or links go to `#` or 404

- Confirm the target page exists in **Pages** with the correct slug (e.g. `about-us`, not `page-about-us`)
- Check that `load_page_data()` is called in the page template PHP file

### Images or videos not loading

Assets live under `assets/` in the theme (e.g. `assets/images/`, `assets/videos/`). Paths in JSON are resolved by `cyma_resolve_asset()` in `functions.php`.

### Mobile menu or Webflow interactions broken

The theme enqueues jQuery and loads `webflow.js` after `wp_footer()`. If interactions fail:

1. Open the browser console and check for JavaScript errors
2. Confirm jQuery loads before `webflow.js`
3. Hard-refresh to clear cached CSS/JS

### Header or navigation looks wrong

The homepage nav lives inside `.section-3` and uses Webflow's `w-nav` component. Custom animation CSS must not override Webflow nav layout (display, position, transforms on `.w-nav-menu`).

### Reset the database

This deletes all WordPress data and starts fresh:

```bash
docker compose down -v
docker compose up -d
```

You will need to run the WordPress install wizard and theme setup again.

### Container won't start

```bash
docker compose ps
docker compose logs wordpress
docker compose logs db
```

Ensure Docker Desktop is running and you have enough disk space for the MySQL volume.

## Production note

This Docker setup is for **local development**. For production, use proper secrets management, HTTPS, strong database passwords, and a hosting environment suited to WordPress (managed WP, VPS, etc.).
