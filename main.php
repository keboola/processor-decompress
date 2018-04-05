<?php
// Catch all warnings and notices
set_error_handler(
    function ($errno, $errstr, $errfile, $errline, array $errcontext) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
);
require __DIR__ . "/vendor/autoload.php";

$arguments = getopt("", ["data:"]);
if (!isset($arguments["data"])) {
    $dataFolder = "/data";
} else {
    $dataFolder = $arguments["data"];
}

$configFile = $dataFolder . "/config.json";
if (!file_exists($configFile)) {
    echo "Config file not found" . "\n";
    exit(2);
}

try {
    $jsonDecode = new \Symfony\Component\Serializer\Encoder\JsonDecode(true);
    $jsonEncode = new \Symfony\Component\Serializer\Encoder\JsonEncode();
    $config = $jsonDecode->decode(
        file_get_contents($dataFolder . "/config.json"),
        \Symfony\Component\Serializer\Encoder\JsonEncoder::FORMAT
    );
    $parameters = (new \Symfony\Component\Config\Definition\Processor())->processConfiguration(
        new \Keboola\Processor\Decompress\ConfigDefinition(),
        [isset($config["parameters"]) ? $config["parameters"] : []]
    );

    if (isset($parameters["compression_type"])) {
        // force compression type
        switch ($parameters["compression_type"]) {
            case 'gzip':
                $decompressFunction = '\Keboola\Processor\Decompress\decompressGzip';
                break;
            case 'zip':
                $decompressFunction = '\Keboola\Processor\Decompress\decompressZip';
                break;
        }

        $finder = new \Symfony\Component\Finder\Finder();
        $finder->notName("*.manifest")->in($dataFolder . "/in/files")->files();
        foreach ($finder as $sourceFile) {
            $decompressFunction($dataFolder, $sourceFile);
        }
    } else {
        // detect compression types by extension
        $finder = new \Symfony\Component\Finder\Finder();
        $finder->notName("*.gz")->notName("*.zip")->notName("*.manifest")->in($dataFolder . "/in/files")->files();
        foreach ($finder as $sourceFile) {
            throw new \Keboola\Processor\Decompress\Exception(
                "File " . $sourceFile->getPathname() . " is not an archive."
            );
        }

        // GZ
        $finder = new \Symfony\Component\Finder\Finder();
        $finder->name("*.gz")->in($dataFolder . "/in/files")->files();
        foreach ($finder as $sourceFile) {
            \Keboola\Processor\Decompress\decompressGzip($dataFolder, $sourceFile);
        }

        // ZIP
        $finder = new \Symfony\Component\Finder\Finder();
        $finder->name("*.zip")->in($dataFolder . "/in/files")->files();
        foreach ($finder as $sourceFile) {
            \Keboola\Processor\Decompress\decompressZip($dataFolder, $sourceFile);
        }

    }

} catch (\Keboola\Processor\Decompress\Exception $e) {
    echo $e->getMessage();
    exit(1);
}
