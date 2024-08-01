<?php

// Directory to scan
$baseDir = __DIR__ . '/app';

// List of deprecated patterns and their replacements
$patterns = [
    'load->model' => 'model',
    'load->library' => 'library',
    'load->view' => 'view',
];

// Log file
$logFile = 'fix_refactoring_view.txt';

// Function to scan files and replace patterns
function scanAndReplaceFiles($dir, $patterns, $logFile) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $log = fopen($logFile, 'a');

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $originalContent = $content;
            foreach ($patterns as $oldPattern => $newPattern) {
                if (strpos($content, $oldPattern) !== false) {
                    $content = str_replace($oldPattern, $newPattern, $content);
                }
            }
            if ($content !== $originalContent) {
                file_put_contents($file->getPathname(), $content);
                fwrite($log, "Updated {$file->getPathname()}\n");
            }
        }
    }

    fclose($log);
}

// Scan the base directory and replace patterns
scanAndReplaceFiles($baseDir, $patterns, $logFile);

echo "Scanning and replacement completed. Check $logFile for details.\n";
?>
