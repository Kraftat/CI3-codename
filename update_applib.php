<?php

function updateServices($directory)
{
    $logFile = 'update_services_log.txt';
    file_put_contents($logFile, "Update Log\n\n");

    try {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    } catch (UnexpectedValueException $e) {
        echo "Error: " . $e->getMessage() . "\n";
        return;
    }

    // List of services to update
    $services = [
        'appLib',
        'bigbluebuttonLib',
        'bulk',
        'bulksmsbd',
        'ciqrcode',
        'clickatell',
        'csvimport',
        'customSms',
        'html2pdf',
        'mailer',
        'midtransPayment',
        'msg91',
        'paypalPayment',
        'paytmKitLib',
        'razorpayPayment',
        'recaptcha',
        'smscountry',
        'sslcommerz',
        'stripePayment',
        'tapPayments',
        'textlocal',
        'twilio',
        'zoomLib'
    ];

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() == 'php') {
            $filePath = $file->getRealPath();
            $fileContent = file_get_contents($filePath);
            $originalContent = $fileContent;

            // Check if the file is a controller by looking for 'namespace App\Controllers'
            if (strpos($fileContent, 'namespace App\Controllers') !== false) {
                $updateRequired = false;
                
                foreach ($services as $service) {
                    // Only include the service if it's mentioned in the controller
                    if (strpos($fileContent, $service) !== false) {
                        $updateRequired = true;

                        // Add 'public $service;' declaration if not already present
                        if (strpos($fileContent, "public \$$service;") === false) {
                            $fileContent = preg_replace('/(class\s+\w+\s+extends\s+AdminController\s*\{)/', "$1\n    public \$$service;", $fileContent);
                        }

                        // Add '$this->service = service(\'service\');' initialization in the constructor if not already present
                        if (strpos($fileContent, "\$this->$service = service('$service');") === false) {
                            $fileContent = preg_replace('/(parent::__construct\(\);\s*)/', "$1\n        \$this->$service = service('$service');", $fileContent);
                        }
                    }
                }

                // Log changes if content has changed
                if ($updateRequired && $fileContent !== $originalContent) {
                    file_put_contents($filePath, $fileContent);
                    file_put_contents($logFile, "Updated: $filePath\n", FILE_APPEND);
                }
            }
        }
    }

    echo "Update completed. Check the log file for details.\n";
}

// Define the directory to scan
$directoryToUpdate = __DIR__ . '/app/Controllers'; // Change this to your controllers directory

// Run the update function
updateServices($directoryToUpdate);
?>
