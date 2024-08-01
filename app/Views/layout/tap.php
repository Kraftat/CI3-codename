<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tap Payment Gateway</title>
</head>
<body>
    <h1>Processing Payment...</h1>
    <script src="https://secure.gosell.io/js/sdk/tap.min.js"></script>
    <script>
        // Redirect immediately to Tap's payment page
        window.location.href = '<?php echo $tap_url; ?>';
    </script>
</body>
</html>
