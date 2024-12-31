<?php

require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/../vendor/autoload.php");

use Goutte\Client;

class Base {
    protected $client;

    function __construct(Client $client){
        $this->client = $client;

        $crawler = $this->client->request('GET', NETKEIBA_LOGIN_URL);

        $form = $crawler->filter('input[type="image"]')->form([
            'login_id' => LOGIN_ID,
            'pswd'     => LOGIN_PASS,
        ]);
 
        $crawler = $this->client->submit($form);
    }
}