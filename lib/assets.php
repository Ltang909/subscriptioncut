<?php
// Appends a cache-busting version (the file's mtime) to static asset URLs,
// so a stale CDN/browser cache doesn't keep serving last deploy's CSS/JS.
function asset_url(string $path): string {
    $full = dirname(__DIR__) . '/' . ltrim($path, '/');
    $v = @filemtime($full) ?: time();
    return $path . '?v=' . $v;
}
