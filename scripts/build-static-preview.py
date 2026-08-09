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

BANNER = """
<style id="cyma-static-preview-banner">
.cyma-static-banner{position:sticky;top:0;z-index:99999;background:#0b3d66;color:#fff;font:14px/1.4 system-ui,sans-serif;padding:10px 16px;text-align:center}
.cyma-static-banner a{color:#9ad0ff}
.cyma-static-banner strong{font-weight:700}
</style>
<div class="cyma-static-banner" role="note">
  <strong>Static GitHub Pages preview</strong> — not a live WordPress site.
  Production remains at <a href="https://cymasys.com/" rel="noopener">cymasys.com</a>.
  Forms, login, and CMS features will not work here.
</div>
"""


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

    if OUT.exists():
        shutil.rmtree(OUT)
    OUT.mkdir(parents=True)

    assets_out = OUT / "assets"
    assets_out.mkdir()
    for sub in ("css", "js"):
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
        html = re.sub(
            r"https?://(?:www\.)?cymasys\.com/wp-content/themes/cyma-prod(?:-v2)?/assets/(css|js)/([^\"?#]+)",
            lambda m: f"{prefix}assets/{m.group(1)}/{m.group(2)}",
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
        return html

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

    subprocess.run(["du", "-sh", str(OUT)], check=False)
    print(f"Built {len(pages)} pages into {OUT}")


if __name__ == "__main__":
    main()
