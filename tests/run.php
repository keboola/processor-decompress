<?php
require_once(__DIR__ . "/../vendor/autoload.php");

$testFolder = __DIR__;

$finder = new \Symfony\Component\Finder\Finder();
$finder->directories()->sortByName()->in($testFolder)->depth(0);
foreach ($finder as $testSuite) {
    print "Test " . $testSuite->getPathname() . "\n";
    $temp = new \Keboola\Temp\Temp("processor-decompress");
    $temp->initRunFolder();
    $temp->setPreserveRunFolder(true);

    $copyCommand = "cp -R " . $testSuite->getPathname() . "/source/data/* " . $temp->getTmpFolder();
    (new \Symfony\Component\Process\Process($copyCommand))->mustRun();

    mkdir($temp->getTmpFolder() . "/out/tables", 0777, true);
    mkdir($temp->getTmpFolder() . "/out/files", 0777, true);

    $runCommand = "php /code/main.php --data=" . $temp->getTmpFolder();
    $runProcess = new \Symfony\Component\Process\Process($runCommand);
    if (is_dir($testSuite->getPathname() . "/expected/")) {
        $runProcess->mustRun();
        $diffCommand = "diff --exclude=.gitkeep --ignore-all-space --recursive " . $testSuite->getPathname() . "/expected/data/out " . $temp->getTmpFolder() . "/out";
        $diffProcess = new \Symfony\Component\Process\Process($diffCommand);
        $diffProcess->run();
        if ($diffProcess->getExitCode() > 0) {
            print "\n" . $runProcess->getOutput() . "\n";
            print "\n" . $diffProcess->getOutput() . "\n";
            exit(1);
        }
    } else {
        $runProcess->run();
        if ($runProcess->getExitCode() != 1) {
            print "\n" . $runProcess->getOutput() . "\n";
            exit(1);
        }
    }
}
