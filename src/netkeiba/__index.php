<?php

/*
    実行コマンド：
    docker-compose up -d
    docker exec -it php-scraper php netkeiba/index.php
*/

require_once(dirname(__FILE__)."/RaceCalendarMstImport.php");
require_once(dirname(__FILE__)."/RaceInfoImport.php");
require_once(dirname(__FILE__)."/RaceResultImport.php");
require_once(dirname(__FILE__)."/SummarizeCorrectAnswerRate.php");

require_once(dirname(__FILE__)."/Base.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");
require_once(dirname(__FILE__)."/GetDataFunction.php");

use Goutte\Client;

$client = new Client();

//$instance = new RaceCalendarMstImport($client);
//$instance->main();

//$instance = new RaceInfoImport($client);
//$instance->main();

//$instance = new RaceResultImport($client);
//$instance->main();

// データ集計
//$instance = new SummarizeCorrectAnswerRate($client);
//$instance->main();
