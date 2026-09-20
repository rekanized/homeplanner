<?php

// Keep a generated key alongside SQLite, with no manual configuration required.
$path = dirname(getenv('DB_DATABASE')).'/app.key';
$file = fopen($path, 'c+');

if ($file === false || ! flock($file, LOCK_EX)) {
    throw new RuntimeException('Unable to open the application key file.');
}

try {
    if (! chmod($path, 0600)) {
        throw new RuntimeException('Unable to secure the application key file.');
    }

    $key = trim(stream_get_contents($file));

    if ($key === '') {
        $key = 'base64:'.base64_encode(random_bytes(32));

        if (fwrite($file, $key) !== strlen($key) || ! fflush($file)) {
            throw new RuntimeException('Unable to save the application key.');
        }
    }

    echo $key;
} finally {
    flock($file, LOCK_UN);
    fclose($file);
}
