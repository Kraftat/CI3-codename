<?php

function extractForeachLoops($directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $foreachLoops = [];

    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $fileContent = file($file->getRealPath());
            foreach ($fileContent as $lineNumber => $lineContent) {
                if (preg_match('/\bforeach\s*\(/', $lineContent)) {
                    $foreachLoops[] = [
                        'file' => $file->getRealPath(),
                        'lineNumber' => $lineNumber + 1,
                        'content' => trim($lineContent)
                    ];
                }
            }
        }
    }

    return $foreachLoops;
}

function saveForeachLoopsToFile($foreachLoops, $outputFile) {
    $fileContent = "";

    foreach ($foreachLoops as $loop) {
        $fileContent .= "File: {$loop['file']}\n";
        $fileContent .= "Line Number: {$loop['lineNumber']}\n";
        $fileContent .= "Content: {$loop['content']}\n";
        $fileContent .= "------------------------\n";
    }

    file_put_contents($outputFile, $fileContent);
}

$directory = __DIR__ . '/app/Controllers'; // Directory to scan
$outputFile = __DIR__ . '/foreach_loops.txt'; // Output file

$foreachLoops = extractForeachLoops($directory);
saveForeachLoopsToFile($foreachLoops, $outputFile);

echo "Foreach loops extracted and saved to foreach_loops.txt\n";
