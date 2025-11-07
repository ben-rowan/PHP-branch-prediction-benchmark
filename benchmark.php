#!/usr/bin/env php
<?php

// https://www.youtube.com/watch?v=-HNpim5x-IE

define('INPUT_DIR', DIRECTORY_SEPARATOR . 'tmp');
define('SORTED', 'sorted');
define('UNSORTED', 'unsorted');
define('SORTED_PATH', INPUT_DIR . DIRECTORY_SEPARATOR . SORTED . DIRECTORY_SEPARATOR);
define('UNSORTED_PATH', INPUT_DIR . DIRECTORY_SEPARATOR . UNSORTED . DIRECTORY_SEPARATOR);
define('RUN_DATA_FILENAME', 'run_data_%d.csv');

define('ITERATIONS', 100);

class Timer
{
    private array $startTimes = [];
    private array $runTimes = [];

    public function startRun(int $runNumber): void
    {
        $this->startTimes[$runNumber] = hrtime(true);
    }

    public function endRun(int $runNumber): void
    {
        $this->runTimes[$runNumber] = hrtime(true) - $this->startTimes[$runNumber];
    }

    public function calculateAvgRunTime(): float
    {
        return array_sum($this->runTimes) / count($this->runTimes);
    }
}

$timer = new Timer();

$runner = function (callable $testSubject, int $runNumber) use ($timer): mixed
{
    $timer->startRun($runNumber);
    $result = $testSubject();
    $timer->endRun($runNumber);

    return $result;
};

$createBranchPredictionTest = function (array $ints) {
    return function () use ($ints) {
        $sumOfElementsBelow128 = 0;
        $sumOfAllElements = 0;

        foreach ($ints as $int) {
            if ($int < 128) {
                $sumOfElementsBelow128 += $int;
            }

            $sumOfAllElements += $int;
        }

        return [$sumOfElementsBelow128, $sumOfAllElements];
    };
};

$createBranchPredictionTestWithExtraBranch = function (array $ints) {
    return function () use ($ints) {
        $sumOfElementsBelow128 = 0;
        $sumOfElementsAbove256 = 0;
        $sumOfAllElements = 0;

        foreach ($ints as $int) {
            if ($int < 128) {
                $sumOfElementsBelow128 += $int;
            }

            // Trying to make this distinct from the other branch.
            if ($int + $int > 256) {
                $sumOfElementsAbove256 += $int;
            }

            $sumOfAllElements += $int;
        }

        return [$sumOfElementsBelow128, $sumOfAllElements];
    };
};

$createSortTest = function (array $unsortedInts) {
    return function () use ($unsortedInts) {
        sort($unsortedInts);
    };
};

$createReadRunDataFromDisk = function (string $type) {
    return function (int $runNumber) use ($type) {
        $path = SORTED === $type ? SORTED_PATH : UNSORTED_PATH;
        $filename = sprintf(RUN_DATA_FILENAME, $runNumber);

        return explode(PHP_EOL, file_get_contents($path . $filename));
    };
};

$typeArg = $argv[1];

switch ($typeArg) {
    case 'sorted':
        $createData = $createReadRunDataFromDisk(SORTED);
        $createTest = $createBranchPredictionTest;
        break;
    case 'unsorted':
        $createData = $createReadRunDataFromDisk(UNSORTED);
        $createTest = $createBranchPredictionTest;
        break;
    case 'sorted_extra':
        $createData = $createReadRunDataFromDisk(SORTED);
        $createTest = $createBranchPredictionTestWithExtraBranch;
        break;
    case 'unsorted_extra':
        $createData = $createReadRunDataFromDisk(UNSORTED);
        $createTest = $createBranchPredictionTestWithExtraBranch;
        break;
    case 'array_sort':
        $createData = $createReadRunDataFromDisk(UNSORTED);
        $createTest = $createSortTest;
        break;
    default:
        throw new \RuntimeException("Invalid type argument provided: '$typeArg'");
}

for ($i = 1; $i <= ITERATIONS; $i++) {
    $progress = sprintf("Starting run number: %03d", $i);
    printf($progress);

    $runData = $createData($i);
    $runner($createTest($runData), $i);

    printf(str_repeat(chr(8), strlen($progress)));
}

printf("Avg run time (seconds): %.5f" . PHP_EOL, $timer->calculateAvgRunTime() / 1000000000);