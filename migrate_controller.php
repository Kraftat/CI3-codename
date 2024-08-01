<?php

function updateFormValidationAndModels($directory)
{
    $logFile = 'update_log.txt';
    file_put_contents($logFile, "Update Log\n\n");

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() == 'php') {
            $filePath = $file->getRealPath();
            $fileContent = file_get_contents($filePath);
            $originalContent = $fileContent;

            // Update form_validation to validation
            $fileContent = preg_replace(
                '/\$form_validation\b/',
                '$validation',
                $fileContent
            );

            // Correct application model usage
            $fileContent = preg_replace(
                '/public \$application_model;/',
                'public $applicationModel;',
                $fileContent
            );

            // Generalize model instantiation for public properties
            $fileContent = preg_replace(
                '/public \$([a-zA-Z_]+)_model;/',
                'public $$1Model;',
                $fileContent
            );

            // Generalize model instantiation in constructors
            $fileContent = preg_replace(
                '/\$this->([a-zA-Z_]+)_model = new \App\\\Models\\\([a-zA-Z_]+)Model\(\);/',
                '$this->$1Model = new \\App\\Models\\$2Model();',
                $fileContent
            );

            // Correct model references within methods
            $fileContent = preg_replace(
                '/\$this->([a-zA-Z_]+)_model->/',
                '$this->$1Model->',
                $fileContent
            );

            // Log changes if content has changed
            if ($fileContent !== $originalContent) {
                file_put_contents($filePath, $fileContent);
                file_put_contents($logFile, "Updated: $filePath\n", FILE_APPEND);
                file_put_contents($logFile, "Before:\n$originalContent\n", FILE_APPEND);
                file_put_contents($logFile, "After:\n$fileContent\n", FILE_APPEND);
            }
        }
    }

    echo "Update completed. Check the log file for details.\n";
}

// Define the directory to scan
$controllersDirectory = __DIR__ . '/app/Controllers';
$modelsDirectory = __DIR__ . '/app/Models';

// Run the update function for controllers and models
updateFormValidationAndModels($controllersDirectory);
updateFormValidationAndModels($modelsDirectory);

?>
