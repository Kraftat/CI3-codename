<?php

// Directory to scan
$viewsDir = __DIR__ . '/app';

// List of deprecated functions or syntax patterns
$deprecatedPatterns = [
    'load->view',   // CI3 syntax for loading views
    'load->model',  // CI3 syntax for loading models
    'load->library',// CI3 syntax for loading libraries
    // Add more patterns as needed
];

// Function to scan files
function scanFiles($dir, $patterns) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            foreach ($patterns as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    echo "Found deprecated pattern '$pattern' in file: " . $file->getPathname() . PHP_EOL;
                }
            }
        }
    }
}

// Scan the views directory
scanFiles($viewsDir, $deprecatedPatterns);

echo "Scanning completed.\n";
?>
