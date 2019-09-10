<?php

declare(strict_types=1);

namespace Keboola\Tests\Processor\Decompress;

use Keboola\Component\UserException;
use Keboola\Processor\Decompress\Decompressor\DecompressorZlib;
use Keboola\Temp\Temp;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DecompressorZlibTest extends TestCase
{
    public function testDecompressionReturnCodeDecompressionFail(): void
    {
        $this->expectException(UserException::class);

        $testId = 'test-wrong-window';
        $testWrongZlibWindowValue = 9;

        $temp = new Temp($testId);
        $temp->initRunFolder();

        $decompressor = new DecompressorZlib(
            $temp->getTmpFolder(),
            new NullLogger(),
            false,
            $testWrongZlibWindowValue
        );

        $finder = new Finder();
        $files = $finder->name('test_zlib')->in(__DIR__ . '/../functional/zlib-simple/source/data/in/files')->files();

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $decompressor->decompress($file);
        }
    }

    public function testDecompressionSuccess(): void
    {
        $testId = 'test-success-zlib-decompress';
        $testRightZlibWindowValue = 15;

        $temp = new Temp($testId);
        $temp->initRunFolder();
        $decompressor = new DecompressorZlib(
            $temp->getTmpFolder(),
            new NullLogger(),
            false,
            $testRightZlibWindowValue
        );

        $finder = new Finder();
        $files = $finder->name('test_zlib')->in(__DIR__ . '/../functional/zlib-simple/source/data/in/files')->files();

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $decompressor->decompress($file);
        }

        $this->assertFileExists($temp->getTmpFolder() . '/test_zlib');
    }
}
