<?php

/*
    実行コマンド：
    docker-compose up -d
    docker exec -it php-scraper php netkeiba/createRaceScheduleData.php
*/

require_once(dirname(__FILE__)."/RaceCalendarMstImport.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

use Goutte\Client;

$client = new Client();

$startTargetYear = date('Y');
$endTargetYear = date('Y');
//$startTargetYear = '2024';
//$endTargetYear = '2024';

$startTargetMonth = '01';
$endTargetMonth = '01';

$targetDateParams = array(
    'startTargetYear' => $startTargetYear,
    'endTargetYear' => $endTargetYear,
    'startTargetMonth' => $startTargetMonth,
    'endTargetMonth' => $endTargetMonth,
);

$raceCalendarMstImport = new RaceCalendarMstImport($client, $targetDateParams);
$raceCalendarMstImport->main();

