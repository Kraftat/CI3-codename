<?php

// Directory to scan
$baseDir = __DIR__ . '/app/Controllers';

// Log file
$logFile = 'controller_update_report.txt';

// Function to scan and update files
function scanAndUpdateControllers($dir, $logFile) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $log = fopen($logFile, 'a');

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $lines = explode("\n", $content);
            $modified = false;
            $useStatements = [];
            $dbInitialized = false;

            // Iterate over lines to find issues and fix them
            foreach ($lines as $lineNumber => $lineContent) {
                // Skip class declaration lines
                if (preg_match('/^class\s+[a-zA-Z_][a-zA-Z0-9_]*\s+extends\s+[a-zA-Z_][a-zA-Z0-9_]*\s*$/', $lineContent)) {
                    continue;
                }

                // Fix model naming conventions in use statements
                if (preg_match('/use\s+App\\\Models\\\([a-zA-Z_][a-zA-Z0-9_]*)\s*;/', $lineContent, $matches)) {
                    $modelName = $matches[1];
                    if (!str_ends_with($modelName, 'Model')) {
                        $suggestedName = $modelName . 'Model';
                        $newLineContent = "use App\\Models\\$suggestedName;";
                        $lines[$lineNumber] = $newLineContent;
                        $modified = true;
                        fwrite($log, "Use statement model naming issue fixed in file: " . $file->getPathname() . " on line " . ($lineNumber + 1) . "\n");
                        fwrite($log, "Original line: " . $lineContent . "\n");
                        fwrite($log, "Fixed line: " . $newLineContent . "\n\n");
                    }
                }

                // Fix model naming conventions in class properties
                if (preg_match('/\@var\s+App\\\Models\\\([a-zA-Z_][a-zA-Z0-9_]*)\s*$/', $lineContent, $matches)) {
                    $modelName = $matches[1];
                    if (!str_ends_with($modelName, 'Model')) {
                        $suggestedName = $modelName . 'Model';
                        $newLineContent = str_replace($modelName, $suggestedName, $lineContent);
                        $lines[$lineNumber] = $newLineContent;
                        $modified = true;
                        fwrite($log, "DocBlock model naming issue fixed in file: " . $file->getPathname() . " on line " . ($lineNumber + 1) . "\n");
                        fwrite($log, "Original line: " . $lineContent . "\n");
                        fwrite($log, "Fixed line: " . $newLineContent . "\n\n");
                    }
                }

                // Fix model naming conventions in property assignments
                if (preg_match('/\$this->([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*new\s+\\\App\\\Models\\\([a-zA-Z_][a-zA-Z0-9_]*)\(\);/', $lineContent, $matches)) {
                    $instanceName = $matches[1];
                    $modelName = $matches[2];
                    if (!str_ends_with($modelName, 'Model')) {
                        $suggestedName = $modelName . 'Model';
                        $newLineContent = "\$this->$instanceName = new \\App\\Models\\$suggestedName();";
                        $lines[$lineNumber] = $newLineContent;
                        $useStatements[] = "use App\\Models\\$suggestedName;";
                        $modified = true;
                        fwrite($log, "Model naming issue fixed in file: " . $file->getPathname() . " on line " . ($lineNumber + 1) . "\n");
                        fwrite($log, "Original line: " . $lineContent . "\n");
                        fwrite($log, "Fixed line: " . $newLineContent . "\n\n");
                    }
                }

                // Remove duplicate $db declarations
                if (preg_match('/(protected|public)\s+\$db\s*;/', $lineContent)) {
                    if ($dbInitialized) {
                        unset($lines[$lineNumber]);
                        $modified = true;
                    } else {
                        $dbInitialized = true;
                    }
                }

                // Remove duplicate $db initializations
                if (preg_match('/\$this->db\s*=\s*\\\Config\\\Database::connect\(\);/', $lineContent)) {
                    if ($dbInitialized) {
                        unset($lines[$lineNumber]);
                        $modified = true;
                    } else {
                        $dbInitialized = true;
                    }
                }

                // Fix form validation rules
                if (strpos($lineContent, 'setRule(') !== false) {
                    $newLineContent = preg_replace('/setRule\(([^,]+),\s*([^,]+),\s*([^,]+)\)/', 'setRules([$1 => ["label" => $2, "rules" => $3]])', $lineContent);
                    $lines[$lineNumber] = $newLineContent;
                    $modified = true;
                }

                // Use redirect()->to() for redirections
                if (strpos($lineContent, 'redirect(base_url(') !== false) {
                    $newLineContent = preg_replace('/redirect\(base_url\(([^)]+)\)\);/', 'return redirect()->to(base_url($1));', $lineContent);
                    $lines[$lineNumber] = $newLineContent;
                    $modified = true;
                }
            }

            // Add use statements at the beginning of the file
            if (!empty($useStatements)) {
                array_splice($lines, 1, 0, $useStatements);
                $modified = true;
            }

            // Write the modified content back to the file
            if ($modified) {
                file_put_contents($file->getPathname(), implode("\n", $lines));
            }
        }
    }

    fclose($log);
}

// Scan the base directory
scanAndUpdateControllers($baseDir, $logFile);

echo "Scanning and updating completed. Check $logFile for details.\n";
?>
