<?php

function updateForeachLoops($directory, $logFile)
{
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $logEntries = [];

    foreach ($files as $file) {
        if ($file->isFile() && pathinfo($file->getFilename(), PATHINFO_EXTENSION) === 'php') {
            $filePath = $file->getRealPath();
            $fileContent = file_get_contents($filePath);
            $updatedContent = updateForeachLoopContent($fileContent, $filePath, $logEntries);

            if ($fileContent !== $updatedContent) {
                file_put_contents($filePath, $updatedContent);
                $logEntries[] = "Updated foreach loops in file: $filePath";
            }
        }
    }

    file_put_contents($logFile, implode(PHP_EOL, $logEntries));
    echo "Foreach loop update complete. Log file: $logFile\n";
}

function updateForeachLoopContent($content, $filePath, &$logEntries)
{
    // Regex to match foreach loops
    $pattern = '/foreach\s*\(([^)]+)\s+as\s+\$([a-zA-Z_][a-zA-Z0-9_]*)\s*(?:=>\s*\$([a-zA-Z_][a-zA-Z0-9_]*))?\s*\)\s*\{/';
    
    // Callback function to replace foreach loops with type checks
    $updatedContent = preg_replace_callback($pattern, function ($matches) use ($filePath, &$logEntries) {
        $arrayVar = trim($matches[1]);
        $keyVar = isset($matches[2]) ? $matches[2] : null;
        $valueVar = isset($matches[3]) ? $matches[3] : $matches[2];

        $replacement = "if (is_array($arrayVar) || is_object($arrayVar)) { foreach ($arrayVar as " . (isset($keyVar) ? "\$$keyVar => " : "") . "\$$valueVar) {";

        $logEntries[] = "Updated foreach in file: $filePath\nOriginal: foreach ($arrayVar as " . (isset($keyVar) ? "\$$keyVar => " : "") . "\$$valueVar)\nUpdated: $replacement";
        
        return $replacement;
    }, $content);

    return $updatedContent;
}

// Replace these with the paths to your models, controllers, and libraries directories
$modelsDirectory = __DIR__ . '/app/Models';
$controllersDirectory = __DIR__ . '/app/Controllers';
$librariesDirectory = __DIR__ . '/app/Libraries';
// Replace this with the path to your log file
$logFile = __DIR__ . '/foreach_update_log.txt';

// Update foreach loops in Models directory
updateForeachLoops($modelsDirectory, $logFile);

// Update foreach loops in Controllers directory
updateForeachLoops($controllersDirectory, $logFile);

// Update foreach loops in Libraries directory
updateForeachLoops($librariesDirectory, $logFile);

echo "Foreach loop update complete. Log file: $logFile\n";
