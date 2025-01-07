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

    function getNumberOfRaces() {

        $year = $this->year;
        $month = $this->month;
        $day = $this->day;
        $jyoCd = $this->jyoCd;
        $kaisaiDate = $year . $jyoCd . $month . $day;

        //$raceListUrl = NETKEIBA_DOMAIN_URL . 'race/race_list.html?kaisai_date=' . $kaisaiDate;
        $raceListUrl = 'https://nar.netkeiba.com/top/race_list.html?kaisai_id=2024450401&kaisai_date=20240401';
        $crawler = $this->client->request('GET', $raceListUrl);

        //$html = $crawler->filter('.Race_Next')->parents('li')->outerHtml();
        //echo $html;

        /*
        var_dump($crawler->html());

        $crawler->filter('div.Race_Num')->each(function ($node) use (&$raceInfo) {
            $text = $node->text();

            var_dump($text);
        });
        */

    }

}

 