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