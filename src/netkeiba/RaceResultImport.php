<?php

require_once(dirname(__FILE__)."/Base.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/GetDataFunction.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

use Goutte\Client;

class RaceResultImport extends Base {
    function __construct(Client $client){
        parent::__construct($client);
    }

    function main() {

        $year = '2024';
        $month = '12';
        $day = '30';
        $raceNum = '01';
        $jyoCd = NAR_RACE_FIELD_NO['oi'];
        $raceId = $year . $jyoCd . $month . $day . $raceNum;

        $getDataFunction = new GetDataFunction($this->client);
        $raceInfo = $getDataFunction->getRaceInfo([
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'race_num' => $raceNum,
        ]);

        $raceResultHorceList = $this->getRaceResultHorceList($raceId);
        $this->insertRaceResult($raceInfo['id'], $raceResultHorceList);
    }

    function getRaceResultHorceList($raceId) {
        $resultUrl = NETKEIBA_DOMAIN_URL . 'race/result.html?race_id=' . $raceId;
        $crawler = $this->client->request('GET', $resultUrl);

        $results = [];

        $crawler->filter('#All_Result_Table tbody tr')->each(function ($node) use (&$results) {
            $resultNum = $node->filter('td.Result_Num')->text();
            $umaban = $node->filter('td.Num.Waku div')->text();
            $horseName = $node->filter('span.Horse_Name a')->text();

            $results[] = [
                'result_num' => $resultNum,
                'uma_ban' => $umaban,
                'horse_name' => $horseName
            ];
        });

        return $results;
    }

    private function insertRaceResult($raceInfoId, $raceResultHorceList) {
        foreach ($raceResultHorceList as $raceResultHorce) {
            $sql = "INSERT INTO `race_result`(
                    `race_info_id`,
                    `result_num`,
                    `uma_ban`,
                    `horse_name`
                )
                VALUES(
                    :race_info_id,
                    :result_num,
                    :uma_ban,
                    :horse_name
                )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':race_info_id', $raceInfoId, PDO::PARAM_INT);
            $stmt->bindParam(':result_num', $raceResultHorce['result_num'], PDO::PARAM_STR);
            $stmt->bindParam(':uma_ban', $raceResultHorce['uma_ban'], PDO::PARAM_STR);
            $stmt->bindParam(':horse_name', $raceResultHorce['horse_name'], PDO::PARAM_STR);

            // 実行
            $stmt->execute();
        }
    }
}

