<?php

class IsWinExpectedHorseArguments {
    public $raceId;
    public $expectedHorses;

    public function __construct($raceId, $expectedHorses) {
        $this->raceId = $raceId;
        $this->expectedHorses = $expectedHorses;
    }
}
