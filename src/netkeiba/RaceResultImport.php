<?php

require_once(dirname(__FILE__)."/Base.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/GetDataFunction.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

use Goutte\Client;

class RaceResultImport extends Base {
    var $year;
    var $month;
    var $day;
    var $jyoCd;
    var $raceNum;

    function __construct(Client $client, $year, $month, $day, $jyoCd, $raceNum){
        parent::__construct($client);

        // 開始年
        $this->year  = $year;

        // 終了年
        $this->month  = $month;

        // 開始月
        $this->day  = $day;

        // 終了月
        $this->jyoCd  = $jyoCd;

        // レース番号
        $this->raceNum  = $raceNum;
    }

    function main() {
        $year = $this->year;
        $month = $this->month;
        $day = $this->day;
        $raceNum =  $this->raceNum;
        $jyoCd = $this->jyoCd;
        $raceId = $year . $jyoCd . $month . $day . str_pad($raceNum, 2, '0', STR_PAD_LEFT);

        $getDataFunction = new GetDataFunction($this->client);
        $raceInfo = $getDataFunction->getRaceInfo([
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'jyo_cd' => $jyoCd,
            'race_num' => $raceNum
        ]);

        $raceResultHorceList = $this->getRaceResultHorceList($raceId);
        $this->insertRaceResult($getDataFunction, $raceInfo['id'], $raceResultHorceList);
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

    private function insertRaceResult(GetDataFunction $getDataFunction, $raceInfoId, $raceResultHorceList) {
        foreach ($raceResultHorceList as $raceResultHorce) {
            $checkRaceResultParams = array(
                'race_info_id' => $raceInfoId,
                'result_num' => $raceResultHorce['result_num']
            );
            if ($this->checkRaceResult($checkRaceResultParams, $getDataFunction)) {
                echo "すでにrace_resultに" . 'race_info_id=' . $raceInfoId . "result_num=" . $raceResultHorce['result_num'] . "のデータが存在しています。 \n";
                continue;
            }
            if ($raceResultHorce['result_num'] === "取消") {
                echo 'race_info_id=' . $raceInfoId . "uma_ban=" . $raceResultHorce['uma_ban'] . "のデータは出走取消です \n";
                continue;
            }

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

            $stmt->execute();

            echo 'race_info_id=' . $raceInfoId . "result_num=" . $raceResultHorce['result_num'] . "のデータをrace_resultに作成成功しました。 \n";
        }
    }

    private function checkRaceResult($params, GetDataFunction $getDataFunction) {
        $results = $getDataFunction->getRaceResult($params);
        if (empty($results)) {
            return False;
        }
        return True;
    }
}

