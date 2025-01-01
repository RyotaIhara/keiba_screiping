<?php

require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

use Goutte\Client;

class Base {
    protected $client;
    protected $pdo;

    function __construct(Client $client){
        // 事前にサイトにログインをする
        $this->client = $client;
        $crawler = $this->client->request('GET', NETKEIBA_LOGIN_URL);
        $form = $crawler->filter('input[type="image"]')->form([
            'login_id' => NETKEIBA_LOGIN_ID,
            'pswd'     => NETKEIBA_LOGIN_PASS,
        ]);
        $crawler = $this->client->submit($form);

        // PDOインスタンスの作成
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->pdo = new PDO($dsn, DB_USER_NAME, DB_USER_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "データベース接続エラー: " . $e->getMessage();
        }
    }
}