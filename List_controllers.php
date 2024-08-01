<?php

// Directory containing your controller files
$controllersDir = __DIR__ . '/app/Controllers';

// Output file path
$outputFile = __DIR__ . '/controller_list.txt';

// Check if the directory exists
if (!is_dir($controllersDir)) {
    die("Directory does not exist: $controllersDir");
}

// Function to recursively scan directories and get file names
function listControllers($dir, &$fileList) {
    $files = glob($dir . '/*.php');
    if ($files) {
        foreach ($files as $file) {
            // Get the filename without the extension
            $filename = basename($file, '.php');
            $fileList[] = $filename;
        }
    }
    
    $subDirs = glob($dir . '/*', GLOB_ONLYDIR);
    if ($subDirs) {
        foreach ($subDirs as $subDir) {
            $subDirName = basename($subDir);
            $fileList[] = $subDirName . '/'; // Add subfolder name with trailing slash
            listControllers($subDir, $fileList); // Recursively list files in subfolders
        }
    }
}

// Array to hold file names and subfolder names
$fileList = [];

// Start listing controllers
listControllers($controllersDir, $fileList);

// Write to the output file
file_put_contents($outputFile, "Controller Files:\n");
foreach ($fileList as $name) {
    file_put_contents($outputFile, $name . "\n", FILE_APPEND);
}

echo "Controller names have been saved to: $outputFile\n";

?>
