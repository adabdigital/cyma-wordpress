#!/usr/bin/env python3
"""Build a static GitHub Pages preview from .img-scan HTML snapshots.

GitHub Pages cannot run WordPress/PHP. This publishes browsable HTML only.
Production WordPress remains on cymasys.com / GoDaddy.
"""

from __future__ import annotations

import re
import shutil
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SRC = ROOT / ".img-scan"
OUT = ROOT / "docs"
THEME_ASSETS = ROOT / "wordpress" / "wp-content" / "themes" / "cyma-prod-v2" / "assets"
PRESERVE_PATHS = (
    Path("h1b-lca/index.html"),
    Path("htaccess-https.sample.md"),
)

# Snapshots still say .png/.jpg after the theme raster files were converted to WebP.
RASTER_ASSET_RE = re.compile(
    r"((?:(?:\.\./)*assets/(?:images|videos)/|\.\./(?:images|videos)/)[^\"'?#\s,)]+)\.(png|jpe?g)",
    re.I,
)


def rewrite_raster_to_webp(text: str, assets_root: Path) -> str:
    """Rewrite local PNG/JPEG asset URLs to .webp when that file exists."""

    def repl(match: re.Match[str]) -> str:
        rel = match.group(1)
        name = Path(rel).name
        kind = "videos" if "/videos/" in rel.replace("\\", "/") else "images"
        if (assets_root / kind / f"{name}.webp").is_file():
            return f"{rel}.webp"
        return match.group(0)

    return RASTER_ASSET_RE.sub(repl, text)


BANNER = """
<style id="cyma-static-preview-banner">
.cyma-static-banner{position:sticky;top:0;z-index:99999;background:#0b3d66;color:#fff;font:14px/1.4 system-ui,sans-serif;padding:10px 16px;text-align:center}
.cyma-static-banner a{color:#9ad0ff}
.cyma-static-banner strong{font-weight:700}
/* Snapshots may embed a blunt opacity:1 override; keep industry idle overlays hidden. */
.industry-list .div-block-1366{opacity:0!important;pointer-events:none}
.industry-list .mg-right-10px.height-520px:hover .div-block-1366,
.industry-list .mg-right-10px.height-520px:focus-within .div-block-1366{opacity:1!important;pointer-events:auto}
.industry-list .mg-right-10px.height-520px:hover .slider-content-block,
.industry-list .mg-right-10px.height-520px:focus-within .slider-content-block{opacity:0}
</style>
<div class="cyma-static-banner" role="note">
  <strong>Static GitHub Pages preview</strong> — not a live WordPress site.
  Production remains at <a href="https://cymasys.com/" rel="noopener">cymasys.com</a>.
  Forms, login, and CMS features will not work here.
</div>
"""

DICE_URL = "https://www.dice.com/jobs?filters.clientBrandNameFilter=Cyma+Systems+Inc"
INSIGHTS_NAV_KEY = 'data-link="a207e37db"'


def rewrite_dice_ctas(html: str) -> str:
    """Point legacy career CTA anchors at Dice in static snapshots."""

    def repl_anchor(match: re.Match[str]) -> str:
        anchor = match.group(0)
        if 'data-link="a2643837b"' not in anchor:
            return anchor
        if 'href="' not in anchor:
            return anchor
        anchor = re.sub(r'href="[^"]*"', f'href="{DICE_URL}"', anchor, count=1)
        if "target=" not in anchor:
            anchor = anchor.replace("<a ", '<a target="_blank" rel="noopener noreferrer" ', 1)
        elif "rel=" not in anchor:
            anchor = anchor.replace('target="_blank"', 'target="_blank" rel="noopener noreferrer"', 1)
        return anchor

    return re.sub(r"<a\b[^>]*>[\s\S]*?</a>", repl_anchor, html, flags=re.I)


def rewrite_insights_nav(html: str, slug: str) -> str:
    """Point Insights nav (a207e37db) at the hub, not article insights-2."""

    if slug == "insights":
        hub = "./"
    elif slug == "home":
        hub = "./insights/"
    else:
        hub = "../insights/"

    def repl_open(match: re.Match[str]) -> str:
        anchor = match.group(0)
        if INSIGHTS_NAV_KEY not in anchor:
            return anchor
        if 'href="' not in anchor:
            return anchor[:-1] + f' href="{hub}">'
        return re.sub(r'href="[^"]*"', f'href="{hub}"', anchor, count=1)

    return re.sub(r"<a\b[^>]*>", repl_open, html, flags=re.I)


def main() -> None:
    if not SRC.exists():
        raise SystemExit(f"Missing source snapshots: {SRC}")

    pages = {
        p.name[len("page_") : -len(".html")]: p
        for p in sorted(SRC.glob("page_*.html"))
    }
    if not pages:
        raise SystemExit(f"No page_*.html files in {SRC}")

    available = set(pages)

    preserved_files: dict[Path, str] = {}
    for rel_path in PRESERVE_PATHS:
        existing = OUT / rel_path
        if existing.exists():
            preserved_files[rel_path] = existing.read_text(encoding="utf-8", errors="replace")

    if OUT.exists():
        shutil.rmtree(OUT)
    OUT.mkdir(parents=True)

    assets_out = OUT / "assets"
    assets_out.mkdir()
    # CSS backgrounds (e.g. industry cards) resolve to ../images/* from assets/css/.
    # Copy images/videos too — css/js alone leaves those URLs as 404 on Pages.
    for sub in ("css", "js", "images", "videos", "docs"):
        src = THEME_ASSETS / sub
        if src.exists():
            shutil.copytree(src, assets_out / sub)

    def page_url(path: str, slug: str) -> str | None:
        path = path.split("?", 1)[0].split("#", 1)[0]
        if path in ("/", ""):
            target = "home"
        else:
            target = path.strip("/")
        if target not in available:
            return None
        if slug == "home":
            return "./" if target == "home" else f"./{target}/"
        if target == "home":
            return "../"
        if target == slug:
            return "./"
        return f"../{target}/"

    def rewrite_html(html: str, slug: str) -> str:
        prefix = "" if slug == "home" else "../"

        def repl_href(match: re.Match[str]) -> str:
            url = match.group(1)
            m2 = re.match(r"^https?://(?:www\.)?cymasys\.com(/[^\"']*)$", url)
            if m2:
                local = page_url(m2.group(1), slug)
                if local is not None:
                    return f'href="{local}"'
            m3 = re.match(r"^(/[a-z0-9\-_/]*)$", url)
            if m3 and not url.startswith("/wp-"):
                local = page_url(url, slug)
                if local is not None:
                    return f'href="{local}"'
            return match.group(0)

        html = re.sub(r'href="([^"]+)"', repl_href, html)
        html = rewrite_dice_ctas(html)
        html = rewrite_insights_nav(html, slug)
        # Rewrite theme asset URLs (css/js/images/videos) to the local docs/assets copy.
        # Snapshots may reference cyma-prod or cyma-prod-v2; both map to theme assets.
        html = re.sub(
            r"https?://(?:www\.)?cymasys\.com/wp-content/themes/cyma-prod(?:-v2)?/assets/(css|js|images|videos|docs)/([^\"'?#]+)",
            lambda m: f"{prefix}assets/{m.group(1)}/{m.group(2)}",
            html,
        )
        html = re.sub(
            r"/wp-content/themes/cyma-prod(?:-v2)?/assets/(css|js|images|videos|docs)/([^\"'?#]+)",
            lambda m: f"{prefix}assets/{m.group(1)}/{m.group(2)}",
            html,
        )
        # Neutralize legacy blunt opacity override baked into page head snapshots.
        html = re.sub(
            r"\[data-w-id\],\s*\[style\*=\"opacity:0\"\]\s*\{\s*opacity:\s*1\s*!important;\s*\}",
            "[data-w-id]:not(section.cyma-reveal):not(.mg-right-10px):not(.div-block-1366),"
            '[style*="opacity:0"]:not(section.cyma-reveal):not(.div-block-1366):not(.mg-right-10px)'
            "{opacity:1!important;}",
            html,
        )
        html = re.sub(
            r"(<body[^>]*>)",
            r"\1\n" + BANNER,
            html,
            count=1,
            flags=re.I,
        )
        html = re.sub(
            r"<title>(.*?)</title>",
            r"<title>\1 | CYMA Static Preview</title>",
            html,
            count=1,
            flags=re.I | re.S,
        )
        if '<meta name="robots"' not in html.lower():
            html = html.replace(
                "</head>",
                '<meta name="robots" content="noindex,nofollow">\n</head>',
                1,
            )
        html = rewrite_raster_to_webp(html, assets_out)
        return html

    css_dir = assets_out / "css"
    if css_dir.exists():
        for css in css_dir.glob("*.css"):
            rewritten = rewrite_raster_to_webp(
                css.read_text(encoding="utf-8", errors="replace"),
                assets_out,
            )
            css.write_text(rewritten, encoding="utf-8")

    for slug, src in pages.items():
        html = rewrite_html(src.read_text(encoding="utf-8", errors="replace"), slug)
        if slug == "home":
            dest = OUT / "index.html"
        else:
            dest = OUT / slug / "index.html"
            dest.parent.mkdir(parents=True, exist_ok=True)
        dest.write_text(html, encoding="utf-8")

    (OUT / "README.md").write_text(
        "# CYMA static preview\n\n"
        "This folder is a **static HTML snapshot** for GitHub Pages.\n\n"
        "It is **not** WordPress. Production: https://cymasys.com/\n",
        encoding="utf-8",
    )
    (OUT / ".nojekyll").write_text("", encoding="utf-8")
    (OUT / "404.html").write_text(
        "<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Not found</title>"
        '<meta http-equiv="refresh" content="0;url=./"></head>'
        '<body><p>Page not in static preview. <a href="./">Home</a></p></body></html>\n',
        encoding="utf-8",
    )

    for rel_path, content in preserved_files.items():
        dest = OUT / rel_path
        dest.parent.mkdir(parents=True, exist_ok=True)
        dest.write_text(content, encoding="utf-8")

    subprocess.run(["du", "-sh", str(OUT)], check=False)
    print(f"Built {len(pages)} pages into {OUT}")


if __name__ == "__main__":
    main()
