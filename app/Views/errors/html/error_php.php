<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PHP Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f2f2f2;
        }
        .container {
            width: 80%;
            margin: 10px auto;
            border: 1px solid #990000;
            padding: 20px;
            background-color: #fff;
        }
        .error-title {
            color: #990000;
        }
        .backtrace {
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h4 class="error-title">A PHP Error was encountered</h4>

        <p>Severity: <?= esc($severity) ?></p>
        <p>Message: <?= esc($message) ?></p>
        <p>Filename: <?= esc($filepath) ?></p>
        <p>Line Number: <?= esc($line) ?></p>

        <?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === true): ?>
            <p>Backtrace:</p>
            <?php foreach (debug_backtrace() as $error): ?>
                <?php if (isset($error['file']) && strpos($error['file'], realpath(APPPATH)) !== 0): ?>
                    <div class="backtrace">
                        <p>File: <?= esc($error['file']) ?><br />
                        Line: <?= esc($error['line']) ?><br />
                        Function: <?= esc($error['function']) ?></p>
                    </div>
                <?php endif ?>
            <?php endforeach ?>
        <?php endif ?>
    </div>
</body>
</html>
