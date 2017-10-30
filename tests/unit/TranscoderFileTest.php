<?php

namespace Grovo\TranscoderFileTest;

use Grovo\AssetTranscoder\TranscoderFile;
use PHPUnit\Framework\TestCase;

class TranscoderJobStatusTest extends TestCase
{
    public function testGetIdReturnsId()
    {
        $this->assertEquals('some-id', $this->getTranscoderFile()->getId());
    }

    public function testGetNameReturnsName()
    {
        $this->assertEquals('some-name', $this->getTranscoderFile()->getName());
    }

    public function testGetSizeReturnsName()
    {
        $this->assertEquals(100, $this->getTranscoderFile()->getSize());
    }

    private function getTranscoderFile()
    {
        return new TranscoderFile('some-id', 'some-name', 100);
    }
}
