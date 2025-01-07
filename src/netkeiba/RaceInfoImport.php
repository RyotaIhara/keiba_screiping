<?php

require_once(dirname(__FILE__)."/Base.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

use Goutte\Client;

class RaceInfoImport extends Base {
    var $year;
    var $month;
    var $day;
    var $jyoCd;
    var $raceNum;

    function __construct(Client $client, $year, $month, $day, $jyoCd, $raceNum){
        parent::__construct($client);

        // 年
        $this->year  = $year;

        // 月
        $this->month  = $month;

        // 日にち
        $this->day  = $day;

        // 競馬場のコード
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
        $raceId = $year . $jyoCd . $month . $day . $raceNum;

        $tmpParams = $this->getRacecoeInfo( $raceId );
        $raceInfo = $tmpParams['raceInfo'];
        $horceInfoList = $tmpParams['horceInfoList'];

        $raceInfoParams = array(
            'race_date' => $year . $month . $day,
            'jyo_cd' => $jyoCd,
            'race_num' => $raceNum,
            'race_name' => $raceInfo['race_name'],
            'entry_horce_count' => count($horceInfoList),
            'course_type' => $raceInfo['course_type'],
            'distance' => $raceInfo['distance'],
            'rotation' => $raceInfo['rotation'],
            'weather' => $raceInfo['weather'],
            'baba_state' => $raceInfo['baba_state']
        );

        //$raceInfoId = $this->insertRaceInfo($raceInfoParams);

        //$this->insertRaceCard($raceInfoId, $horceInfoList);

    }

    function getRacecoeInfo($raceId) {
        $shutubaUrl = NETKEIBA_DOMAIN_URL . 'race/shutuba.html?race_id=' . $raceId;
        $crawler = $this->client->request('GET', $shutubaUrl);

        $raceInfo = array();

        $raceName = $crawler->filter('div.RaceName')->text('');
        $raceInfo['race_name'] =trim(preg_replace('/\s+/', ' ', $raceName));

        $crawler->filter('div.RaceData01')->each(function ($node) use (&$raceInfo) {
            $timeText = $node->text();

            $distance = $node->filter('span')->eq(0)->text();
            $raceInfo['distance'] = trim($distance);

            if (preg_match('/^(ダ|芝)(\d+m)$/', $distance, $matches)) {
                $raceInfo['course_type'] = $matches[1];
                $raceInfo['distance'] = $matches[2];
            } else {
                echo "データが正しく解析できませんでした。(1)\n";
            }

            // コース
            preg_match('/\((右|左)\)/', $timeText, $courseMatch);
            $raceInfo['rotation'] = str_replace(['(', ')'], '', $courseMatch[0])?? '';;

            // 天候
            preg_match('/天候:([^\s<]+)/', $timeText, $weatherMatch);
            $raceInfo['weather'] = $weatherMatch[1] ?? '';
        
            // 馬場状態
            $trackCondition = $node->filter('span.Item04')->text();
            $raceInfo['baba_state'] = str_replace('/ 馬場:', '', trim($trackCondition));
        });


        // 出走馬情報を取得
        //$crawler->filter('td.HorseInfo')->each(function ($node) use (&$horceInfoList) {
        $crawler->filter('tr.HorseList')->each(function ($parentRow) use (&$horceInfoList) {
            //$parentRow = $node->ancestors()->filter('tr');
            $wakuBan = $parentRow->filter('td[class^="Waku"]')->text('');
            $umaBan = $parentRow->filter('td[class^="Umaban"]')->text('');
            $horseName = $parentRow->filter('td.HorseInfo span.HorseName a')->text('');
            $age = $parentRow->filter('td')->eq(1)->text('');
            $jockey = $parentRow->filter('td.Jockey a')->text('');
            $stable = $parentRow->filter('td.Trainer')->text('');

            $weight = NULL;
            $weightGainLoss = NULL;
            $favourite = NULL;
            $winOdds = NULL;

            $isCancel = False;
            if ($parentRow->filter('td.Cancel_Txt')->count() > 0) {
                $isCancel = True;
            } else {
                $weightInfoOrigin = $parentRow->filter('td.Weight')->text('');
                if (preg_match('/^(\d+)\(([^)]+)\)$/', $weightInfoOrigin, $matches)) {
                    $weight = $matches[1];
                    $weightGainLoss = $matches[2];
                    $favourite = $parentRow->filter('td.Popular.Txt_C')->text('');
                    $winOdds = $parentRow->filter('td.Popular.Txt_R')->text('');
                } else {
                    echo "データが正しく解析できませんでした。(2)\n";
                }
            }

            $horceInfoList[] = [
                'waku_ban' => $wakuBan,
                'uma_ban' => $umaBan,
                'horse_name' => $horseName,
                'age' => $age,
                'weight' => $weight,
                'jockey' => $jockey,
                'favourite' => $favourite,
                'win_odds' => $winOdds,
                'stable' => $stable,
                'weight_gain_loss' => $weightGainLoss,
                'is_cancel' => $isCancel
            ];
        });

        $results = array(
            'raceInfo' => $raceInfo,
            'horceInfoList' => $horceInfoList,
        );

        return $results;

    }

    private function insertRaceInfo($params) {
        $sql = "INSERT INTO `race_info`(
                `race_date`,
                `jyo_cd`,
                `race_num`,
                `race_name`,
                `entry_horce_count`,
                `course_type`,
                `distance`,
                `rotation`,
                `weather`,
                `baba_state`
            )
            VALUES(
                :race_date,
                :jyo_cd,
                :race_num,
                :race_name,
                :entry_horce_count,
                :course_type,
                :distance,
                :rotation,
                :weather,
                :baba_state
            )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':race_date', $params['race_date'], PDO::PARAM_STR);
        $stmt->bindParam(':jyo_cd', $params['jyo_cd'], PDO::PARAM_STR);
        $stmt->bindParam(':race_num', $params['race_num'], PDO::PARAM_STR);
        $stmt->bindParam(':race_name', $params['race_name'], PDO::PARAM_STR);
        $stmt->bindParam(':entry_horce_count', $params['entry_horce_count'], PDO::PARAM_STR);
        $stmt->bindParam(':course_type', $params['course_type'], PDO::PARAM_STR);
        $stmt->bindParam(':distance', $params['distance'], PDO::PARAM_STR);
        $stmt->bindParam(':rotation', $params['rotation'], PDO::PARAM_STR);
        $stmt->bindParam(':weather', $params['weather'], PDO::PARAM_STR);
        $stmt->bindParam(':baba_state', $params['baba_state'], PDO::PARAM_STR);
    
        // 実行
        $stmt->execute();

        $raceInfoId = $this->pdo->lastInsertId();

        return $raceInfoId;
    }

    private function insertRaceCard($raceInfoId, $horceInfoList) {
        foreach ($horceInfoList as $horceInfo) {
            $sql = "INSERT INTO `race_card`(
                    `race_info_id`,
                    `waku_ban`,
                    `uma_ban`,
                    `horse_name`,
                    `age`,
                    `weight`,
                    `jockey_name`,
                    `favourite`,
                    `win_odds`,
                    `stable`,
                    `weight_gain_loss`,
                    `is_cancel`
                )
                VALUES(
                    :race_info_id,
                    :waku_ban,
                    :uma_ban,
                    :horse_name,
                    :age,
                    :weight,
                    :jockey_name,
                    :favourite,
                    :win_odds,
                    :stable,
                    :weight_gain_loss,
                    :is_cancel
                )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':race_info_id', $raceInfoId, PDO::PARAM_INT);
            $stmt->bindParam(':waku_ban', $horceInfo['waku_ban'], PDO::PARAM_STR);
            $stmt->bindParam(':uma_ban', $horceInfo['uma_ban'], PDO::PARAM_STR);
            $stmt->bindParam(':horse_name', $horceInfo['horse_name'], PDO::PARAM_STR);
            $stmt->bindParam(':age', $horceInfo['age'], PDO::PARAM_STR);
            $stmt->bindParam(':weight', $horceInfo['weight'], PDO::PARAM_STR);
            $stmt->bindParam(':jockey_name', $horceInfo['jockey_name'], PDO::PARAM_STR);
            $stmt->bindParam(':favourite', $horceInfo['favourite'], PDO::PARAM_STR);
            $stmt->bindParam(':win_odds', $horceInfo['win_odds'], PDO::PARAM_STR);
            $stmt->bindParam(':stable', $horceInfo['stable'], PDO::PARAM_STR);
            $stmt->bindParam(':weight_gain_loss', $horceInfo['weight_gain_loss'], PDO::PARAM_STR);
            $stmt->bindParam(':is_cancel', $horceInfo['is_cancel'], PDO::PARAM_STR);

            // 実行
            $stmt->execute();
        }

    }

}

 