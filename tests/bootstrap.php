<?php

if (PHP_VERSION_ID < 70300 || PHP_VERSION_ID >= 80400) {
    fwrite(STDERR, sprintf(
        "ENV_UNSUPPORTED_PHP_VERSION: TradeAxis market-data PHPUnit proof requires PHP >= 7.3 and < 8.4 for Lumen 8.3.4 clean output. Current PHP: %s. Use the documented operator/CI baseline before running PHPUnit evidence.\n",
        PHP_VERSION
    ));
    exit(2);
}

require dirname(__DIR__).'/vendor/autoload.php';
