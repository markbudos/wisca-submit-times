<?php

class Event {
    public $number;
    public $event;
    public $type;
    public $results = [];

    public static function setResult($resultId, $set) {
        $conn = WiscaDB::get();
        $stmt = $conn->prepare("UPDATE Results SET validated = ? WHERE resultId = ?");
        $stmt->execute([$set, $resultId]);
        $conn = null;
    }

    public static function loadResults($start, $end, $classification = null, $userId = null) {
        $params = [$start, $end];
        $query = "SELECT e.eventId, a.firstname, a.lastname, t.school AS team, s.school,
                         e.event, e.type, r.minutes, r.seconds, r.milliseconds,
                         r.date, a.gradyear, r.validated, r.resultId, u.name,
                         r.location, r.participantId, r.points
                  FROM Results r
                  JOIN Events e ON e.eventId = r.eventId
                  JOIN Users u ON u.userId = r.userId
                  LEFT JOIN Athletes a ON (r.participantId = a.athleteId AND r.type = 'a')
                  LEFT JOIN Schools s ON a.schoolId = s.schoolId
                  LEFT JOIN Schools t ON (r.participantId = t.schoolId AND r.type = 't')
                  WHERE r.date > ? AND r.date < ? AND validated >= 0 ";

        if ($classification) {
            $query .= "AND (s.size = ? OR t.size = ?) ";
            $params[] = $classification;
            $params[] = $classification;
        }

        if ($userId) {
            $query .= "AND u.userId = ? ORDER BY r.date DESC, e.eventId";
            $params[] = $userId;
        } else {
            $query .= "ORDER BY e.eventId, r.minutes, r.seconds, r.milliseconds, r.points DESC";
        }

        $conn = WiscaDB::get();
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $conn = null;
        return $results;
    }

    public static function loadLocations() {
		$conn = WiscaDB::get();
		$stmt = $conn->prepare("select distinct location from Results order by location");
        $stmt->execute();
        $locations = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $location = new Location();
			$location->init($row);
			$locations[] = $location;								
		}
		$conn = null;
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

    public static function loadEvents() {
        $conn = WiscaDB::get();
        $stmt = $conn->prepare("SELECT eventId, event, type FROM Events ORDER BY eventId");
        $stmt->execute();
        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $event = new Event();
            $event->init($row);
            $events[$event->number] = $event;
        }
        $conn = null;
        return $events;
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
		$stmt = $conn->prepare('insert into Results (participantId, type, minutes, seconds, 
			milliseconds, points, eventId, userId, date, location, created) values 
			(?,?,?,?,?,?,?,?,?,?,NOW())');
                $stmt->execute(array($participant, $type, 
			$minutes, $seconds, $millis, $points, $eo->number, 
			Session::getSession()->user->userId, $do, $location));

		$conn = null;
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

    public function init($row) {
        $this->number = $row['eventId'];
        $this->event  = $row['event'];
        $this->type   = $row['type'];
    }

}

class Location {

    public $location;
    
    public function init($row) {
        $this->location = $row['location'];
    }

    public function label() {
        return $this->location;
    }
}

?>