<?php

namespace Grovo\AssetTranscoder;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use InvalidArgumentException;
use GuzzleHttp\Client;
use ZipArchive;

class ZamzarTranscoder implements Transcoder
{
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $jobsEndpoint;

    /**
     * @var string
     */
    private $filesEndpoint;

    /**
     * @var string
     */
    private $fileStoreDirectory;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * ZamzarTranscoder constructor.
     * @param string $apiKey
     * @param string $jobsEndpoint
     * @param string $filesEndpoint
     * @param string $fileStoreDirectory
     * @param bool $sslVerify
     */
    public function __construct(
        string $apiKey,
        string $jobsEndpoint,
        string $filesEndpoint,
        string $fileStoreDirectory,
        bool $sslVerify = true
    ) {
    
        $this->apiKey = $apiKey;
        $this->jobsEndpoint = $jobsEndpoint;
        $this->filesEndpoint = $filesEndpoint;
        $this->fileStoreDirectory = $fileStoreDirectory;
        $this->httpClient = new Client([
            'verify' => $sslVerify
        ]);
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $params
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws TranscoderConnectionFailureException
     */
    private function makeHttpRequest(string $method, string $endpoint, array $params = [])
    {
        try {
            $response = $this->httpClient->request(
                $method,
                $endpoint,
                array_merge(
                    $params,
                    [
                        'auth' => [$this->apiKey, ''],
                    ]
                )
            );

            return $response;
        } catch (ConnectException $e) {
            throw new TranscoderConnectionFailureException($e->getMessage());
        }
    }

    /**
     * Ping provider to verify we can start and query jobs
     *
     * @return bool
     */
    public function ping() : bool
    {
        $response = $this->makeHttpRequest(self::HTTP_METHOD_GET, $this->jobsEndpoint);
        $responseBody = json_decode($response->getBody(), true);
        if ($responseBody) {
            return true;
        }

        return false;
    }

    /**
     * @param string $filepath
     * @param string $targetFormat
     * @return string jobId
     */
    public function start(string $filepath, string $targetFormat): string
    {
        $sourceFile = fopen($filepath, 'r');
        if ($sourceFile === false) {
            throw new InvalidArgumentException("Failed to open {$filepath}");
        }

        $response = $this->makeHttpRequest(
            self::HTTP_METHOD_POST,
            $this->jobsEndpoint,
            [
                'multipart' => [
                    [
                        'name' => 'source_file',
                        'contents' => $sourceFile
                    ],
                    [
                        'name' => 'target_format',
                        'contents' => $targetFormat
                    ]
                ]
            ]
        );

        $jobInfo = json_decode($response->getBody(), true);
        return $jobInfo['id'];
    }


    /**
     * @param string $id
     * @return TranscoderJobStatus
     */
    public function getStatus(string $id): TranscoderJobStatus
    {
        try {
            $response = $this->makeHttpRequest(self::HTTP_METHOD_GET, $this->jobsEndpoint . "/{$id}");
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                throw new InvalidArgumentException("Job {$id} not found");
            }

            throw $e;
        }

        $jobInfo = json_decode($response->getBody(), true);

        $files = array_map(function ($tf) {
            return new TranscoderFile($tf['id'], $tf['name'], $tf['size']);
        }, $jobInfo['target_files'] ?? []);

        return new TranscoderJobStatus(
            $jobInfo['id'],
            $jobInfo['status'],
            $files
        );
    }

    /**
     * @param string $zipFilePath
     * @param string $imageFileBaseName
     * @return array
     */
    private function unzipArchive(string $zipFilePath, string $imageFileBaseName): array
    {
        $filePaths = [];

        $zip = new ZipArchive();
        $zip->open($zipFilePath);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $targetFileName = "{$imageFileBaseName}-{$i}.png";
            $zip->extractTo($this->fileStoreDirectory, [$targetFileName]);
            $filePaths[] = $this->fileStoreDirectory . "/" . $targetFileName;
        }

        $zip->close();

        return $filePaths;
    }

    /**
     * @param string $fileId
     * @param string $saveTo
     */
    private function downloadFile(string $fileId, string $saveTo)
    {
        $this->makeHttpRequest(
            self::HTTP_METHOD_GET,
            $this->filesEndpoint . "/{$fileId}/content",
            ['save_to' => $saveTo]
        );
    }

    /**
     * @param string $fileId
     * @return string
     */
    private function getTempFileName(string $fileId): string
    {
        return tempnam($this->fileStoreDirectory, "transcode-target-{$fileId}-");
    }

    /**
     * @param string $id
     * @return string[]
     * @throws TranscoderJobNotFinishedException
     */
    public function finish(string $id): array
    {
        $outputFileList = [];

        $jobStatus = $this->getStatus($id);
        if ($jobStatus->isWorking()) {
            throw new TranscoderJobNotFinishedException("Job {$id} is still in progress");
        }

        if ($jobStatus->isFailed()) {
            throw new TranscoderJobNotFinishedException("Job {$id} has failed");
        }

        $zipPackage = $jobStatus->getZipTargetFile();
        if ($zipPackage === null) {
            $targetFiles = $jobStatus->getTargetFiles();
            foreach ($targetFiles as $f) {
                $tmpFile = $this->getTempFileName($f->getId());
                $this->downloadFile($f->getId(), $tmpFile);
                $outputFileList[] = $tmpFile;
            }
        } else {
            $tmpZipFile = $this->getTempFileName($zipPackage->getId());
            $this->downloadFile($zipPackage->getId(), $tmpZipFile);
            $outputFileList = $this->unzipArchive($tmpZipFile, basename($zipPackage->getName(), ".zip"));
            unlink($tmpZipFile);
        }

        return $outputFileList;
    }
}
