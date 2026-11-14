<?php
/** FindBack read-only static asset audit. No database access or writes. */
$root = dirname(__DIR__);
$paths = ['app', 'resources', 'routes', 'public/js', 'public/css'];
$extensions = ['php', 'js', 'css', 'html'];
$checked = 0;
$missing = [];
$versioned = [];
$assets = [];
foreach (['css', 'js'] as $type) {
    $dir = $root . '/public/' . $type;
    if (!is_dir($dir)) continue;
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iter as $file) {
        if (!$file->isFile() || strtolower($file->getExtension()) !== $type) continue;
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root . '/public/')));
        $assets[$relative] = true;
        if (preg_match('/-v\d+\.' . $type . '$/i', $relative)) $versioned[] = $relative;
    }
}
foreach ($paths as $path) {
    $dir = $root . '/' . $path;
    if (!is_dir($dir)) continue;
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iter as $file) {
        if (!$file->isFile() || !in_array(strtolower($file->getExtension()), $extensions, true)) continue;
        $checked++;
        $body = file_get_contents($file->getPathname());
        if ($body === false) continue;
        // Literal references only. Dynamic references must be reviewed separately.
        preg_match_all('~(?:asset\s*\(\s*[\'\"]/?|[\'\"]/?)(?P<path>(?:css|js)/[a-zA-Z0-9_./-]+\.(?:css|js))~', $body, $matches);
        foreach ($matches['path'] as $asset) {
            if (!isset($assets[$asset])) {
                $source = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
                $missing[$asset][$source] = true;
            }
        }
    }
}
ksort($missing);
sort($versioned);
echo "FindBack static asset audit (read-only)\n";
echo 'Source files scanned: ' . $checked . "\n";
echo 'CSS/JS assets: ' . count($assets) . "\n";
echo 'Version-suffixed assets: ' . count($versioned) . "\n";
foreach ($versioned as $asset) echo "VERSIONED $asset\n";
echo 'Missing literal references: ' . count($missing) . "\n";
foreach ($missing as $asset => $sources) echo "MISSING $asset <- " . implode(', ', array_keys($sources)) . "\n";
echo "NOTE: Dynamic assets, remote URLs, Vite, and runtime behavior require separate verification.\n";
exit(($missing || $versioned) ? 1 : 0);
