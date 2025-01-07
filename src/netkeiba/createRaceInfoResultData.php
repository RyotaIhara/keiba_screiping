<?php

/*
    実行コマンド：
    docker-compose up -d
    docker exec -it php-scraper php netkeiba/createRaceInfoResultData.php
*/

require_once(dirname(__FILE__)."/GetRaceList.php");
require_once(dirname(__FILE__)."/RaceInfoImport.php");
require_once(dirname(__FILE__)."/RaceResultImport.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");
require_once(dirname(__FILE__)."/GetDataFunction.php");

use Goutte\Client;

$client = new Client();

$getDataFunction = new GetDataFunction($client);
$raceScheduleList = $getDataFunction->getRaceSchedule(array(
    'race_date' => '2024-01-01'
));

/*
$year = '2024';
$month = '04';
$day = '01';
$jyoCd = '45';
*/

foreach ($raceScheduleList as $raceSchedule) {
    list($year, $month, $day) = explode('-', $raceSchedule['race_date']);
    $jyoCd = $raceSchedule['jyo_cd'];

    $getRaceList = new GetRaceList($client, $year, $month, $day, $jyoCd);
    $countOfRaces = $getRaceList->getCountOfRaces();

    for ($raceNum = 1; $raceNum <= $countOfRaces; $raceNum++) {
        $raceInfoImport = new RaceInfoImport($client, $year, $month, $day, $jyoCd, $raceNum);
        $raceInfoImport->main();

        $raceResultImport = new RaceResultImport($client, $year, $month, $day, $jyoCd);
        //$raceResultImport->main();
    }

}
