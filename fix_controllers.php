<?php

// Directory to scan
$baseDir = __DIR__ . '/app/Controllers';

// Log file
$logFile = 'controller_fix_log.txt';

// Patterns to check for database usage and initialization
$patterns = [
    '/\$db\s*->/',                   // Matches $db-> (possible usage of $db)
    '/\$this->db\s*=\s*\\\Config\\\Database::connect\(\);/',  // Matches $this->db = \Config\Database::connect();
];

// Function to scan files and log issues
function scanAndFixFiles($dir, $patterns, $logFile) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $log = fopen($logFile, 'a');

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $lines = explode("\n", $content);
            $dbInitialized = false;
            $needsDbInitialization = false;
            $dbPattern = $patterns[0];
            $initPattern = $patterns[1];

            foreach ($lines as $lineContent) {
                if (preg_match($initPattern, $lineContent)) {
                    $dbInitialized = true;
                    break;
                }
            }

            foreach ($lines as $lineNumber => $lineContent) {
                if (preg_match($dbPattern, $lineContent)) {
                    $needsDbInitialization = true;
                    if (!$dbInitialized) {
                        // Insert database initialization after the class declaration and before any method
                        foreach ($lines as $index => $line) {
                            if (strpos($line, 'class') !== false) {
                                // Find the next non-empty line after the class declaration
                                $insertIndex = $index + 1;
                                while (trim($lines[$insertIndex]) === '') {
                                    $insertIndex++;
                                }
                                array_splice($lines, $insertIndex, 0, "    protected \$db;\n    public function __construct()\n    {\n        \$this->db = \\Config\\Database::connect();\n        parent::__construct();\n    }");
                                $dbInitialized = true;
                                break;
                            }
                        }
                    }
                    fwrite($log, "Fixed undefined \$db variable in file: " . $file->getPathname() . " on line " . ($lineNumber + 1) . "\n");
                }
            }

            // Write the modified content back to the file
            file_put_contents($file->getPathname(), implode("\n", $lines));
        }
    }

    fclose($log);
}

// Scan the base directory
scanAndFixFiles($baseDir, $patterns, $logFile);

echo "Scanning and fixing completed. Check $logFile for details.\n";
?>
