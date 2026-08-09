#!/usr/bin/env python3
"""Convert PNG/JPG/JPEG assets to WebP in cyma-prod-v2 theme and update references."""

import json
import os
import re
import subprocess
import sys
from pathlib import Path

THEME = Path(__file__).resolve().parents[1] / "wordpress/wp-content/themes/cyma-prod-v2"
QUALITY = 80
RASTER_EXTS = {".png", ".jpg", ".jpeg"}
TEXT_EXTENSIONS = {".php", ".css", ".js", ".json", ".html", ".htm", ".xml", ".md", ".txt", ".scss", ".sass", ".less"}
SKIP_DIRS = {".git", "node_modules", "vendor"}


def is_raster(path: Path) -> bool:
    return path.suffix.lower() in RASTER_EXTS


def find_raster_files(root: Path) -> list[Path]:
    files = []
    for dirpath, dirnames, filenames in os.walk(root):
        dirnames[:] = [d for d in dirnames if d not in SKIP_DIRS]
        for name in filenames:
            p = Path(dirpath) / name
            if is_raster(p):
                files.append(p)
    return sorted(files)


def convert_file(src: Path) -> tuple[Path | None, str | None]:
    dst = src.with_suffix(".webp")
    if dst.exists() and dst.stat().st_mtime >= src.stat().st_mtime:
        return dst, None
    try:
        result = subprocess.run(
            ["cwebp", "-q", str(QUALITY), str(src), "-o", str(dst)],
            capture_output=True,
            text=True,
        )
        if result.returncode != 0:
            return None, result.stderr.strip() or result.stdout.strip() or "cwebp failed"
        if not dst.exists():
            return None, "output file missing"
        return dst, None
    except FileNotFoundError:
        return None, "cwebp not found"


def build_replacement_patterns(src: Path, theme: Path) -> list[tuple[re.Pattern, str]]:
    """Patterns to replace references to this file (basename and theme-relative paths)."""
    rel = src.relative_to(theme).as_posix()
    stem = src.stem
    old_ext = src.suffix
    new_rel = str(Path(rel).with_suffix(".webp"))
    basename_old = src.name
    basename_new = f"{stem}.webp"

    patterns: list[tuple[re.Pattern, str]] = []

    # Exact basename with original extension casing
    for ext in {old_ext, old_ext.lower(), old_ext.upper()}:
        old_name = f"{stem}{ext}"
        patterns.append(
            (re.compile(re.escape(old_name), re.IGNORECASE), basename_new)
        )

    # Theme-relative path variants
    for path_variant in {rel, rel.lower()}:
        old_path = path_variant
        new_path = str(Path(path_variant).with_suffix(".webp"))
        patterns.append((re.compile(re.escape(old_path), re.IGNORECASE), new_path))

    # URL-style paths without leading theme prefix
    for prefix in ("assets/", "./assets/", "../images/", "images/"):
        if rel.startswith("assets/"):
            partial = rel[len("assets/") :]
            old_partial = partial
            new_partial = str(Path(partial).with_suffix(".webp"))
            patterns.append(
                (re.compile(re.escape(prefix + old_partial), re.IGNORECASE), prefix + new_partial)
            )

    return patterns


def update_text_file(path: Path, replacements: list[tuple[re.Pattern, str]]) -> bool:
    try:
        text = path.read_text(encoding="utf-8")
    except (UnicodeDecodeError, OSError):
        return False
    original = text
    for pattern, repl in replacements:
        text = pattern.sub(repl, text)
    if text != original:
        path.write_text(text, encoding="utf-8")
        return True
    return False


def collect_text_files(theme: Path) -> list[Path]:
    files = []
    for dirpath, dirnames, filenames in os.walk(theme):
        dirnames[:] = [d for d in dirnames if d not in SKIP_DIRS]
        for name in filenames:
            p = Path(dirpath) / name
            if p.suffix.lower() in TEXT_EXTENSIONS:
                files.append(p)
    return files


def main() -> int:
    if not THEME.is_dir():
        print(f"Theme not found: {THEME}", file=sys.stderr)
        return 1

    raster_files = find_raster_files(THEME)
    size_before = sum(f.stat().st_size for f in raster_files)

    converted = 0
    skipped_existing = 0
    failures: list[tuple[str, str]] = []
    webp_files: list[Path] = []

    print(f"Found {len(raster_files)} raster files ({size_before / 1024 / 1024:.2f} MB)")

    for src in raster_files:
        dst, err = convert_file(src)
        if err:
            failures.append((str(src.relative_to(THEME)), err))
            continue
        if dst is None:
            failures.append((str(src.relative_to(THEME)), "unknown error"))
            continue
        webp_files.append(dst)
        converted += 1

    size_after = sum(f.stat().st_size for f in webp_files if f.exists())

    # Build global replacement list (dedupe by pattern string)
    all_replacements: list[tuple[re.Pattern, str]] = []
    seen_patterns: set[str] = set()
    for src in raster_files:
        if any(src.relative_to(THEME).as_posix() == f[0] for f in failures):
            continue
        for pat, repl in build_replacement_patterns(src, THEME):
            key = pat.pattern + "=>" + repl
            if key not in seen_patterns:
                seen_patterns.add(key)
                all_replacements.append((pat, repl))

    text_files = collect_text_files(THEME)
    updated_files: list[str] = []
    for tf in text_files:
        if update_text_file(tf, all_replacements):
            updated_files.append(str(tf.relative_to(THEME)))

    # Remove originals for successfully converted files
    removed = 0
    for src in raster_files:
        rel = src.relative_to(THEME).as_posix()
        if any(rel == f[0] for f in failures):
            continue
        webp = src.with_suffix(".webp")
        if webp.exists():
            src.unlink()
            removed += 1

    # Verify no broken refs to deleted extensions for converted basenames
    remaining_refs: list[str] = []
    converted_stems = {
        src.stem for src in raster_files
        if not any(src.relative_to(THEME).as_posix() == f[0] for f in failures)
    }
    ref_pattern = re.compile(
        r'(?:assets/|images/|\.\./images/)?[\w\-./]+\.(?:png|jpe?g)',
        re.IGNORECASE,
    )
    for tf in text_files:
        try:
            content = tf.read_text(encoding="utf-8")
        except (UnicodeDecodeError, OSError):
            continue
        for match in ref_pattern.finditer(content):
            hit = match.group(0)
            stem = Path(hit).stem
            if stem in converted_stems:
                remaining_refs.append(f"{tf.relative_to(THEME)}: {hit}")

    report = {
        "converted": converted,
        "removed_originals": removed,
        "failures": failures,
        "size_before_bytes": size_before,
        "size_after_bytes": size_after,
        "size_before_mb": round(size_before / 1024 / 1024, 2),
        "size_after_mb": round(size_after / 1024 / 1024, 2),
        "saved_mb": round((size_before - size_after) / 1024 / 1024, 2),
        "saved_percent": round((1 - size_after / size_before) * 100, 1) if size_before else 0,
        "updated_files_count": len(updated_files),
        "updated_files": updated_files,
        "remaining_broken_refs": remaining_refs[:50],
        "remaining_broken_refs_count": len(remaining_refs),
    }

    print(json.dumps(report, indent=2))
    return 0 if not failures else 2


if __name__ == "__main__":
    raise SystemExit(main())
