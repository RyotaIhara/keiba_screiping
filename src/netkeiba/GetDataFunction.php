<?php

require_once(dirname(__FILE__)."/Base.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

use Goutte\Client;

class GetDataFunction extends Base {
    function __construct(Client $client){
        parent::__construct($client);
    }

    function getRacecourseMst($whereParams) {
        $sql = 'SELECT * FROM racecourse_mst';
        $params = [];

        if (count($whereParams) !== 0) {
            $sql .= ' WHERE';
        }
        if (isset($whereParams['jyo_cd'])) {
            $sql .= ' jyo_cd = :jyo_cd';
            $params[':jyo_cd'] = $whereParams['jyo_cd'];
        }
        if (isset($whereParams['racecourse_name'])) {
            $sql .= ' racecourse_name = :racecourse_name';
            $params[':racecourse_name'] = $whereParams['racecourse_name'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $results;

    }
}

