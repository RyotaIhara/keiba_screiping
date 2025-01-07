<?php

require_once(dirname(__FILE__)."/Base.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");
require_once(dirname(__FILE__)."/GetDataFunction.php");

use Goutte\Client;

class RaceCalendarMstImport extends Base {
    var $startTargetYear;
    var $endTargetYear;
    var $startTargetMonth;
    var $endTargetMonth;

    function __construct(Client $client, $targetDateParams){
        parent::__construct($client);

        // 開始年
        $this->startTargetYear  = $targetDateParams['startTargetYear'];

        // 終了年
        $this->endTargetYear  = $targetDateParams['endTargetYear'];

        // 開始月
        $this->startTargetMonth  = $targetDateParams['startTargetMonth'];

        // 終了月
        $this->endTargetMonth  = $targetDateParams['endTargetMonth'];

    }

    function main() {
        $getDataFunction = new GetDataFunction($this->client);

        foreach ($this->generateDatesAndRaceNumbers(
            $this->startTargetYear,
            $this->endTargetYear,
            $this->startTargetMonth,
            $this->endTargetMonth
        ) as [$year, $month]) {
            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
            $resultUrl = NETKEIBA_DOMAIN_URL . 'top/calendar.html?year=' . $year .'&month=' . $month;

            $crawler = $this->client->request('GET', $resultUrl);
            $crawler->filter('td.RaceCellBox')->each(function ($node) use ($year, $month, $getDataFunction) {
                $day = $node->filter('span.Day')->count() ? $node->filter('span.Day')->text() : null;
                $day = str_pad($day, 2, '0', STR_PAD_LEFT);

                $jyoNames = $node->filter('span.JyoName')->each(function ($jyoNode) {
                    return $jyoNode->text();
                });

                foreach ($jyoNames as $jyoName) {
                    // 「帯広ば」だけ特殊なので整形
                    if ($jyoName === '帯広ば') {
                        $jyoName = '帯広';
                    }

                    $result = $getDataFunction->getRacecourseMst(
                        [
                            'racecourse_name' => $jyoName . '競馬場'
                        ]
                    );
                    $date = $year . '-' . $month . '-' . $day;
                    $jyoCd = $result[0]['jyo_cd'];

                    $params = array(
                        'race_date' => $date,
                        'jyo_cd' => $jyoCd,
                    );

                    if ($this->checkRaceSchedule($params, $getDataFunction)) {
                        echo "すでに" . 'race_date=' . $date . '、jyo_cd=' . $jyoCd . "のデータが存在しています。 \n";
                    } else {
                        // データがなければインサート
                        $this->insertRaceSchedule($params);
                        echo 'race_date=' . $date . '、jyo_cd=' . $jyoCd . "のデータ作成に成功しました。 \n";
                    }
                }
            });
        }
    }

    private function insertRaceSchedule($params) {
        $sql = "INSERT INTO race_schedule (race_date, jyo_cd) VALUES (:race_date, :jyo_cd)";

        // プリペアドステートメントを準備
        $stmt = $this->pdo->prepare($sql);
    
        // プレースホルダーに値をバインド
        $stmt->bindParam(':race_date', $params['race_date'], PDO::PARAM_STR);
        $stmt->bindParam(':jyo_cd', $params['jyo_cd'], PDO::PARAM_STR);
    
        // 実行
        $stmt->execute();
    }

    private function checkRaceSchedule($params, GetDataFunction $getDataFunction) {
        $results = $getDataFunction->getRaceSchedule($params);
        if (empty($results)) {
            return False;
        }
        return True;
    }

    protected function generateDatesAndRaceNumbers($startYear, $endYear, $startMonth, $endMonth) {
        for ($year = $startYear; $year <= $endYear; $year++) {
            for ($month = $startMonth; $month <= $endMonth; $month++) {
                yield [$year, $month];
            }
        }
    }

}

