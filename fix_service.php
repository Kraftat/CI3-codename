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
 * Fix service calls with array parameters.
 *
 * @param string $filePath File to process
 * @return void
 */
function fixServiceCalls($filePath) {
    $content = file_get_contents($filePath);

    // Replace service calls with array parameters
    $pattern = '/service\(\s*([\'"][^\'"]+[\'"])\s*(\$.+?)\)/';
    $replacement = 'service($1, $2)';
    $content = preg_replace($pattern, $replacement, $content);

    // Write the updated content back to the file
    file_put_contents($filePath, $content);
}

// Define the directory containing your controllers and libraries
$controllersDir = __DIR__ . '/app/Controllers';
$librariesDir = __DIR__ . '/app/Libraries';

// Process all PHP files in the controllers and libraries directories
$controllerFiles = getDirContents($controllersDir);
$libraryFiles = getDirContents($librariesDir);

// Fix service calls in controller and library files
foreach (array_merge($controllerFiles, $libraryFiles) as $file) {
    fixServiceCalls($file);
}

echo "Service calls fixed.";
?>
