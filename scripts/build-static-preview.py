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
LIVE_NEWS_SCRIPT = """
<script>
(function () {
    var feedUrl = 'https://api.rss2json.com/v1/api.json?rss_url=' + encodeURIComponent('https://feeds.bbci.co.uk/news/technology/rss.xml');
    var terms = /\\b(ai|artificial intelligence|cloud|cybersecurity|data|devops|machine learning|software|technology|tech)\\b/i;
    var selectors = '.div-block-1106-copy-js, .slider-30 .w-slider-mask';
    var escapeHtml = function (value) { return String(value).replace(/[&<>'"]/g, function (character) { return {'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[character]; }); };
    var render = function (items) {
        var cards = items.filter(function (item) { return terms.test(item.title + ' ' + item.description); }).slice(0, 3).map(function (item) {
            var date = item.pubDate ? new Date(item.pubDate).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) : '';
            return '<article class="div-block-1103"><div class="div-block-1104"><div class="text-block-449">News</div><div class="text-block-450">' + escapeHtml(date) + '</div></div><h3 class="heading-17">' + escapeHtml(item.title) + '</h3><div class="text-block-453">' + escapeHtml(item.description || '') + '</div><a class="div-block-1105" href="' + escapeHtml(item.link) + '" target="_blank" rel="noopener noreferrer"><span class="text-block-454">Read More</span><span aria-hidden="true">&#8599;</span></a></article>';
        }).join('');
        if (!cards) return;
        document.querySelectorAll(selectors).forEach(function (container) { container.innerHTML = cards; });
    };
    var fallbackItems = [{title:'Latest BBC Technology News', description:'Read the latest technology news from BBC.', link:'https://www.bbc.com/news/technology'}];
    document.querySelectorAll(selectors).forEach(function (container) { container.innerHTML = '<p>Loading technology news...</p>'; });
    var timeout = new Promise(function (_, reject) { setTimeout(function () { reject(new Error('Feed timeout')); }, 5000); });
    Promise.race([fetch(feedUrl).then(function (response) { return response.json(); }), timeout]).then(function (data) { if (data.items) render(data.items); else render(fallbackItems); }).catch(function () { render(fallbackItems); });
}());
</script>
"""


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


def rewrite_legal_nav(html: str, slug: str) -> str:
    """Keep Legal dropdown links inside the static GitHub Pages site."""

    prefix = "" if slug == "home" else "../"
    html = html.replace(
        'href="https://cymasys.com/notice-of-filing/"',
        f'href="{prefix}notice-of-filing/"',
    )
    html = html.replace(
        'href="https://cymasys.com/h1b-lca/"',
        f'href="{prefix}h1b-lca/"',
    )
    return html


CASE_STUDIES_STATIC = [
    ("Automotive", "Smart Grid Modernization for an Energy Provider", "https://cymasys.com/case-studies/case-study-2/"),
    ("Energy", "Streamlining Vendor Data", "https://cymasys.com/case-studies/case-study-3/"),
    ("Banking &amp; Financial Services", "Enhancing Data &amp; IT in Healthcare", "https://cymasys.com/case-studies/case-study-4/"),
    ("Manufacturing", "Upgrading Manufacturing Systems to Enable Smart, Scalable Operations", "https://cymasys.com/case-studies/case-studies-5/"),
    ("Retail", "Enhancing Retail Platforms to Deliver Seamless, Data-Driven Customer Experiences", "https://cymasys.com/case-studies/case-study-6/"),
    ("Telecommunication", "Scaling Telecommunication Platforms to Support High-Volume, Always-On Services", "https://cymasys.com/case-studies/case-study-7/"),
    ("Public Sector", "Delivering Secure, Scalable Digital Services for the Public Sector", "https://cymasys.com/case-studies/case-study-8/"),
    ("Insurance", "Building Reliable Digital Platforms for Insurance Services", "https://cymasys.com/case-studies/building-reliable-digital-platforms-for-insurance-services/"),
]


def case_studies_static_list_html(thumb: str, arrow: str) -> str:
    items = []
    for industry, heading, url in CASE_STUDIES_STATIC:
        items.append(
            '<div role="listitem" class="w-dyn-item">'
            '<div class="div-block-1230">'
            f'<img decoding="async" src="{thumb}" loading="lazy" alt="" class="image-68">'
            '<div class="div-block-1233"><div class="div-block-1382">'
            f'<div class="text-block-554">{industry}</div>'
            f'<h2 class="heading-96">{heading}</h2></div>'
            '<div class="div-block-1383">'
            f'<a href="{url}" class="transformingbusiness-ai-btn w-inline-block">'
            '<div class="text-block-529-copy">Read More</div>'
            f'<img decoding="async" loading="lazy" src="{arrow}" alt="" class="image-145">'
            "</a></div></div></div></div>"
        )
    return (
        '<div role="list" class="collection-list w-dyn-items">'
        + "".join(items)
        + "</div>"
    )


def strip_case_studies_webflow_chrome(html: str, thumb: str, arrow: str) -> str:
    """Remove leftover Webflow empty-state + pagination; keep a static listing."""

    replacement = case_studies_static_list_html(thumb, arrow)

    def repl(match: re.Match[str]) -> str:
        return match.group(1) + replacement + match.group(2)

    updated, n = re.subn(
        r'(<section class="section-65">\s*<div class="w-layout-blockcontainer container-55 w-container">\s*<div class="w-dyn-list">)[\s\S]*?(</div>\s*</div>\s*</section>)',
        repl,
        html,
        count=1,
        flags=re.I,
    )
    if n:
        html = updated
    else:
        html = re.sub(
            r'<div class="w-dyn-empty">\s*<div>\s*No items found\.?\s*</div>\s*</div>',
            "",
            html,
            flags=re.I,
        )
        html = re.sub(
            r'<div\b[^>]*role="navigation"[^>]*class="[^"]*w-pagination-wrapper[^"]*"[^>]*>[\s\S]*?</a>\s*</div>',
            "",
            html,
            flags=re.I,
        )
    return html


def rewrite_static_copy(html: str, slug: str) -> str:
    """Apply copy corrections and remove retired presentation elements."""

    replacements = {
        "Seize the Next Oppertunity": "Seize the Next Opportunity.",
        "Explore Career Oppertunities": "Explore Career Opportunities.",
        "techbusinesses": "tech businesses",
        "unique your business needs": "your unique business needs",
        "Cloud & Devops": "Cloud & DevOps",
        "Cloud &amp; Devops": "Cloud &amp; DevOps",
        "Software That Deliver Real Results": "Software That Delivers Real Results",
        "By cotinuing": "By continuing",
        "fortune 1000 companies": "Fortune 1000 companies",
        "Manichester, CT 060442": "Manchester, CT 06042",
    }
    for old, new in replacements.items():
        html = html.replace(old, new)
    html = re.sub(r"Cloud\s*&(?:amp;)?\s*Devops", "Cloud &amp; DevOps", html, flags=re.I)

    # Keep only LinkedIn and Facebook in the repeated footer markup.
    html = re.sub(
        r'<a[^>]*data-link=["\']a-4058cb70["\'][^>]*>[\s\S]*?</a>',
        "",
        html,
        flags=re.I,
    )
    html = re.sub(
        r'<a[^>]*data-link=["\']a23["\'][^>]*>[\s\S]*?</a>',
        "",
        html,
        flags=re.I,
    )
    html = re.sub(
        r'<a[^>]*>[^<]*<img[^>]*class=["\'][^"\']*image-(?:110|111)[^"\']*["\'][^>]*>[^<]*</a>',
        "",
        html,
        flags=re.I,
    )

    if slug == "home":
        html = re.sub(
            r'<div class="hyper-text"[^>]*>\s*(?:Unlock New Possibilities|Workforce Solutions)\s*</div>',
            "",
            html,
            flags=re.I,
        )

    if slug == "job-seekers":
        news_urls = iter(
            [
                "https://cymasys.com/insights-2/",
                "https://cymasys.com/insights-3/",
                "https://cymasys.com/insights-4/",
            ] * 2
        )

        def link_news_card(match: re.Match[str]) -> str:
            url = next(news_urls)
            class_name = match.group(1)
            content = match.group(2)
            return f'<a href="{url}" class="{class_name}" target="_blank" rel="noopener noreferrer">{content}</a>'

        html = re.sub(
            r'<div class="(div-block-1105[^\"]*)">([\s\S]*?<img[^>]*>\s*)</div>',
            link_news_card,
            html,
            count=6,
            flags=re.I,
        )

    if slug == "case-studies":
        prefix = "../"
        html = strip_case_studies_webflow_chrome(
            html,
            thumb=f"{prefix}assets/images/casestudies2.webp",
            arrow=f"{prefix}assets/images/group-1000007155-2.svg?v=1780144474",
        )
    return html


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
        html = rewrite_legal_nav(html, slug)
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
        html = rewrite_static_copy(html, slug)
        if slug == "job-seekers":
            html = html.replace("</body>", LIVE_NEWS_SCRIPT + "</body>", 1)
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
        dest.write_text(rewrite_static_copy(content, rel_path.parent.name), encoding="utf-8")

    notice_dir = OUT / "notice-of-filing"
    notice_dir.mkdir(parents=True, exist_ok=True)
    (notice_dir / "index.html").write_text(
        '<!DOCTYPE html><html lang="en-US"><head><meta charset="UTF-8">'
        '<meta name="viewport" content="width=device-width, initial-scale=1">'
        '<title>Notice of Filing | CYMA</title>'
        '<link rel="stylesheet" href="../assets/css/style.css">'
        '<style>body{margin:0;color:#30363b;font-family:Arial,sans-serif}.notice-nav{height:78px;display:flex;align-items:center;justify-content:space-between;padding:0 clamp(24px,4vw,64px);box-sizing:border-box;background:#fff}.notice-logo{font-size:24px;color:#0562a7;font-weight:700}.notice-nav a{margin-left:24px;color:#0562a7;text-decoration:none;font-size:14px;font-weight:700}.notice-hero{height:272px;background:linear-gradient(90deg,rgba(0,92,145,.68),rgba(19,130,150,.45)),url("../assets/images/noticeoffiling.webp") center/cover}.notice-content{background:#fff url("../assets/images/bg-notice.webp") center/cover;padding:24px clamp(24px,9vw,220px) 100px;min-height:520px}.notice-crumb{font-size:12px;color:#69737b;margin-bottom:12px}.notice-content h1{font-size:clamp(40px,5vw,56px);font-weight:400;text-align:center;margin:8px 0 48px}.notice-card{max-width:820px;margin:auto}.notice-card h2{font-size:30px;margin:0 0 14px}.notice-card h3{font-size:14px;color:#0562a7;margin:16px 0 6px}.notice-card p{font-size:14px;line-height:1.55;margin:0 0 10px}.notice-footer{padding:28px 24px;text-align:center;border-top:1px solid #dbe8ed;font-size:13px}@media(max-width:700px){.notice-nav{height:auto;padding:18px 20px}.notice-nav-links{display:none}.notice-hero{height:190px}.notice-content{padding:20px 24px 64px}.notice-content h1{margin-bottom:32px}.notice-card h2{font-size:25px}}</style></head>'
        '<body><header class="notice-nav"><a class="notice-logo" href="../">CYMA</a><nav class="notice-nav-links"><a href="../">Home</a><a href="../about-us/">About Us</a><a href="../business-solutions/">Business Solutions</a><a href="../industries/">Industries</a><a href="../job-seekers/">Job Seekers</a><a href="../legal/">Legal</a><a href="../resources/">Resources</a></nav></header>'
        '<div class="notice-hero"></div><main class="notice-content"><div class="notice-crumb">Home &bull; Legal &bull; <strong>Notice of Filing</strong></div><h1>Notice of Filing</h1><section class="notice-card"><h2>Job Title: Software Developer</h2><h3>Job Duties:</h3><p>Design, and implement Java/J2EE applications using AGILE methodology, participate in Scrum, Retrospective, and Release Planning Meetings. Develop and manage CI/CD pipeline utilizing the Azure DevOps platform. Integrate cloud storage services with AWS S3-compatible APIs to ensure compatibility and interoperability. Build UI Screens using Angular JS, Node JS, HTML5, CSS, JavaScript, and Bootstrap. Perform error handling at the method level. Work on Spring controllers, microservices and DAO using annotation. Execute Kibana and elastic search to identify the Kafka and IBM MQ message failure scenarios. Improve response times and test web services, Restful web services and test using SOAP UI and POSTMAN with required validation of Source and Targets. Work on Mockito Framework. Utilize Git, TFS, Maven, Jenkins, Docker, and Kubernetes for efficient development workflows. Will work in Manchester, CT and/or various unanticipated client sites throughout the U.S. Must be willing to travel and/or relocate.</p><h3>Salary:</h3><p>$127,000 /year</p><h3>Employer:</h3><p>Cyma Systems Inc</p><h3>Work Location:</h3><p>360 Tolland Turnpike, Suite 2D, Manchester, CT and/or various unanticipated client sites throughout the U.S.</p><h3>Apply to:</h3><p>Cyma Systems Inc, 360 Tolland Turnpike, Suite 2D, Manchester, CT 06042</p></section></main><footer class="notice-footer">CYMA SYSTEMS, INC. &bull; 360 Tolland Turnpike, Suite 2D Manchester, CT 06042, USA</footer></body></html>',
        encoding="utf-8",
    )

    if shutil.which("du"):
        subprocess.run(["du", "-sh", str(OUT)], check=False)
    print(f"Built {len(pages)} pages into {OUT}")


if __name__ == "__main__":
    main()
