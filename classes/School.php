<?php
class Coach {

	public function __construct($userId, $name) {
		$this->userId = $userId;
		$this->name = $name;
	}	

	public $userId;
	public $name;
}

class School {

	public $school;
	public $schoolId;
	public $classification;
	public $girlscoaches = array();
	public $boyscoaches = array();

	public function label() {
		return $this->school;
	}

	public function init($row) {
		$this->schoolId = $row[0];
		$this->school = $row[1];
		$this->classification = $row[2];
		$this->girlscoaches['head'] = new Coach($row[3], $row[4]);;
		$this->girlscoaches['assistant'] = new Coach($row[5], $row[6]);;
		$this->girlscoaches['dive'] = new Coach($row[7], $row[8]);;
		$this->boyscoaches['head'] = new Coach($row[9], $row[10]);;
		$this->boyscoaches['assistant'] = new Coach($row[11], $row[12]);;
		$this->boyscoaches['dive'] = new Coach($row[13], $row[14]);;
	}

	public static function getSchool($school, $classification, $create = false) {
		$schools = self::loadSchools($classification);
		foreach ($schools as $item) {
			if ($item->school == $school) {
				return $item;
			}
		}
		if ($create) {
			$conn = WiscaDB::get();
			$result =& $conn->query("insert into Schools (school, size) values (?, ?)", 
				array($school, $classification));
			$conn->disconnect();
		}
		return self::getSchool($school, $classification);
	}

	public function reassign($to) {
		if ($this->schoolId != $to) {
			$conn = WiscaDB::get();
			$result =& $conn->query("update Athletes set schoolId = ? where schoolId = ?", 
				array($to, $this->schoolId));
			$result =& $conn->query("update Results set participantId=? where type='t' and participantId=?", 
				array($to, $this->schoolId));
			$result =& $conn->query("delete from Schools where schoolId = ?", array($this->schoolId));
		}
	}

	public function save() {
		$conn = WiscaDB::get();
		$params = array($this->size, $this->school, 
			$this->girlscoaches['head']->userId, $this->girlscoaches['assistant']->userId, 		
			$this->girlscoaches['dive']->userId, $this->boyscoaches['head']->userId, 
			$this->boyscoaches['assistant']->userId, $this->boyscoaches['dive']->userId,
			$this->schoolId);
		$result =& $conn->query("update Schools set size = ?, school = ?, ".
				"coachId1 = ?, coachId2 = ?, coachId3 = ?, ". 
				"coachId4 = ?, coachId5 = ?, coachId6 = ? ". 
				"where schoolId = ?", $params);
	}

	public static function lookupSchool($schoolId) {
		$conn = WiscaDB::get();
		$result =& $conn->query("select s.schoolId, s.school, s.size, ".
				"coach1.userId, coach1.name, coach2.userId, ".
				"coach2.name, coach3.userId, coach3.name, coach4.userId, coach4.name, ".
				"coach5.userId, coach5.name, coach6.userId, coach6.name from Schools as s ".
				"left join Users as coach1 on (coach1.userId = s.coachId1) ". 
				"left join Users as coach2 on (coach2.userId = s.coachId2) ". 
				"left join Users as coach3 on (coach3.userId = s.coachId3) ". 
				"left join Users as coach4 on (coach4.userId = s.coachId4) ". 
				"left join Users as coach5 on (coach5.userId = s.coachId5) ". 
				"left join Users as coach6 on (coach6.userId = s.coachId6) ". 
				"where schoolId = ?", array($schoolId));
		if ($row =& $result->fetchRow()) {
			$school = new School();
			$school->init($row);
		}
		$conn->disconnect();
		return $school;
	}

	public static function loadSchools($classification) {
		$conn = WiscaDB::get();
		$sql = "select s.schoolId, s.school, s.size, ".
				"coach1.userId, coach1.name, coach2.userId, ".
				"coach2.name, coach3.userId, coach3.name, coach4.userId, coach4.name, ".
				"coach5.userId, coach5.name, coach6.userId, coach6.name from Schools as s ".
				"left join Users as coach1 on (coach1.userId = s.coachId1) ". 
				"left join Users as coach2 on (coach2.userId = s.coachId2) ". 
				"left join Users as coach3 on (coach3.userId = s.coachId3) ". 
				"left join Users as coach4 on (coach4.userId = s.coachId4) ". 
				"left join Users as coach5 on (coach5.userId = s.coachId5) ". 
				"left join Users as coach6 on (coach6.userId = s.coachId6) ". 
				($classification ? 'where size = ?' : '')." order by school";
		$params = array();
		if ($classification) {
				$params[] = $classification;
		}
		$result =& $conn->query($sql, $params);
		$schools = array();
		while ($row =& $result->fetchRow()) {
			$school = new School();
			$school->init($row);
			$schools[] = $school;								
		}
		$conn->disconnect();
		return $schools;
	}

}


?>