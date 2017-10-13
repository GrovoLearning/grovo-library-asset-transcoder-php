# grovo-library-asset-transcoder-php

[![CircleCI](https://circleci.com/gh/GrovoLearning/grovo-library-asset-transcoder-php.svg?style=svg&circle-token=c3a0b26a76eb797b8a8f7723e0e517d223dacbe9)](https://circleci.com/gh/GrovoLearning/grovo-library-asset-transcoder-php) [![codecov](https://codecov.io/gh/GrovoLearning/grovo-library-asset-transcoder-php/branch/master/graph/badge.svg?token=3G9Xec1LC8)](https://codecov.io/gh/GrovoLearning/grovo-library-asset-transcoder-php)


Asset transcoder interface and an implementation using the [Zamzar](https://developers.zamzar.com/) API for conversion.

## ZamzarTranscoder

### Required environment variables
- **API_KEY**: Zamzar API key
- **JOBS_URL**: Base URL for Zamzar jobs (see https://developers.zamzar.com/docs#section-Jobs)
- **FILES_URL**: Base URL for Zamzar files (see https://developers.zamzar.com/docs#section-Files)
- **OUTPUT_DIR**: Local path where to store transcoded files

### Input formats supported
The library does not enforce a restriction on input files. An unsupported input file will result in a failed job.

### Output formats supported
- TARGET_FORMAT_PNG
- TARGET_FORMAT_PDF

This is based on current needs and can be expanded based on needs and what's supported by the transcoding provider.

### Construct `ZamzarTranscoder`

```php
$transcoder = new ZamzarTranscoder(
    getenv('API_KEY'),
    getenv('JOBS_URL'),
    getenv('FILES_URL'),
    getenv('OUTPUT_DIR'),
    true /* sslVerify */
);
```

### Start a conversion job

```php
$jobId = $transcoder->start("source.docx", Transcoder::TARGET_FORMAT_PDF);
```

### Check the status of a job

```php
$status = $transcoder->getStatus($jobId);
```

`$status` is an instance of `TranscoderJobStatus`

 `TranscoderJobStatus::isWorking()` will return `true` if the job is initializing or still being worked on, and return `false` if the job has finished (either successfully or unsuccessfully).

 `TranscoderJobStatus::isSuccessful()` will return `true` if the job was successful, `false` otherwise.

### Finishing the job and getting the output files

```php
$outputFileList = $transcoder->finish($jobId);
```

`$outputFileList` will be an array of output files written to disk. 

Note: while Zamzar may provide a single zip file for jobs that produce multiple files, the transcoder will unzip the file in these cases and `$outputFileList` will be the list of list of files within the zip archive.

