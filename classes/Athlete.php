<?php
class Athlete {

	public $firstName;
	public $lastName;
	public $year;
	public $gradyear;
	public $athleteId;

	public function label($eventdate = null) {

		$date = date_create('today');
		if ($eventdate) {
			$date = date_create($eventdate);
		}
		$month = $date->format('n');
		$year = $date->format('Y');
		if ($month > 8) { $year++; }
		$class = 12 - ($this->gradyear - $year);
		return $this->firstName.' '.$this->lastName.' ('.$class.')';
	}

	public function init($row) {
		$this->firstName = $row['firstname'];
		$this->lastName = $row['lastname'];
		$this->gradyear = $row['gradyear'];
		$this->athleteId = $row['participantId'];
	}

	private function padZero($str, $length) {
		$l = strlen($str);
		while ($length - $l > 0) {
			$length--;
			$str = '0'.$str;
		}
		return $str;
	}

	public function formatResult($result, $includeEvent) {
		$ret = array();
		$ret[] = $result['date'];
		$ret[] = ($result['team'] ? $result['team'] : $this->label($ret[0]).', '.$result['school']);
		if ($includeEvent) {
			$event = new Event(); $event->event = $result['event']; $event->type = $result['type'];
			$ret[] = $event->label();
		}
		$score = ($result['minutes'] ? $result['minutes'].':' : '');
		if ($result['type'] == 'd') {
			$score .= number_format($result['points'], 2);
		} else {
			$score .= self::padZero($result['seconds'], 2).'.'.self::padZero($result['milliseconds'], 2);
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
		$year = date('Y');
		if ($month > 8) { $year++; }
		$gradyear = $year + 12 - $grade;
		$names = explode(' ', $athlete, 2);
		$firstName = $names[0];
		$lastName = $names[1];
		$athletes = self::loadAthletes($school->school);
		foreach ($athletes as $item) {
			if ($item->firstName == $firstName && 
				$item->lastName == $lastName && 
				$gradyear == $item->gradyear) {
				return $item;
			}
		}
		if ($create) {
			$conn = WiscaDB::get();
			$result =& $conn->query('insert into Athletes (firstname, lastname, 
					schoolId, gradyear) values (?, ?, ?, ?)', 
					array($firstName, $lastName, 
					$school->schoolId, $gradyear));
			$conn->disconnect();
		}
		return self::getAthlete($school, $athlete, $grade);
	}

	public static function loadAthletes($school) {
		$conn = WiscaDB::get();

		$month = date('n');
		$year = date('Y');
		if ($month >= 6) { $year++; }

		$result =& $conn->query("select firstname, lastname, gradyear, athleteId 'participantId' 
			from Athletes as a 
			join Schools as s on (s.schoolId = a.schoolId and s.school = ?)
			where gradyear >= ?
			order by lastName",
			array($school, $year));
		$athletes = array();
		while ($result->fetchInto($row, DB_FETCHMODE_ASSOC)) {
			$athlete = new Athlete();
			$athlete->init($row);
			$athletes[] = $athlete;						
		}
		$conn->disconnect();
		return $athletes;
	}

}


?>