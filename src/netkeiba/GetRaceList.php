<?php

require_once(dirname(__FILE__)."/Base.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

use Goutte\Client;

class GetRaceList extends Base {
    var $year;
    var $month;
    var $day;
    var $jyoCd;
    var $raceNum;

    function __construct(Client $client, $year, $month, $day, $jyoCd){
        parent::__construct($client);

        // 年
        $this->year  = $year;

        // 月
        $this->month  = $month;

        // 日にち
        $this->day  = $day;

        // 競馬場のコード
        $this->jyoCd  = $jyoCd;
    }

    function getCountOfRaces() {

        $year = $this->year;
        $month = $this->month;
        $day = $this->day;
        $jyoCd = $this->jyoCd;
        $kaisaiDate = $year . $jyoCd . $month . $day;

        //$raceListUrl = NETKEIBA_DOMAIN_URL . 'race/race_list.html?kaisai_date=' . $kaisaiDate;
        $raceListUrl = 'https://nar.netkeiba.com/race/shutuba.html?race_id=202545010201';
        $crawler = $this->client->request('GET', $raceListUrl);

        $crawler->filter('div.RaceNumWrap ul.fc li')->each(function ($node) use (&$results) {
            $link = $node->filter('a');
            if ($link->count() > 0) {
                $results[] = trim($link->text());
            }
        });

        return count($results);

    }

}

 