<?php

function extractFunctionsWithForeach($directory, $logFile)
{
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $regexIterator = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

    $logContent = "";

    foreach ($regexIterator as $file) {
        $filePath = $file[0];
        $fileContent = file_get_contents($filePath);

        // Skip files that don't contain any `foreach`
        if (strpos($fileContent, 'foreach') === false) {
            echo "No 'foreach' in file: $filePath\n"; // Debugging line
            continue;
        }

        echo "'foreach' found in file: $filePath\n"; // Debugging line

        // Use token_get_all to parse PHP code
        $tokens = token_get_all($fileContent);
        $namespace = '';
        $className = '';
        $functionName = '';
        $isClass = false;
        $isFunction = false;
        $hasForeach = false;
        $functionStart = false;
        $functionCode = '';
        $bracketCount = 0;

        foreach ($tokens as $token) {
            if (is_array($token)) {
                switch ($token[0]) {
                    case T_NAMESPACE:
                        $namespace = extractNamespace($tokens);
                        echo "Namespace: $namespace\n"; // Debugging line
                        break;

                    case T_CLASS:
                        $isClass = true;
                        break;

                    case T_STRING:
                        if ($isClass) {
                            $className = $token[1];
                            echo "Class: $className\n"; // Debugging line
                            $isClass = false;
                        } elseif ($isFunction) {
                            $functionName = $token[1];
                            echo "Function: $functionName\n"; // Debugging line
                            $isFunction = false;
                        }
                        break;

                    case T_FUNCTION:
                        $isFunction = true;
                        break;

                    case T_FOREACH:
                        $hasForeach = true;
                        echo "'foreach' detected in function: $functionName\n"; // Debugging line
                        break;
                }
            }

            if ($isFunction && $token === '{') {
                $functionStart = true;
                $bracketCount++;
                $functionCode = '';
            }

            if ($functionStart) {
                $functionCode .= is_array($token) ? $token[1] : $token;

                if ($token === '{') {
                    $bracketCount++;
                } elseif ($token === '}') {
                    $bracketCount--;
                }

                if ($bracketCount === 0) {
                    $functionStart = false;

                    if ($hasForeach && $functionName) {
                        $logContent .= "Controller: " . ($namespace ? $namespace . '\\' : '') . $className . " | Function: " . $functionName . "\n";
                        $logContent .= $functionCode . "\n\n";
                        echo "Logging function: $functionName in $className\n"; // Debugging line
                        $functionName = '';
                        $hasForeach = false;
                    }
                }
            }
        }
    }

    if (empty($logContent)) {
        $logContent = "No functions containing 'foreach' found.";
    }

    file_put_contents($logFile, $logContent);
}

function extractNamespace(&$tokens)
{
    $namespace = '';
    while ($token = next($tokens)) {
        if (is_array($token) && ($token[0] === T_STRING || $token[0] === T_NS_SEPARATOR)) {
            $namespace .= $token[1];
        } elseif ($token === ';') {
            break;
        }
    }
    return $namespace;
}

// Set the directory to your controllers directory
$controllersDirectory = __DIR__ . '/app/Controllers';
// Set the log file path
$logFilePath = __DIR__ . '/foreach_functions_log.txt';
extractFunctionsWithForeach($controllersDirectory, $logFilePath);

echo "Log written to $logFilePath\n";
