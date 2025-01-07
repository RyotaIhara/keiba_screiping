<?php

require_once(dirname(__FILE__)."/Base.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

use Goutte\Client;

class GetDataFunction extends Base {
    function __construct(Client $client){
        parent::__construct($client);
    }

    /* race_course_mstのデータを取得する（条件指定できる） */
    function getRacecourseMst($params) {
        $sql = 'SELECT * FROM racecourse_mst';
        $whereParams = [];

        if (count($params) !== 0) {
            $sql .= ' WHERE';
        }
        if (isset($params['jyo_cd'])) {
            $sql .= ' jyo_cd = :jyo_cd';
            $whereParams[':jyo_cd'] = $params['jyo_cd'];
        }
        if (isset($params['racecourse_name'])) {
            $sql .= ' racecourse_name = :racecourse_name';
            $whereParams[':racecourse_name'] = $params['racecourse_name'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($whereParams);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $results;

    }

    /* 日付とレース番号からrace_infoの情報を取得する */
    function getRaceInfo($params) {
        $raceDate = $params['year'] . '-' . $params['month'] . '-' . $params['day'];
        $jyoCd = $params['jyo_cd'];
        $raceNum = $params['race_num'];

        $sql = '
            SELECT * FROM race_info
            WHERE 
            race_date = :race_date 
            AND jyo_cd = :jyo_cd
            AND race_num = :race_num
        ';
        $whereParams = [
            ':race_date' => $raceDate,
            ':jyo_cd' => $jyoCd,
            ':race_num' => $raceNum
        ];

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($whereParams);

        $results = $stmt->fetch(PDO::FETCH_ASSOC);

        return $results;

    }

    /* race_info_idと馬番号からrace_cardの情報を取得する */
    function getRaceCard($params) {
        $raceInfoId = $params['race_info_id'];
        $umaBan = $params['uma_ban'];

        $sql = '
            SELECT * FROM race_card
            WHERE 
            race_info_id = :race_info_id 
            AND uma_ban = :uma_ban
        ';
        $whereParams = [
            ':race_info_id' => $raceInfoId,
            ':uma_ban' => $umaBan
        ];

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($whereParams);

        $results = $stmt->fetch(PDO::FETCH_ASSOC);

        return $results;

    }

    /* race_info_idと馬番号からrace_resultの情報を取得する */
    function getRaceResult($params) {
        $raceInfoId = $params['race_info_id'];
        $resultNum = $params['result_num'];

        $sql = '
            SELECT * FROM race_result
            WHERE 
            race_info_id = :race_info_id 
            AND result_num = :result_num
        ';
        $whereParams = [
            ':race_info_id' => $raceInfoId,
            ':result_num' => $resultNum
        ];

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($whereParams);

        $results = $stmt->fetch(PDO::FETCH_ASSOC);

        return $results;

    }

    /* race_scheduleの情報を取得する（条件指定できる） */
    function getRaceSchedule($params) {
        $sql = 'SELECT * FROM race_schedule';
        $whereClauses = [];
        $whereParams = [];
    
        // パラメータに応じて条件を追加
        if (isset($params['jyo_cd'])) {
            $whereClauses[] = 'jyo_cd = :jyo_cd';
            $whereParams[':jyo_cd'] = $params['jyo_cd'];
        }
        if (isset($params['race_date'])) {
            $whereClauses[] = 'race_date = :race_date';
            $whereParams[':race_date'] = $params['race_date'];
        }
    
        // WHERE句の組み立て
        if (!empty($whereClauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }
    
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($whereParams);
    
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return $results;
    }
}

