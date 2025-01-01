<?php

require_once(dirname(__FILE__)."/Base.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

use Goutte\Client;

class RacecourseMstImport extends Base {
    function __construct(Client $client){
        parent::__construct($client);
    }

    function main() {

        $racecourseList = $this->getRacecourse();
        $this->insertRacecourseMst($racecourseList);

        echo "INSERTが完了しました\n";
    }

    function getRacecourse() {
        $resultUrl = NETKEIBA_DOMAIN_URL . 'racecourse/racecourse_list.html?rf=sidemenu';
        $crawler = $this->client->request('GET', $resultUrl);

        $results = [];

        $crawler->filter('ul.raceCourse_list li')->each(function ( $node) use (&$results) {
            $raceCourseName = $node->filter('h3')->text();
            $link = $node->filter('a')->attr('href');
            preg_match('/jyo_cd=(\d+)/', $link, $matches);
            $jyoCd = $matches[1] ?? null;
            $results[] = [
                'raceCourseName' => $raceCourseName,
                'jyoCd' => $jyoCd,
            ];
        });

        return $results;
    }

    function insertRacecourseMst($racecourseList) {
        foreach ($racecourseList as $racecourse) {
            $sql = "INSERT INTO racecourse_mst (jyo_cd, racecourse_name) VALUES (:jyo_cd, :racecourse_name)";

            // プリペアドステートメントを準備
            $stmt = $this->pdo->prepare($sql);
        
            // プレースホルダーに値をバインド
            $stmt->bindParam(':jyo_cd', $racecourse['jyoCd'], PDO::PARAM_STR);
            $stmt->bindParam(':racecourse_name', $racecourse['raceCourseName'], PDO::PARAM_STR);
        
            // 実行
            $stmt->execute();
        }
    }

}

