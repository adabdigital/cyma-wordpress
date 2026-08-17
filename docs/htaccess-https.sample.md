# Optional production HTTPS redirects (Apache)

Do **not** copy this into local Docker. Local WordPress is meant to stay on
`http://localhost:8081` (browsers will show "Not secure" — that is expected).

## What production already does (checked Aug 2026)

| Check | Result |
|-------|--------|
| `https://cymasys.com` cert | Valid GoDaddy leaf through Dec 6, 2026; SAN includes apex + `www` |
| HTTP → HTTPS | Sucuri returns `301` to `https://…` |
| Mixed content on homepage | None observed (all asset URLs `https://`) |
| CSP | `upgrade-insecure-requests` present |
| HSTS | **Not** sent by Sucuri today (GitHub Pages does send HSTS) |

Theme helpers in `cyma-prod-v2/inc/https.php` also force HTTPS URL schemes and
redirect cleartext requests on `cymasys.com` / `www.cymasys.com` only — never
on localhost / `127.0.0.1` / `*.local`.

## Optional `.htaccess` (origin Apache)

Only needed if the edge is **not** already forcing HTTPS. Place **above** the
WordPress rewrite block:

```apache
# Force HTTPS (production only — skip localhost)
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{HTTPS} !=on
RewriteCond %{HTTP:X-Forwarded-Proto} !https
RewriteCond %{HTTP_HOST} !^localhost [NC]
RewriteCond %{HTTP_HOST} !^127\.0\.0\.1 [NC]
RewriteCond %{HTTP_HOST} !\.local$ [NC]
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

## Hosting checklist (Sucuri / GoDaddy)

Do these in the control panels — not in the theme:

1. Confirm SSL mode is **Full** (or Full Strict) at Sucuri, not Flexible.
2. Keep the GoDaddy cert renewed before Dec 2026; watch chain completeness.
3. Set WordPress **Settings → General** Address fields to `https://cymasys.com`.
4. After cert monitoring is reliable, enable **HSTS** at Sucuri (start with a
   short `max-age`, then raise). Do **not** enable HSTS while the cert/chain
   is broken — browsers will hard-fail.
5. Prefer apex canonicalization (`www` → apex) at the edge; WordPress already
   301s `https://www` → `https://cymasys.com/`.
