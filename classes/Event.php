<?php
class Event {

	public $number;
	public $event;
	public $type;
	public $results = array();

	public static function setResult($resultId, $set) {
		$conn = WiscaDB::get();
		$result =& $conn->query("update Results set validated = ? where resultId = ?", array($set, $resultId));
		$conn->disconnect();
	}

	public static function loadResults($start, $end, $classification = null, $userId = null) {
		$params = array($start, $end);
		$query = 'SELECT e.eventId, a.firstname, a.lastname, t.school \'team\', s.school, 
				e.event, e.type, r.minutes, r.seconds, r.milliseconds, 
				r.date, a.gradyear, r.validated, r.resultId, u.name, r.location, r.participantId, r.points
			from Results as r
			join Events as e on (e.eventId = r.eventId)
			join Users as u on (u.userId = r.userId)
			left join Athletes as a on (r.participantId = a.athleteId and r.type = \'a\')
			left join Schools as s on (a.schoolId = s.schoolId)
			left join Schools as t on (r.participantId = t.schoolId and r.type = \'t\')
			where r.date > ? and r.date < ? and validated >= 0 ';
		if ($classification) { 
			$query .= 'and (s.size = ? or t.size = ?) '; 
			$params[] = $classification;
			$params[] = $classification; //need two of these
		}
		if ($userId) { 
			$query .= 'and (u.userId = ?) '; 
			$params[] = $userId;
			$query .= ' order by r.date desc, e.eventId';
		} else {
			$query .= ' order by e.eventId, r.minutes, r.seconds, r.milliseconds, r.points desc';
		}
		$conn = WiscaDB::get();
		$result =& $conn->query($query, $params);
		$results = array();
		while ($result->fetchInto($row, DB_FETCHMODE_ASSOC)) {
			$results[] = $row;
		}
		$conn->disconnect();
		return $results;
	}

	public static function loadEvents() {
		$conn = WiscaDB::get();
		$result =& $conn->query("select * from Events order by eventId");
		$events = array();
		while ($row =& $result->fetchRow()) {
			$event = new Event();
			$event->init($row);
			$events[$event->number] = $event;
		}
		$conn->disconnect();
		return $events;
	}

	public static function loadLocations() {
		$conn = WiscaDB::get();
		$result =& $conn->query("select distinct location from Results order by location");
		$locations = array();
		while ($row =& $result->fetchRow()) {
			$location = new Location();
			$location->init($row);
			$locations[] = $location;								
		}
		$conn->disconnect();
		return $locations;
	}

	public function label() {
		$ret = $this->event;
		if ($this->type == 'd') {
			$ret .= ' Diving';
		} else if ($this->type == 'r') {
			$ret .= ' Relay';
		}
		return $ret;
	}
	public function init($row) {
		$this->number = $row[0];
		$this->event = $row[1];
		$this->type = $row[2];
	}

	private static function getEvent($event) {
		$events = self::loadEvents();
		foreach ($events as $item) {
			if ($item->label() == $event) {
				return $item;
			} 
		}
		return null;
	}
	public static function saveResult($school, $classification, $event, 
				$athlete, $result, $location, $date, $errors) {
		$eo = self::getEvent($event);
		if (!$eo) {
			$errors[] = '"'.$event.'" is a non-existent event.';
			return;
		}
		$so = School::getSchool($school, $classification, true);
		if (!$so) {
			$errors[] = '"'.$school.'" already exists with a different classification.';
			return;
		}
		$participant = $so->schoolId;
		$type = 't';
		$points = $eo->type == 'd' ? $result[0] : null;
		$minutes = $eo->type != 'd' ? $result[0] : null;
		$seconds = $eo->type != 'd' ? $result[1] : null;
		$millis = $eo->type != 'd' ? $result[2] : null;
		$ao = null;
		if ($eo->type != 'r') {
			$type = 'a';
			$matches = array();
			if (!preg_match("/^(.+) \(*(\d+)\)*/", $athlete, $matches)) {
				$errors[] = '"'.$athlete.'" requires a grade. (e.g. "Jane Smith (10))")';
				return;
			} else if ($matches[2] > 12 || $matches[2] < 9) {
				$errors[] = '"'.$matches[2].'" must be a number between 9 and 12.';
				return;
			}
			$ao = Athlete::getAthlete($so, $matches[1], $matches[2], true);
			if (!$ao) {
				$errors[] = 'Unable to create athlete: "'.$athlete.'."';
				return;
			}
			$participant = $ao->athleteId;
		}
		$conn = WiscaDB::get();
		$do = date('Y/m/d', strtotime($date));
		$result =& $conn->query('insert into Results (participantId, type, minutes, seconds, 
			milliseconds, points, eventId, userId, date, location, created) values 
			(?,?,?,?,?,?,?,?,?,?,NOW())', array($participant, $type, 
			$minutes, $seconds, $millis, $points, $eo->number, 
			Session::getSession()->user->userId, $do, $location));

		$conn->disconnect();
		$team = $school;
		if ($eo->type != 'r') {
				$team = '';
		}
		$params = array('eventId'=>$eo->number, 
					   'firstname'=>$ao->firstName, 
					   'lastname'=>$ao->lastName, 
					   'team'=>$team, 'school'=>$school, 
					   'event'=>$eo->event,
					   'type'=>$eo->type, 
					   'minutes'=>$minutes, 
					   'seconds'=>$seconds, 
					   'milliseconds'=>$millis, $do, 
					   'gradyear'=>$ao->gradyear, 
					    0, 0, 
					   'name'=>Session::getSession()->user->name, 
					   'location'=>$location,
					   'date'=>$date
				);
		return $params;
	}
}

class Location {

	public $location;
	
	public function init($row) {
		$this->location = $row[0];
	}

	public function label() {
		return $this->location;
	}
}

?>
