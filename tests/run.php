<?php

use Keboola\Temp\Temp;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

require_once __DIR__ . "/../vendor/autoload.php";

$testFolder = __DIR__;

$finder = new Finder();
$fs = new Symfony\Component\Filesystem\Filesystem();
$finder->directories()->sortByName()->in($testFolder)->depth(0);
foreach ($finder as $testSuite) {
    print "Test " . $testSuite->getPathname() . "\n";
    $temp = new Temp("my-component");
    $temp->initRunFolder();

    $copyCommand = "cp -R " . $testSuite->getPathname() . "/source/data/* " . $temp->getTmpFolder();
    (new Process($copyCommand))->mustRun();

    $fs->mkdir([
        $temp->getTmpFolder() . "/in/tables",
        $temp->getTmpFolder() . "/in/files",
        $temp->getTmpFolder() . "/out/tables",
        $temp->getTmpFolder() . "/out/files"
    ]);

    $runCommand = "php /code/main.php --data=" . $temp->getTmpFolder();
    $runProcess = new Process($runCommand);
    $runProcess->run();
    if (($runProcess->getExitCode() > 0) && !file_exists($testSuite->getPathname() . "/expected")) {
        if ($runProcess->getExitCode() == 1) {
            print "Failed as expected ({$runProcess->getExitCode()}): {$runProcess->getOutput()} \n";
        } else {
            print "Failed unexpectedly {$runProcess->getExitCode()}\n";
            if ($runProcess->getOutput()) {
                print $runProcess->getOutput() . "\n";
            }
            if ($runProcess->getErrorOutput()) {
                print $runProcess->getErrorOutput() . "\n";
            }
            exit(1);
        }
    } else {
        $diffCommand = "diff --exclude=.gitkeep --ignore-all-space --recursive " .
            $testSuite->getPathname() . "/expected/data/out " . $temp->getTmpFolder() . "/out";
        $diffProcess = new Process($diffCommand);
        $diffProcess->run();
        if ($diffProcess->getExitCode() > 0) {
            print "\n" . $diffProcess->getOutput() . "\n";
            exit(1);
        }
    }
}
