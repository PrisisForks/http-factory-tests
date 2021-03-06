<?php

namespace Interop\Http\Factory;

use Interop\Http\Factory\UploadedFileFactoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

abstract class UploadedFileFactoryTestCase extends TestCase
{
    use StreamHelper;

    /**
     * @var UploadedFileFactoryInterface
     */
    protected $factory;

    /**
     * @return UploadedFileFactoryInterface
     */
    abstract protected function createUploadedFileFactory();

    public function setUp()
    {
        $this->factory = $this->createUploadedFileFactory();
    }

    protected function assertUploadedFile(
        $file,
        $content,
        $size = null,
        $error = null,
        $clientFilename = null,
        $clientMediaType = null
    ) {
        $this->assertInstanceOf(UploadedFileInterface::class, $file);
        $this->assertSame($content, (string) $file->getStream());
        $this->assertSame($size ?: strlen($content), $file->getSize());
        $this->assertSame($error ?: UPLOAD_ERR_OK, $file->getError());
        $this->assertSame($clientFilename, $file->getClientFilename());
        $this->assertSame($clientMediaType, $file->getClientMediaType());
    }

    public function testCreateUploadedFileWithString()
    {
        $content = 'i made this!';
        $size = strlen($content);
        $filename = $this->createTemporaryFile();

        file_put_contents($filename, $content);

        $file = $this->factory->createUploadedFile($filename);

        $this->assertUploadedFile($file, $content, $size);
    }

    public function testCreateUploadedFileWithClientFilenameAndMediaType()
    {
        $content = 'this is your capitan speaking';
        $upload = $this->createTemporaryResource($content);
        $error = UPLOAD_ERR_OK;
        $clientFilename = 'test.txt';
        $clientMediaType = 'text/plain';

        $file = $this->factory->createUploadedFile($upload, null, $error, $clientFilename, $clientMediaType);

        $this->assertUploadedFile($file, $content, null, $error, $clientFilename, $clientMediaType);
    }

    public function testCreateUploadedFileWithError()
    {
        $upload = $this->createTemporaryResource();
        $error = UPLOAD_ERR_NO_FILE;

        $file = $this->factory->createUploadedFile($upload, null, $error);

        // Cannot use assertUploadedFile() here because the error prevents
        // fetching the content stream.
        $this->assertInstanceOf(UploadedFileInterface::class, $file);
        $this->assertSame($error, $file->getError());
    }
}
