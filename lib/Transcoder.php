<?php

namespace Grovo\AssetTranscoder;

interface Transcoder
{
    const TARGET_FORMAT_PNG = 'png';
    const TARGET_FORMAT_PDF = 'pdf';

    /**
     * Ping provider to verify we can start and query jobs
     *
     * @return bool
     */
    public function ping() : bool;

    /**
     * Start a Powerpoint to PNG transcoding job
     *
     * @param string $filepath Path to ppt or pptx file
     * @param string $targetFormat
     * @return string Job ID
     */
    public function start(string $filepath, string $targetFormat) : string;

    /**
     * @param string $id Job ID
     * @return TranscoderJobStatus
     */
    public function getStatus(string $id) : TranscoderJobStatus;

    /**
     * @param string $id
     * @return string[]
     */
    public function finish(string $id) : array;
}
