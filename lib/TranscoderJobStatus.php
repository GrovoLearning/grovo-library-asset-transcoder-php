<?php

namespace Grovo\AssetTranscoder;

class TranscoderJobStatus
{
    const STATUS_INIT = 'initialising';
    const STATUS_SUCCESS = 'successful';
    const STATUS_FAIL = 'failed';
    const STATUS_WORKING = 'converting';

    const ZIP_FILE_EXTENSION = '.zip';

    /**
     * @var string
     */
    private $jobId;

    /**
     * @var string
     */
    private $status;

    /**
     * @var TranscoderFile[]
     */
    private $targetFiles;

    /**
     * TranscoderJobStatus constructor.
     * @param string $jobId
     * @param string $status
     * @param TranscoderFile[] $targetFiles
     */
    public function __construct(string $jobId, string $status, array $targetFiles = [])
    {
        $this->jobId = $jobId;
        $this->status = $status;
        $this->targetFiles = $targetFiles;
    }

    /**
     * @return string
     */
    public function getJobId(): string
    {
        return $this->jobId;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return ($this->status === self::STATUS_SUCCESS);
    }

    /**
     * @return bool
     */
    public function isFailed(): bool
    {
        return ($this->status === self::STATUS_FAIL);
    }

    /**
     * @return bool
     */
    public function isWorking(): bool
    {
        return ($this->status === self::STATUS_INIT || $this->status === self::STATUS_WORKING);
    }

    /**
     * @return TranscoderFile[]
     */
    public function getTargetFiles()
    {
        return $this->targetFiles;
    }

    /**
     * @return TranscoderFile
     */
    public function getZipTargetFile()
    {
        foreach ($this->targetFiles as $targetFile) {
            $name = $targetFile->getName();

            if (substr($name, strlen($name)-4) === self::ZIP_FILE_EXTENSION) {
                return $targetFile;
            }
        }

        return null;
    }
}
