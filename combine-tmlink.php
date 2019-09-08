<?php

ini_set('max_execution_time', 300);

/*$datesRaw = file_get_contents("../tmlink-dates.csv");

$datesMap = array();

// Index the data by year
$rows = str_getcsv($datesRaw, PHP_EOL);
$isFirst = true;
foreach ($rows as $row) {
    if (!$isFirst) {
        $rowWithFields = str_getcsv($row, ",");

        $datesMap[$rowWithFields[0]] = $rowWithFields[1];
    }
    $isFirst = false;
}

echo "<pre>";
print_r($datesMap);*/

$outputFile = "../tmlink-combined.csv";

$niceFile = fopen('../tmlink-nice.csv', 'r');
$datesFile = fopen('../tmlink-dates.csv', 'r');

echo "niceFile = $niceFile <br />";
echo "datesFile = $datesFile <br />";

// Ignore the header lines
$niceLine = fgets($niceFile);
$dateLine = fgets($datesFile);

if ($niceFile && $datesFile) {

    while (!feof($niceFile)) {
        $niceLine = fgets($niceFile);
        $niceFields = str_getcsv($niceLine, ",");
        $applicationId = $niceFields[0];

        echo "looking for applicationID = " . $applicationId . " from  NICE data<br />";

        if (isset($dateFields) && isset($dateFields[0]) && $dateFields[0] == $applicationId) {
            file_put_contents($outputFile, implode(",",array($niceFields[0], $niceFields[1], $dateFields[1])) . PHP_EOL,  FILE_APPEND);
            echo "wrote from first check<br />";
        }
        else {
            while (!feof($datesFile)) {
                $dateLine = fgets($datesFile);
                $dateFields = str_getcsv($dateLine, ",");

                echo "found date row for applicationID = " . $dateFields[0] . "<br />";

                if ($dateFields[0] == $applicationId) {
                    file_put_contents($outputFile, implode(",",array($niceFields[0], $niceFields[1], $dateFields[1])) . PHP_EOL,  FILE_APPEND);
                    echo "wrote from second check<br />";
                    break;
                }
                else if ($dateFields[0] > $applicationId) {
                    break;
                }
            }
        }
    }
    fclose($datesFile);
}
