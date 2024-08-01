<?php

function scanAndRefactorPatterns($dir, $patterns)
{
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $logFile = __DIR__ . '/refactor_log.txt';
    // Clear previous log file
    file_put_contents($logFile, '');

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getRealPath();
            $code = file_get_contents($filePath);
            $originalCode = $code;

            foreach ($patterns as $pattern => $replacement) {
                $code = preg_replace($pattern, $replacement, $code);
            }

            // If the code was modified, save the changes and log the refactor
            if ($code !== $originalCode) {
                file_put_contents($filePath, $code);
                $logEntry = "Refactored: $filePath\n";
                echo $logEntry;
                file_put_contents($logFile, $logEntry, FILE_APPEND);
                // Log before and after changes
                file_put_contents($logFile, "Before:\n$originalCode\nAfter:\n$code\n\n", FILE_APPEND);
            } else {
                $logEntry = "No changes needed for: $filePath\n";
                echo $logEntry;
                file_put_contents($logFile, $logEntry, FILE_APPEND);
            }
        }
    }

    echo "Refactoring and verification completed. Check the log file for details.\n";
}

// Patterns to check and their correct replacements
$patterns = [
    // Check for $this->input->post() changes
    '/\$this->input->post\((.*?)\)/' => '\$this->request->getPost(\1)',
    // Check for $this->input->get() changes
    '/\$this->input->get\((.*?)\)/' => '\$this->request->getGet(\1)',
    // Check for $this->input->server() changes
    '/\$this->input->server\((.*?)\)/' => '\$this->request->getServer(\1)',
    // Check for $this->input->cookie() changes
    '/\$this->input->cookie\((.*?)\)/' => '\$this->request->getCookie(\1)',
    // Check for $this->input->ip_address() changes
    '/\$this->input->ip_address\(\)/' => '\$this->request->getIPAddress()',

    // Check for $db->where()->get()->result() changes
    '/\$db->where\((.*?)\)->get\((.*?)\)->result\(\)/' => '\$db->table(\2)->where(\1)->get()->getResult()',
    // Check for $db->where() changes
    '/\$db->where\((.*?)\)/' => '\$db->table(\1)->where(\1)',
    // Check for $db->get() changes
    '/\$db->get\((.*?)\)/' => '\$db->table(\1)->get()',
    // Check for $db->insert() changes
    '/\$db->insert\((.*?)\)/' => '\$db->table(\1)->insert(\1)',
    // Check for $db->update() changes
    '/\$db->update\((.*?)\)/' => '\$db->table(\1)->update(\1)',
    // Check for $db->delete() changes
    '/\$db->delete\((.*?)\)/' => '\$db->table(\1)->delete(\1)',
    // Check for $db->result() changes
    '/\$db->result\(\)/' => '\$db->getResult()',
];

// Ensure $db is properly instantiated
function ensureDbInitialization($code)
{
    $initPattern = '/\$db\s*=\s*\\Config\\Database::connect\(\);/';
    $dbUsagePattern = '/\$db->/';
    if (preg_match($dbUsagePattern, $code) && !preg_match($initPattern, $code)) {
        $initCode = "\$db = \\Config\\Database::connect();\n";
        $code = preg_replace('/<\?php\s*/', "<?php\n$initCode", $code, 1);
    }
    return $code;
}

$directories = [
    __DIR__ . '/app/Controllers',
    __DIR__ . '/app/Models',
];

foreach ($directories as $directory) {
    scanAndRefactorPatterns($directory, $patterns);
}

echo "Refactoring completed. Check the log file for details.\n";
