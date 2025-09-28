<?php
class Athlete {
    public $firstName;
    public $lastName;
    public $year;
    public $gradyear;
    public $athleteId;

    public function label($eventdate = null) {
        $date = $eventdate ? date_create($eventdate) : date_create('today');
        $month = $date->format('n');
        $year  = $date->format('Y');

        if ($month > 8) { $year++; }
        $class = 12 - ($this->gradyear - $year);

        return $this->firstName.' '.$this->lastName.' ('.$class.')';
    }

    public function init($row) {
        $this->firstName = $row['firstname'];
        $this->lastName  = $row['lastname'];
        $this->gradyear  = $row['gradyear'];
        $this->athleteId = $row['participantId'];
    }

    private function padZero($str, $length) {
        while (strlen($str) < $length) {
            $str = '0'.$str;
        }
        return $str;
    }

    public function formatResult($result, $includeEvent) {
        $ret = [];
        $ret[] = $result['date'];
        $ret[] = ($result['team'] ? $result['team'] : $this->label($ret[0]).', '.$result['school']);

        if ($includeEvent) {
            $event = new Event();
            $event->event = $result['event'];
            $event->type  = $result['type'];
            $ret[] = $event->label();
        }

        $score = ($result['minutes'] ? $result['minutes'].':' : '');
        if ($result['type'] == 'd') {
            $score .= number_format($result['points'], 2);
        } else {
            $score .= $this->padZero($result['seconds'], 2).'.'.$this->padZero($result['milliseconds'], 2);
        }
        $ret[] = $score;
        $ret[] = '@'.$result['location'];

        if (Session::getSession()->user->admin || Session::getSession()->user->name == $result['name']) {
            $ret[] = 'submitted by: '.$result['name'];
        }
        return $ret;
    }

    public static function getAthlete($school, $athlete, $grade, $create = false) {
        $month = date('n');
        $year  = date('Y');
        if ($month > 8) { $year++; }
        $gradyear  = $year + 12 - $grade;

        $names = explode(' ', $athlete, 2);
        $firstName = $names[0];
        $lastName  = $names[1] ?? '';

        $athletes = self::loadAthletes($school->school);
        
        foreach ($athletes as $item) {
            if ($item->firstName === $firstName &&
                $item->lastName  === $lastName &&
                intval($gradyear) === intval($item->gradyear)) {
                return $item;
            }
        }

        if ($create) {
            $conn = WiscaDB::get();
            $stmt = $conn->prepare(
                "INSERT INTO Athletes (firstname, lastname, schoolId, gradyear) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$firstName, $lastName, $school->schoolId, $gradyear]);
            $conn = null;
        }

        $ret = self::getAthlete($school, $athlete, $grade);
        return $ret;
    }

    public static function loadAthletes($school) {
        $conn = WiscaDB::get();

        $month = date('n');
        $year  = date('Y');
        if ($month >= 6) { $year++; }

        $sql = "SELECT firstname, lastname, gradyear, athleteId AS participantId
                  FROM Athletes AS a
                  JOIN Schools AS s ON (s.schoolId = a.schoolId AND s.school = ?)
                 WHERE gradyear >= ?
              ORDER BY lastname";
        $stmt = $conn->prepare($sql);
        
        $stmt->execute([$school, $year]);

        $athletes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $athlete = new Athlete();
            $athlete->init($row);
            $athletes[] = $athlete;
        }
        $conn = null;
        return $athletes;
    }
}
?>
