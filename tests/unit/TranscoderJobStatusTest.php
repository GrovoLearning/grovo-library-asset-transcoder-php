<?php

namespace Grovo\AssetTranscoderTest;

use Grovo\AssetTranscoder\TranscoderFile;
use Grovo\AssetTranscoder\TranscoderJobStatus;
use PHPUnit\Framework\TestCase;

class TranscoderJobStatusTest extends TestCase
{
    public function testGetZipTargetFileReturnsFilesWithZipExtension()
    {
        $status = new TranscoderJobStatus(
            "jib",
            TranscoderJobStatus::STATUS_WORKING,
            [
                new TranscoderFile("f1", "f1.zip", 1),
                new TranscoderFile("f2", "f2.png", 1),
                new TranscoderFile("f3", "f3.png", 1),
            ]
        );

        $this->assertEquals(
            new TranscoderFile("f1", "f1.zip", 1),
            $status->getZipTargetFile()
        );
    }

    public function testIsSuccessfulReturnsTrueForSuccessState()
    {
        $status = new TranscoderJobStatus(
            "jib",
            TranscoderJobStatus::STATUS_SUCCESS
        );

        $this->assertEquals(true, $status->isSuccessful());
    }

    public function testIsFailedReturnsTrueForSuccessState()
    {
        $status = new TranscoderJobStatus(
            "jib",
            TranscoderJobStatus::STATUS_FAIL
        );

        $this->assertEquals(true, $status->isFailed());
    }

    public function testIsWorkingReturnsTrueForSuccessState()
    {
        $status = new TranscoderJobStatus(
            "jib",
            TranscoderJobStatus::STATUS_WORKING
        );

        $this->assertEquals(true, $status->isWorking());
    }
}
