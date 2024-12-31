<?php

require_once(dirname(__FILE__)."/Base.php");
require_once(dirname(__FILE__)."/methodArgument/GetHorseRaceExpectedByRaceArguments.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

use Goutte\Client;


class SummarizeDataAnalysisResult extends Base {
    function __construct(Client $client){
        parent::__construct($client);
    }

    function main() {
        $targetYear   = '2024';
        $targetMonth  = '12';
        $targetDay    = '31';
        $targetRaceNo = '04';
        $narRaceFieldNo =  NAR_RACE_FIELD_NO['oi'];
        $arguments = new GetHorseRaceExpectedByRaceArguments($targetYear, $targetMonth, $targetDay, $targetRaceNo, $narRaceFieldNo);

        $results = $this->getHorseRaceExpectedByRace($arguments);

        var_dump($results);
    }

    /*
     * レースの勝利予想馬を取得するメソッド
     */
    function getHorseRaceExpectedByRace(GetHorseRaceExpectedByRaceArguments $arguments) {
        $targetYear   = $arguments->targetYear;
        $targetMonth  = $arguments->targetMonth;
        $targetDay    = $arguments->targetDay;
        $targetRaceNo = $arguments->targetRaceNo;
        $narRaceFieldNo = $arguments->narRaceFieldNo;

        $raceId = $targetYear . $narRaceFieldNo . $targetMonth . $targetDay . $targetRaceNo;

        $dataTopUrl = NETKEIBA_DOMAIN_URL . 'race/data_top.html?race_id=' . $raceId;
        $crawler = $this->client->request('GET', $dataTopUrl);

        $results = [];

        // データ上位馬3頭の情報を取得
        $crawler->filter('div.DataPickupHorseWrap dl')->each(function ($node) use (&$results) {
            // 馬番 (Umaban_Num) の取得
            $umaban = $node->filter('span.Umaban_Num')->text();

            // 馬名 (data_top_horse_link) の取得
            $horseName = $node->filter('a.data_top_horse_link')->text();

            // 馬名のリンク先URLを取得
            $horseLink = $node->filter('a.data_top_horse_link')->attr('href');

            // 特徴データの取得
            $featureDatas = [];
            $node->filter('dd.PickupDataBox ul li')->each(function ($li) use (&$featureDatas) {
                $featureDatas[] = $li->text();
            });

            $results[] = [
                'umaban' => $umaban,
                'horseName' => $horseName,
                'horseLink' => $horseLink,
                'featureDatas' => $featureDatas,
            ];
        });

        return $results;
    }

}

