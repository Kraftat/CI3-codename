<?php

/**
 * Recursively scan directory for files with a specific extension.
 *
 * @param string $dir Directory to scan
 * @param string $extension File extension to filter by
 * @return array List of files with the specified extension
 */
function getDirContents($dir, $extension = 'php') {
    $results = [];
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            if (pathinfo($path, PATHINFO_EXTENSION) === $extension) {
                $results[] = $path;
            }
        } elseif ($value != "." && $value != "..") {
            $results = array_merge($results, getDirContents($path, $extension));
        }
    }

    return $results;
}

/**
 * Replace old CodeIgniter 3 library loading syntax with CodeIgniter 4 service syntax.
 *
 * @param string $filePath File to process
 * @param string $logFile Log file to record changes
 * @return void
 */
function replaceLibraryLoadingSyntax($filePath, $logFile) {
    $content = file_get_contents($filePath);
    $pattern = '/\$this->load->library\(\s*[\'"](\w+)[\'"]\s*(, \[.*?\]|\s*, \$(.*?))?\);/';

    if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $libraryName = $match[1];
            $replacement = "\$this->$libraryName = service('$libraryName'" . (isset($match[2]) ? "$match[2]" : "") . ");";
            $content = str_replace($match[0], $replacement, $content);

            // Log the change
            file_put_contents($logFile, "Replaced: {$match[0]} with {$replacement} in {$filePath}\n", FILE_APPEND);
        }
        // Write the updated content back to the file
        file_put_contents($filePath, $content);
    }
}

/**
 * Fix service calls with array parameters.
 *
 * @param string $filePath File to process
 * @param string $logFile Log file to record changes
 * @return void
 */
function fixServiceCalls($filePath, $logFile) {
    $content = file_get_contents($filePath);
    $pattern = '/service\((\'\w+\')(\$[a-zA-Z_]\w*)\)/';

    if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $replacement = "service({$match[1]}, {$match[2]})";
            $content = str_replace($match[0], $replacement, $content);

            // Log the change
            file_put_contents($logFile, "Fixed: {$match[0]} to {$replacement} in {$filePath}\n", FILE_APPEND);
        }
        // Write the updated content back to the file
        file_put_contents($filePath, $content);
    }
}

// Define the directories containing your controllers, libraries, and models
$controllersDir = __DIR__ . '/app/Controllers';
$librariesDir = __DIR__ . '/app/Libraries';
$modelsDir = __DIR__ . '/app/Models';
$logFile = __DIR__ . '/conversion_log.txt';

// Clear the log file
file_put_contents($logFile, '');

// Process all PHP files in the controllers, libraries, and models directories
$controllerFiles = getDirContents($controllersDir);
$libraryFiles = getDirContents($librariesDir);
$modelFiles = getDirContents($modelsDir);

// Replace old syntax and fix service calls in controller, library, and model files
foreach (array_merge($controllerFiles, $libraryFiles, $modelFiles) as $file) {
    replaceLibraryLoadingSyntax($file, $logFile);
    fixServiceCalls($file, $logFile);
}

echo "Conversion completed. Check conversion_log.txt for details.";
?>
