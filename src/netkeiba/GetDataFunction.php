<?php

require_once(dirname(__FILE__)."/Base.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

use Goutte\Client;

class GetDataFunction extends Base {
    function __construct(Client $client){
        parent::__construct($client);
    }

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

    function getRaceInfo($params) {
        $raceDate = $params['year'] . '-' . $params['month'] . '-' . $params['day'];
        $raceNum = $params['race_num'];

        $sql = '
            SELECT * FROM race_info
            WHERE race_date = :race_date AND race_num = :race_num
        ';
        $whereParams = [
            ':race_date' => $raceDate,
            ':race_num' => $raceNum
        ];

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($whereParams);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $results[0];

    }
}

