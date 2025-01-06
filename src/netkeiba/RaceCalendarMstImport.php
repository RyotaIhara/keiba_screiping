<?php

require_once(dirname(__FILE__)."/Base.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

require_once(dirname(__FILE__)."/GetDataFunction.php");

use Goutte\Client;

class RaceCalendarMstImport extends Base {
    const DEFAUTL_START_TARGET_YEAR  = 2024;
    const DEFAUTL_END_TARGET_YEAR    = 2024;
    const DEFAUTL_STRAT_TARGET_MONTH = 1;
    const DEFAUTL_END_TARGET_MONTH   = 12;

    var $startTargetYear;
    var $endTargetYear;
    var $startTargetMonth;
    var $endTargetMonth;

    function __construct(Client $client){
        parent::__construct($client);

        $this->startTargetYear  = self::DEFAUTL_START_TARGET_YEAR;
        $this->endTargetYear    = self::DEFAUTL_END_TARGET_YEAR;
        $this->startTargetMonth = self::DEFAUTL_STRAT_TARGET_MONTH;
        $this->endTargetMonth   = self::DEFAUTL_END_TARGET_MONTH;
    }

    function main() {
        $getDataFunction = new GetDataFunction($this->client);

        foreach ($this->generateDatesAndRaceNumbers(
            $this->startTargetYear,
            $this->endTargetYear,
            $this->startTargetMonth,
            $this->endTargetMonth
        ) as [$year, $month]) {
            $resultUrl = NETKEIBA_DOMAIN_URL . 'top/calendar.html?year=' . $year .'&month=' . $month;

            $crawler = $this->client->request('GET', $resultUrl);
            $crawler->filter('td.RaceCellBox.HaveData')->each(function ($node) use ($year, $month, $getDataFunction) {
                $day = $node->filter('span.Day')->count() ? $node->filter('span.Day')->text() : null;

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
                        'raceDate' => $date,
                        'jyoCd' => $jyoCd,
                    );

                    $this->insertRaceSchedule($params);
                }
        });
        }
    }

    private function insertRaceSchedule($params) {
        $sql = "INSERT INTO race_schedule (race_date, jyo_cd) VALUES (:race_date, :jyo_cd)";

        // プリペアドステートメントを準備
        $stmt = $this->pdo->prepare($sql);
    
        // プレースホルダーに値をバインド
        $stmt->bindParam(':race_date', $params['raceDate'], PDO::PARAM_STR);
        $stmt->bindParam(':jyo_cd', $params['jyoCd'], PDO::PARAM_STR);
    
        // 実行
        $stmt->execute();
    }

    protected function generateDatesAndRaceNumbers($startYear, $endYear, $startMonth, $endMonth) {
        for ($year = $startYear; $year <= $endYear; $year++) {
            for ($month = $startMonth; $month <= $endMonth; $month++) {
                yield [$year, $month];
            }
        }
    }

}

