<?php

/*
    実行コマンド：
    docker-compose up -d
    docker exec -it php-scraper php netkeiba/index.php
*/

require_once(dirname(__FILE__)."/SummarizeCorrectAnswerRate.php");
require_once(dirname(__FILE__)."/Base.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

use Goutte\Client;

$client = new Client();

$summarizeCorrectAnswerRate = new SummarizeCorrectAnswerRate($client);
$summarizeCorrectAnswerRate->main();
