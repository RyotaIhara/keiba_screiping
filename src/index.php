<?php
require 'vendor/autoload.php';

use Goutte\Client;

/*
    実行コマンド：
    docker-compose up -d
    docker exec -it php-scraper php index.php
*/

$url = 'https://nar.netkeiba.com/race/shutuba.html?race_id=202444123107&rf=race_submenu';

$client = new Client();
$crawler = $client->request('GET', $url);

// HorseInfo内のHorseNameを取得
$crawler->filter('table.RaceTable01 td.HorseInfo span.HorseName')->each(function ($node, $index) {
    echo "Horse " . ($index + 1) . ": " . $node->text() . "\n";
});
