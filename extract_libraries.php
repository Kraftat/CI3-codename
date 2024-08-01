<?php

// Directory containing your libraries
$librariesDir = __DIR__ . '/app/Libraries';

// Function to fetch library data from Packagist
function checkPackagist($libraryName) {
    $url = "https://repo.packagist.org/p2/{$libraryName}.json";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    // Check HTTP status code
    if ($info['http_code'] == 200) {
        return json_decode($output, true);
    }

    return false;
}

// Read the Libraries directory
$libraries = scandir($librariesDir);

// Check each item to see if it could be a Composer package
foreach ($libraries as $lib) {
    if ($lib === '.' || $lib === '..' || $lib === '.gitkeep') continue;

    // Assume directory name or file name (without extension) could be a package name
    $packageName = strtolower($lib);
    $packageData = checkPackagist("vendorname/{$packageName}"); // You will need to modify "vendorname" accordingly

    if ($packageData) {
        echo "Package found for {$lib}: Could potentially be managed via Composer.\n";
    } else {
        echo "No Packagist package found for {$lib}.\n";
    }
}
