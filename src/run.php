<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

try {
    $app = new Keboola\Processor\Decompress\Component();
    $app->run();
    exit(0);
} catch (\Keboola\Component\UserException $e) {
    echo $e->getMessage();
    exit(1);
} catch (\Throwable $e) {
    echo get_class($e) . ':' . $e->getMessage();
    echo "\nFile: " . $e->getFile();
    echo "\nLine: " . $e->getLine();
    echo "\nCode: " . $e->getCode();
    echo "\nTrace: " . $e->getTraceAsString() . "\n";
    exit(2);
}
