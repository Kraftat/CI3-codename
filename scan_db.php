<?php

function scanForUndefinedDbUsage($dir)
{
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $logFile = __DIR__ . '/undefined_db_usage_log.txt';
    // Clear previous log file
    file_put_contents($logFile, '');

    $dbUsagePattern = '/\$db->/';
    $dbInitPattern = '/\$db\s*=\s*\\Config\\Database::connect\(\);/';

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getRealPath();
            $code = file_get_contents($filePath);

            if (preg_match($dbUsagePattern, $code)) {
                if (!preg_match($dbInitPattern, $code)) {
                    $logEntry = "Undefined \$db usage found in: $filePath\n";
                    echo $logEntry;
                    file_put_contents($logFile, $logEntry, FILE_APPEND);

                    // Log the lines where $db is used
                    $lines = explode("\n", $code);
                    foreach ($lines as $lineNumber => $lineContent) {
                        if (preg_match($dbUsagePattern, $lineContent)) {
                            $lineLog = "Line " . ($lineNumber + 1) . ": $lineContent\n";
                            echo $lineLog;
                            file_put_contents($logFile, $lineLog, FILE_APPEND);
                        }
                    }
                }
            }
        }
    }

    echo "Scan completed. Check the log file for details.\n";
}

$directories = [
    __DIR__ . '/app/Controllers',
    __DIR__ . '/app/Models',
];

foreach ($directories as $directory) {
    scanForUndefinedDbUsage($directory);
}

echo "Scan completed. Check the log file for details.\n";
