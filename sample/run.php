<?php

include "../vendor/autoload.php";

use Grovo\AssetTranscoder\ZamzarTranscoder;

$transcoder = new ZamzarTranscoder(
    getenv('API_KEY'),
    getenv('JOBS_URL'),
    getenv('FILES_URL'),
    getenv('OUTPUT_DIR'),
    false
);


echo "Starting conversion... ";
$jobId = $transcoder->start($argv[1], \Grovo\AssetTranscoder\Transcoder::TARGET_FORMAT_PDF);
echo "job ({$jobId}) scheduled.\n";

while (true) {
    echo "Checking status of job ({$jobId})... ";

    $s = $transcoder->getStatus($jobId);
    if ($s->isWorking()) {
        echo "working.\n";
    } else {
        if ($s->isSuccessful()) {
            echo "done (successful).\n";
        } else {
            echo "done (failed).\n";
        }
        break;
    }

    sleep(2);
}

echo "Downloading output... \n";
$output = $transcoder->finish($jobId);
foreach ($output as $of) {
    echo "Wrote " . $of . " .\n";
}
echo "done.\n";
