<?php

function searchLibraryLoads($directory, $outputFile) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $fileHandle = fopen($outputFile, 'w');

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $fileContent = file($file->getPathname(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($fileContent as $line) {
                if (strpos($line, '$this->load->library(') !== false || strpos($line, '$this->load->library') !== false) {
                    fwrite($fileHandle, $line . PHP_EOL);
                }
            }
        }
    }

    fclose($fileHandle);
}

// Set your project directory and output file name
$projectDirectory = 'app/Controllers';
$outputFileName = 'library_loads.txt';

searchLibraryLoads($projectDirectory, $outputFileName);

echo "Lines containing \$this->load->library have been saved to $outputFileName.";

