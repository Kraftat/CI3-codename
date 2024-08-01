<?php

// Define constants for the application path and log file
define('APP_PATH', __DIR__ . '/app');
define('LOG_FILE', __DIR__ . '/scan_results.txt');

// Include the necessary CodeIgniter classes
require __DIR__ . '/vendor/codeigniter4/framework/system/Autoloader/Autoloader.php';
require __DIR__ . '/vendor/codeigniter4/framework/system/Common.php';
require __DIR__ . '/vendor/codeigniter4/framework/system/Config/AutoloadConfig.php';
require __DIR__ . '/vendor/codeigniter4/framework/system/Config/BaseConfig.php';
require __DIR__ . '/vendor/codeigniter4/framework/system/Config/Modules.php';
require __DIR__ . '/vendor/codeigniter4/framework/system/Services.php';

use Config\Services;
use Config\Database;

function scanDirectory($dir, &$results = []) {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $results[] = $path;
        } elseif ($value != "." && $value != "..") {
            scanDirectory($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}

function logMessage($message) {
    file_put_contents(LOG_FILE, $message . PHP_EOL, FILE_APPEND);
}

function checkFile($file) {
    $content = file_get_contents($file);
    $tokens = token_get_all($content);
    
    $namespace = '';
    $class = '';
    $extends = '';
    $functions = [];
    $calledMethods = [];

    for ($i = 0; $i < count($tokens); $i++) {
        if ($tokens[$i][0] === T_NAMESPACE) {
            $namespace = getNamespace($tokens, $i);
        } elseif ($tokens[$i][0] === T_CLASS) {
            $class = getClassName($tokens, $i);
        } elseif ($tokens[$i][0] === T_EXTENDS) {
            $extends = getExtends($tokens, $i);
        } elseif ($tokens[$i][0] === T_FUNCTION) {
            $functions[] = getFunctionName($tokens, $i);
        } elseif ($tokens[$i][0] === T_OBJECT_OPERATOR || $tokens[$i][0] === T_DOUBLE_COLON) {
            $calledMethods[] = getCalledMethod($tokens, $i);
        }
    }

    return [
        'namespace' => $namespace,
        'class' => $class,
        'extends' => $extends,
        'functions' => $functions,
        'calledMethods' => $calledMethods
    ];
}

function getNamespace($tokens, &$i) {
    $namespace = '';
    for ($j = $i + 1; $j < count($tokens); $j++) {
        if ($tokens[$j][0] === T_STRING || $tokens[$j][0] === T_NS_SEPARATOR) {
            $namespace .= $tokens[$j][1];
        } else {
            break;
        }
    }
    return $namespace;
}

function getClassName($tokens, &$i) {
    for ($j = $i + 1; $j < count($tokens); $j++) {
        if ($tokens[$j][0] === T_STRING) {
            return $tokens[$j][1];
        }
    }
    return '';
}

function getExtends($tokens, &$i) {
    for ($j = $i + 1; $j < count($tokens); $j++) {
        if ($tokens[$j][0] === T_STRING) {
            return $tokens[$j][1];
        }
    }
    return '';
}

function getFunctionName($tokens, &$i) {
    for ($j = $i + 1; $j < count($tokens); $j++) {
        if ($tokens[$j][0] === T_STRING) {
            return $tokens[$j][1];
        }
    }
    return '';
}

function getCalledMethod($tokens, &$i) {
    for ($j = $i + 1; $j < count($tokens); $j++) {
        if ($tokens[$j][0] === T_STRING) {
            return $tokens[$j][1];
        }
    }
    return '';
}

function analyzeFiles($files) {
    $results = [];
    foreach ($files as $file) {
        if (preg_match('/\.php$/', $file)) {
            $results[] = checkFile($file);
        }
    }
    return $results;
}

function validateClasses($results) {
    foreach ($results as $result) {
        if (isset($result['class']) && $result['class'] !== '') {
            if (strpos($result['namespace'], 'Controllers') !== false && $result['extends'] !== 'BaseController') {
                logMessage("Controller {$result['namespace']}\\{$result['class']} does not extend BaseController.");
            }
            if (strpos($result['namespace'], 'Models') !== false && $result['extends'] !== 'Model') {
                logMessage("Model {$result['namespace']}\\{$result['class']} does not extend Model.");
            }
            if (strpos($result['namespace'], 'Libraries') !== false && empty($result['extends'])) {
                logMessage("Library {$result['namespace']}\\{$result['class']} should extend a base library class.");
            }
        }
    }
}

function checkMethods($results) {
    $definedMethods = [];
    $calledMethods = [];

    foreach ($results as $result) {
        if (isset($result['class']) && $result['class'] !== '') {
            $fullClassName = "{$result['namespace']}\\{$result['class']}";
            foreach ($result['functions'] as $function) {
                $definedMethods[$fullClassName][] = $function;
            }
            foreach ($result['calledMethods'] as $method) {
                $calledMethods[$fullClassName][] = $method;
            }
        }
    }

    foreach ($calledMethods as $class => $methods) {
        foreach ($methods as $method) {
            $methodFound = false;
            foreach ($definedMethods as $definedClass => $definedMethodsArray) {
                if (in_array($method, $definedMethodsArray)) {
                    $methodFound = true;
                    break;
                }
            }
            if (!$methodFound) {
                logMessage("Method {$method} called in {$class} is not defined in any model or library.");
            }
        }
    }
}

function validateRoutes() {
    $routes = Services::routes();
    $controllerPaths = scanDirectory(APP_PATH . '/Controllers');
    $controllers = [];

    foreach ($controllerPaths as $path) {
        if (preg_match('/\.php$/', $path)) {
            $controllers[] = str_replace([APP_PATH . '/Controllers/', '.php'], '', $path);
        }
    }

    foreach ($routes->getRoutes() as $route => $handler) {
        if (is_string($handler)) {
            $handlerParts = explode('::', $handler);
            if (count($handlerParts) === 2) {
                $controller = str_replace('\\', '/', $handlerParts[0]);
                if (!in_array($controller, $controllers)) {
                    logMessage("Route {$route} points to undefined controller {$controller}.");
                }
            }
        }
    }
}

// Initialize log file
file_put_contents(LOG_FILE, "Scan Results:\n\n");

// Scan the directories
$files = [];
$files = scanDirectory(APP_PATH . '/Controllers', $files);
$files = scanDirectory(APP_PATH . '/Models', $files);
$files = scanDirectory(APP_PATH . '/Libraries', $files);

// Analyze files
$results = analyzeFiles($files);

// Validate classes
validateClasses($results);

// Check method definitions and calls
checkMethods($results);

// Validate routes
validateRoutes();

echo "Scan complete. Check the log file at " . LOG_FILE . " for details.\n";

?>
