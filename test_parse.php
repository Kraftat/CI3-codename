<?php

require 'vendor/autoload.php';

use PhpParser\ParserFactory;
use PhpParser\Error;

function testParserFactory()
{
    $parserFactory = new ParserFactory();
    $parser = $parserFactory->createForNewestSupportedVersion();

    $code = '<?php echo "Hello, world!"; ?>';

    try {
        $stmts = $parser->parse($code);
        if ($stmts) {
            echo "Parsed Successfully\n";
        } else {
            echo "Parsing Failed\n";
        }
    } catch (Error $e) {
        echo 'Parse Error: ', $e->getMessage(), "\n";
    }
}

testParserFactory();
