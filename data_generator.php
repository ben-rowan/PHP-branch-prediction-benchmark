#!/usr/bin/env php
<?php

define('OUTPUT_DIR', DIRECTORY_SEPARATOR . 'tmp');
define('SORTED_DIR', 'sorted');
define('UNSORTED_DIR', 'unsorted');
define('SORTED_PATH', OUTPUT_DIR . DIRECTORY_SEPARATOR . SORTED_DIR . DIRECTORY_SEPARATOR);
define('UNSORTED_PATH', OUTPUT_DIR . DIRECTORY_SEPARATOR . UNSORTED_DIR . DIRECTORY_SEPARATOR);
define('RUN_DATA_FILENAME', 'run_data_%d.csv');

define('ITERATIONS', 100);
define('NUM_RANDOM_INTS', 10000000);

if (!is_dir(SORTED_PATH)) {
    mkdir(SORTED_PATH);
}

if (!is_dir(UNSORTED_PATH)) {
    mkdir(UNSORTED_PATH);
}

for ($i = 1; $i <= ITERATIONS; $i++) {
    $progress = sprintf('Generating data for run: %03d', $i);
    
    printf($progress);

    $unsorted = [];

    for ($j = 1; $j <= NUM_RANDOM_INTS; $j++) {
        $unsorted[] = rand(0, 255);
    }

    $sorted = $unsorted;

    sort($sorted);

    $filename = sprintf(RUN_DATA_FILENAME, $i);
    file_put_contents(SORTED_PATH . $filename, implode(PHP_EOL, $sorted));
    file_put_contents(UNSORTED_PATH . $filename, implode(PHP_EOL, $unsorted));

    printf(str_repeat(chr(8), strlen($progress)));
}

printf('Done' . PHP_EOL);