<?php

require_once(dirname(__FILE__)."/Base.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

require_once(dirname(__FILE__)."/methodArgument/GetHorseRaceExpectedByRaceArguments.php");
require_once(dirname(__FILE__)."/methodArgument/IsWinExpectedHorseArguments.php");
require_once(dirname(__FILE__)."/methodArgument/GetResultInfoByRaceArguments.php");

use Goutte\Client;

class SummarizeCorrectAnswerRate extends Base {
    const DEFAUTL_START_TARGET_YEAR  = 2023;
    const DEFAUTL_END_TARGET_YEAR    = 2024;
    const DEFAUTL_STRAT_TARGET_MONTH = 1;
    const DEFAUTL_END_TARGET_MONTH   = 12;
    const DEFAUTL_START_TARGET_DAY   = 1;
    const DEFAUTL_END_TARGET_DAY     = 31;
    const DEFAUTL_STRAT_TARGET_RACE_FIELD_NO = 1;
    const DEFAUTL_END_TARGET_RACE_FIELD_NO   = 12;

    var $startTargetYear;
    var $endTargetYear;
    var $startTargetMonth;
    var $endTargetMonth;
    var $startTargetDay;
    var $endTargetDay;
    var $startTargetRaceFieldNo;
    var $endTargetRaceFieldNo;

    function __construct(Client $client){
        parent::__construct($client);
        
        $this->startTargetYear  = self::DEFAUTL_START_TARGET_YEAR;
        $this->endTargetYear    = self::DEFAUTL_END_TARGET_YEAR;
        $this->startTargetMonth = self::DEFAUTL_STRAT_TARGET_MONTH;
        $this->endTargetMonth   = self::DEFAUTL_END_TARGET_MONTH;
        $this->startTargetDay   = self::DEFAUTL_START_TARGET_DAY;
        $this->endTargetDay     = self::DEFAUTL_END_TARGET_DAY;
        $this->startTargetRaceFieldNo = self::DEFAUTL_STRAT_TARGET_RACE_FIELD_NO;
        $this->endTargetRaceFieldNo   = self::DEFAUTL_END_TARGET_RACE_FIELD_NO;
    }

    function main() {
        $totalIsWinExpectedHorseCount = 0;

        foreach ($this->generateDatesAndRaceNumbers(
            $this->startTargetYear,
            $this->endTargetYear,
            $this->startTargetMonth,
            $this->endTargetMonth,
            $this->startTargetDay,
            $this->endTargetDay,
            $this->startTargetRaceFieldNo,
            $this->endTargetRaceFieldNo
        ) as [$year, $month, $day, $raceFieldNo]) {
            $raceId = sprintf('%02d', $year) . NAR_RACE_FIELD_NO['oi'] . sprintf('%02d', $month) . sprintf('%02d', $day) . sprintf('%02d', $raceFieldNo);
            
            // レースの勝利予想馬を取得する
            $getHorseRaceExpectedByRaceArguments = new GetHorseRaceExpectedByRaceArguments($raceId);
            $expectedHorses = $this->getHorseRaceExpectedByRace($getHorseRaceExpectedByRaceArguments);

            // 予想馬が1位だったか数を集計する
            $isWinExpectedHorseArguments = new IsWinExpectedHorseArguments($raceId, $expectedHorses);
            if ($this->isWinExpectedHorse($isWinExpectedHorseArguments)) {
                $totalIsWinExpectedHorseCount++;
            }
        }

        var_dump('トータル数：' . $totalIsWinExpectedHorseCount);
    }

    /*
     * レースの勝利予想馬を取得するメソッド
     */
    function getHorseRaceExpectedByRace(GetHorseRaceExpectedByRaceArguments $arguments) {
        $raceId = $arguments->raceId;

        $dataTopUrl = NETKEIBA_DOMAIN_URL . 'race/data_top.html?race_id=' . $raceId;
        $crawler = $this->client->request('GET', $dataTopUrl);

        $results = [];

        // データ上位馬3頭の情報を取得
        $crawler->filter('div.DataPickupHorseWrap dl')->each(function ($node) use (&$results) {
            // 馬番の取得
            $horseNumber = $node->filter('span.Umaban_Num')->text();

            // 馬名 の取得
            $horseName = $node->filter('a.data_top_horse_link')->text();

            // 馬名のリンク先URLを取得
            $horseLink = $node->filter('a.data_top_horse_link')->attr('href');

            // 特徴データの取得
            $featureDatas = [];
            $node->filter('dd.PickupDataBox ul li')->each(function ($li) use (&$featureDatas) {
                $featureDatas[] = $li->text();
            });

            $results[] = [
                'horseNumber' => $horseNumber,
                'horseName' => $horseName,
                'horseLink' => $horseLink,
                'featureDatas' => $featureDatas,
            ];
        });

        return $results;
    }

    /*
     * レースの結果情報を取得するメソッド
     */
    function getResultInfoByRace(GetResultInfoByRaceArguments $arguments) {
        $resultUrl = NETKEIBA_DOMAIN_URL . 'race/result.html?race_id=' . $arguments->raceId;
        $crawler = $this->client->request('GET', $resultUrl);

        $results = [];

        // テーブル行をループ処理
        $crawler->filter('#All_Result_Table tbody tr')->each(function ($node) use (&$results) {
            $rank = $node->filter('div.Rank')->text();
            $horseNumber = $node->filter('td.Num.Waku div')->text();
            $horseName = $node->filter('span.Horse_Name a')->text();
            $horseLink = $node->filter('span.Horse_Name a')->attr('href');
            $sexAge = $node->filter('td.Horse_Info div.Horse_Info_Detail span.Detail_Left')->text();
            $jockeyWeight = $node->filter('span.JockeyWeight')->text();
            $jockeyName = $node->filter('td.Jockey a')->text();
            $time = $node->filter('span.RaceTime')->first()->text();

            $results[] = [
                'rank' => $rank,
                'horseNumber' => $horseNumber,
                'horseName' => $horseName,
                'horseLink' => $horseLink,
                'sexAge' => $sexAge,
                'jockeyWeight' => $jockeyWeight,
                'jockeyName' => $jockeyName,
                'time' => $time,
            ];
        });

        return $results;
    }

    /*
     * 予想馬が1位だったかを判定するメソッド
     */
    function isWinExpectedHorse(IsWinExpectedHorseArguments $arguments) {
        $getResultInfoByRaceArguments = new GetResultInfoByRaceArguments($arguments->raceId);
        $results = $this->getResultInfoByRace($getResultInfoByRaceArguments);

        foreach($results as $item) {
            if ($item['rank'] === '1') {
                foreach ($arguments->expectedHorses as $expectedHorse) {
                    if ($expectedHorse['horseNumber'] === $item['horseNumber']) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function generateDatesAndRaceNumbers($startYear, $endYear, $startMonth, $endMonth, $startDay, $endDay, $startRaceFieldNo, $endRaceFieldNo) {
        for ($year = $startYear; $year <= $endYear; $year++) {
            for ($month = $startMonth; $month <= $endMonth; $month++) {
                for ($day = $startDay; $day <= $endDay; $day++) {
                    for ($raceFieldNo = $startRaceFieldNo; $raceFieldNo <= $endRaceFieldNo; $raceFieldNo++) {
                        yield [$year, $month, $day, $raceFieldNo];
                    }
                }
            }
        }
    }

}

