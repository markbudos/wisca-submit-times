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
    public $girlscoaches = [];
    public $boyscoaches = [];

    public function label() {
        return $this->school;
    }

    public function init($row) {
        // assuming $row is numeric array (PDO::FETCH_NUM)
        $this->schoolId       = $row[0];
        $this->school         = $row[1];
        $this->classification = $row[2];
        $this->girlscoaches['head']      = new Coach($row[3],  $row[4]);
        $this->girlscoaches['assistant'] = new Coach($row[5],  $row[6]);
        $this->girlscoaches['dive']      = new Coach($row[7],  $row[8]);
        $this->boyscoaches['head']       = new Coach($row[9],  $row[10]);
        $this->boyscoaches['assistant']  = new Coach($row[11], $row[12]);
        $this->boyscoaches['dive']       = new Coach($row[13], $row[14]);
    }

    public static function getSchool($school, $classification, $create = false) {
        $schools = self::loadSchools($classification);
        foreach ($schools as $item) {
            if ($item->school === $school) {
                return $item;
            }
        }
        if ($create) {
            $conn = WiscaDB::get();
            $stmt = $conn->prepare("INSERT INTO Schools (school, size) VALUES (?, ?)");
            $stmt->execute([$school, $classification]);
            $conn = null;
        }
        return self::getSchool($school, $classification);
    }

    public function reassign($to) {
        if ($this->schoolId != $to) {
            $conn = WiscaDB::get();

            $stmt = $conn->prepare("UPDATE Athletes SET schoolId = ? WHERE schoolId = ?");
            $stmt->execute([$to, $this->schoolId]);

            $stmt = $conn->prepare("UPDATE Results SET participantId = ? WHERE type='t' AND participantId = ?");
            $stmt->execute([$to, $this->schoolId]);

            $stmt = $conn->prepare("DELETE FROM Schools WHERE schoolId = ?");
            $stmt->execute([$this->schoolId]);

            $conn = null;
        }
    }

    public function save() {
        $conn = WiscaDB::get();
        $sql = "UPDATE Schools 
                   SET size = ?, school = ?, 
                       coachId1 = ?, coachId2 = ?, coachId3 = ?, 
                       coachId4 = ?, coachId5 = ?, coachId6 = ?
                 WHERE schoolId = ?";
        $params = [
            $this->classification, $this->school,
            $this->girlscoaches['head']->userId,
            $this->girlscoaches['assistant']->userId,
            $this->girlscoaches['dive']->userId,
            $this->boyscoaches['head']->userId,
            $this->boyscoaches['assistant']->userId,
            $this->boyscoaches['dive']->userId,
            $this->schoolId
        ];
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $conn = null;
    }

    public static function lookupSchool($schoolId) {
        $conn = WiscaDB::get();
        $sql = "SELECT s.schoolId, s.school, s.size,
                       coach1.userId, coach1.name, coach2.userId, coach2.name,
                       coach3.userId, coach3.name, coach4.userId, coach4.name,
                       coach5.userId, coach5.name, coach6.userId, coach6.name
                  FROM Schools AS s
             LEFT JOIN Users AS coach1 ON coach1.userId = s.coachId1
             LEFT JOIN Users AS coach2 ON coach2.userId = s.coachId2
             LEFT JOIN Users AS coach3 ON coach3.userId = s.coachId3
             LEFT JOIN Users AS coach4 ON coach4.userId = s.coachId4
             LEFT JOIN Users AS coach5 ON coach5.userId = s.coachId5
             LEFT JOIN Users AS coach6 ON coach6.userId = s.coachId6
                 WHERE s.schoolId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$schoolId]);
        $row = $stmt->fetch(PDO::FETCH_NUM);

        $school = null;
        if ($row) {
            $school = new School();
            $school->init($row);
        }
        $conn = null;
        return $school;
    }

    public static function loadSchools($classification) {
        $conn = WiscaDB::get();
        $sql = "SELECT s.schoolId, s.school, s.size,
                       coach1.userId, coach1.name, coach2.userId, coach2.name,
                       coach3.userId, coach3.name, coach4.userId, coach4.name,
                       coach5.userId, coach5.name, coach6.userId, coach6.name
                  FROM Schools AS s
             LEFT JOIN Users AS coach1 ON coach1.userId = s.coachId1
             LEFT JOIN Users AS coach2 ON coach2.userId = s.coachId2
             LEFT JOIN Users AS coach3 ON coach3.userId = s.coachId3
             LEFT JOIN Users AS coach4 ON coach4.userId = s.coachId4
             LEFT JOIN Users AS coach5 ON coach5.userId = s.coachId5
             LEFT JOIN Users AS coach6 ON coach6.userId = s.coachId6 ";
        $params = [];
        if ($classification) {
            $sql .= "WHERE s.size = ? ";
            $params[] = $classification;
        }
        $sql .= "ORDER BY s.school";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $schools = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $school = new School();
            $school->init($row);
            $schools[] = $school;
        }
        $conn = null;
        return $schools;
    }
}
?>
