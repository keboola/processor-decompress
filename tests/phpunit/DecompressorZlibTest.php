<?php

declare(strict_types=1);

namespace Keboola\Tests\Processor\Decompress;

use Keboola\Component\UserException;
use Keboola\Processor\Decompress\Decompressor\DecompressorZlib;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DecompressorZlibTest extends TestCase
{
    private function getTmpPath(string $testId): string
    {
        $tmpDir = sys_get_temp_dir();
        $tmpDir .= "/" . $testId;
        return $tmpDir;
    }

    public function initTmpFolder(string $testId): void
    {
        $fs = new Filesystem();
        clearstatcache();
        if (!file_exists($this->getTmpPath($testId)) && !is_dir($this->getTmpPath($testId))) {
            $fs->mkdir($this->getTmpPath($testId), 0777);
        }
    }

    public function testDecompressionReturnCodeDecompressionFail(): void
    {
        $this->expectException(UserException::class);

        $testId = 'test-wrong-window';
        $testWrongZlibWindowValue = 9;

        $this->initTmpFolder($testId);
        $decompressor = new DecompressorZlib(
            $this->getTmpPath($testId),
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

        $this->initTmpFolder($testId);
        $decompressor = new DecompressorZlib(
            $this->getTmpPath($testId),
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

        $this->assertFileExists($this->getTmpPath($testId) . '/test_zlib');
    }
}
