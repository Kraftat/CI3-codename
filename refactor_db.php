<?php

function refactorCode($dir)
{
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $logFile = __DIR__ . '/refactor_db_log.txt';
    // Clear previous log file
    file_put_contents($logFile, '');

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getRealPath();
            $code = file_get_contents($filePath);

            // Check if $db-> is used but $db is not initialized
            if (preg_match('/\$db->/', $code) && !preg_match('/\$db\s*=\s*\\\Config\\\Database::connect\(\);/', $code)) {
                // Add protected $db; declaration in the class
                if (preg_match('/class\s+\w+\s+extends\s+\w+\s*\{/', $code)) {
                    $code = preg_replace('/(class\s+\w+\s+extends\s+\w+\s*\{)/', "$1\n    protected \$db;\n", $code, 1);
                }
                // Add $this->db = \Config\Database::connect(); initialization in the constructor
                if (preg_match('/function\s+__construct\s*\(.*\)\s*\{/', $code)) {
                    $code = preg_replace('/(function\s+__construct\s*\(.*\)\s*\{)/', "$1\n        \$this->db = \Config\Database::connect();\n", $code, 1);
                } else {
                    // Add a constructor if not present
                    $code = preg_replace('/(class\s+\w+\s+extends\s+\w+\s*\{)/', "$1\n    public function __construct()\n    {\n        \$this->db = \Config\Database::connect();\n    }\n", $code, 1);
                }
                
                // Log the changes
                $logEntry = "Refactored: $filePath\n";
                file_put_contents($logFile, $logEntry, FILE_APPEND);
                file_put_contents($filePath, $code);
            }
        }
    }

    echo "Refactoring completed. Check the log file for details.\n";
}

$directories = [
    __DIR__ . '/app/Controllers',
    __DIR__ . '/app/Models',
];

foreach ($directories as $directory) {
    refactorCode($directory);
}

echo "Refactoring completed. Check the log file for details.\n";
