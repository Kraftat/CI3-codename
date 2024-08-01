<?php

function scanForPatterns($dir, $patterns)
{
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $logFile = __DIR__ . '/refactor_verification_log.txt';
    // Clear previous log file
    file_put_contents($logFile, '');

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getRealPath();
            $code = file_get_contents($filePath);

            foreach ($patterns as $pattern => $replacement) {
                if (preg_match_all($pattern, $code, $matches)) {
                    $unrefactoredInstances = [];
                    foreach ($matches[0] as $match) {
                        if (!preg_match($replacement, $match)) {
                            $unrefactoredInstances[] = $match;
                        }
                    }

                    if (!empty($unrefactoredInstances)) {
                        $logEntry = "Unrefactored instance found in: $filePath\n";
                        foreach ($unrefactoredInstances as $instance) {
                            $logEntry .= "Unrefactored code: $instance\n";
                        }
                        echo $logEntry;
                        file_put_contents($logFile, $logEntry, FILE_APPEND);
                    }
                }
            }
        }
    }

    echo "Verification completed. Check the log file for details.\n";
}

// Patterns to check and their correct replacements
$patterns = [
    // Check for $this->input->post() changes
    '/\$this->input->post\((.*?)\)/' => '/\$this->request->getPost\((.*?)\)/',
    // Check for $this->input->get() changes
    '/\$this->input->get\((.*?)\)/' => '/\$this->request->getGet\((.*?)\)/',
    // Check for $this->input->server() changes
    '/\$this->input->server\((.*?)\)/' => '/\$this->request->getServer\((.*?)\)/',
    // Check for $this->input->cookie() changes
    '/\$this->input->cookie\((.*?)\)/' => '/\$this->request->getCookie\((.*?)\)/',
    // Check for $this->input->ip_address() changes
    '/\$this->input->ip_address\(\)/' => '/\$this->request->getIPAddress\(\)/',

    // Check for $db->where()->get()->result() changes
    '/\$db->where\((.*?)\)->get\((.*?)\)->result\(\)/' => '/\$db->table\((.*?)\)->where\((.*?)\)->get\(\)->getResult\(\)/',
    // Check for $db->where() changes
    '/\$db->where\((.*?)\)/' => '/\$db->table\((.*?)\)->where\((.*?)\)/',
    // Check for $db->get() changes
    '/\$db->get\((.*?)\)/' => '/\$db->table\((.*?)\)->get\(\)/',
    // Check for $db->insert() changes
    '/\$db->insert\((.*?)\)/' => '/\$db->table\((.*?)\)->insert\((.*?)\)/',
    // Check for $db->update() changes
    '/\$db->update\((.*?)\)/' => '/\$db->table\((.*?)\)->update\((.*?)\)/',
    // Check for $db->delete() changes
    '/\$db->delete\((.*?)\)/' => '/\$db->table\((.*?)\)->delete\((.*?)\)/',
    // Check for $db->result() changes
    '/\$db->result\(\)/' => '/\$db->getResult\(\)/',
];

$directories = [
    __DIR__ . '/app/Controllers',
    __DIR__ . '/app/Models',
];

foreach ($directories as $directory) {
    scanForPatterns($directory, $patterns);
}

echo "Verification completed. Check the log file for details.\n";
