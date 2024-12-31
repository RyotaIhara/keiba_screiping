<?php

class GetHorseRaceExpectedByRaceArguments {
    public $targetYear;
    public $targetMonth;
    public $targetDay;
    public $targetRaceNo;
    public $narRaceFieldNo;

    public function __construct($targetYear, $targetMonth, $targetDay, $targetRaceNo, $narRaceFieldNo) {
        $this->targetYear = $targetYear;
        $this->targetMonth = $targetMonth;
        $this->targetDay = $targetDay;
        $this->targetRaceNo = $targetRaceNo;
        $this->narRaceFieldNo = $narRaceFieldNo;
    }
}
