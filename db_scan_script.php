<?php

// Directory to scan
$baseDir = __DIR__ . '/app';

// Log file
$logFile = 'db_variable_scan_log.txt';

// Patterns to check for database usage and initialization
$patterns = [
    '/\$db\s*->/',                  // Matches $db-> (possible usage of $db)
    '/\$this->db\s*=\s*\\\Config\\\Database::connect\s*\(\)/'    // Matches $this->db = \Config\Database::connect() (proper initialization of $db)
];

// Function to scan files and log issues
function scanFiles($dir, $patterns, $logFile) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $log = fopen($logFile, 'a');

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $lines = explode("\n", $content);
            $dbInitialized = false;
            foreach ($lines as $lineContent) {
                if (preg_match($patterns[1], $lineContent)) {
                    $dbInitialized = true;
                    break;
                }
            }
            foreach ($lines as $lineNumber => $lineContent) {
                if (preg_match($patterns[0], $lineContent)) {
                    if (!$dbInitialized) {
                        fwrite($log, "Potential undefined \$db variable in file: " . $file->getPathname() . " on line " . ($lineNumber + 1) . "\n");
                        fwrite($log, "Line content: " . $lineContent . "\n\n");
                    }
                }
            }
        }
    }

    fclose($log);
}

// Scan the base directory
scanFiles($baseDir, $patterns, $logFile);

echo "Scanning completed. Check $logFile for details.\n";
?>
