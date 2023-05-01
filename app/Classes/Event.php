<?php

class Event
{
    public int $id;
    public string $type;
    public string $day;
    public string $start;
    public string $end;
    public int $minPers;
    public int $maxPers;
    public int $actPers;
    public string $ort;
    public string $info;
    private int $usercount;
    public int $initiator;
    public array $group;
    public bool $canceled;
    public int $rate;

    /**
     * @param int|null $id
     * @param string $type
     * @param string $day
     * @param string $start
     * @param string $end
     * @param int $minPers
     * @param int $maxPers
     * @param int $actPers
     * @param string $ort
     * @param string $info
     * @param int $initiator
     */
    public function __construct(string $type, string $day, string $start, string $end, int $minPers, int $maxPers,  string $ort, string $info, int $initiator, int $rate, bool $canceled, int $actPers = 0,int $id=null)
    {
        if (isset($id)) {
            $this->id = $id;
        }
        $this->type = $type;
        $this->day = $day;
        $this->start = $start;
        $this->end = $end;
        $this->minPers = $minPers;
        $this->maxPers = $maxPers;
        $this->actPers = $actPers;
        $this->ort = $ort;
        $this->info = $info;
        $this->canceled = $canceled;
        $this->rate = $rate;
        if (isset($usercount)) {
            $this->usercount = $usercount;
        }
        $this->initiator = $initiator;
    }

    //--------------------------Getters und Setters----------------------------------

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }



    //----------------------------------------Ende Getters und Setters---------------------------------------------
    public static function createTable()
    {
        $mysqli = Db::connect();
        $mysqli->query('DROP TABLE if exists Events');
        $sql = 'CREATE TABLE Events (id INT AUTO_INCREMENT PRIMARY KEY, type_id int, day varchar(16), start TIME, end TIME, min INT, max INT, ort VARCHAR(128), info VARCHAR(400), starter_id INT, rate INT, canceled BOOL)';
        $mysqli->query($sql);
        $mysqli->query("INSERT INTO Events VALUES(NULL, 1, '2022-03-01', '18:00:00', '20:30:00', 2, 8, 'Potsdam, Schiffbauergasse 2', 'Bringt Schwimmsachen mit', 1,0, false)");
        $mysqli->query("INSERT INTO Events VALUES(NULL, 2, '2022-03-02', '12:00:00', '14:30:00', 2, 8, 'Berlin', 'Jeder zahlt selber', 2,7, false)");
        $mysqli->query('DROP TABLE if exists old');
    }
    private static function processLoadedEvents($result){
        $events =[];
        while($row=$result->fetch_assoc()) {
            $typeName = Type::loadNameById($row['type_id']);
            $event = new Event($typeName, $row['day'], $row['start'], $row['end'], $row['min'], $row['max'], $row['ort'], $row['info'], $row['starter_id'], $row['rate'],$row['canceled'],count(UserToEvents::getUserIdsByEventId($row['id'])), $row['id']);
            $eventDate = date_create($row['day'] . ' ' . $row['start']);
            if ($eventDate >= date_create()) {
                $events[] = $event;
            } else {
                $event->delete();
            }
        }
        return $events;
    }

    public static function loadById($id){
        $mysqli = Db::connect();
        $sql = "select * from Events WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i',$id);
        $stmt->execute();
        return self::processLoadedEvents($stmt->get_result())[0] ?? null;
    }

    public static function loadAllByStarterId($id){
        $mysqli = Db::connect();
        $stmt = $mysqli->prepare('SELECT * from Events WHERE starter_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return self::processLoadedEvents($stmt->get_result());
    }

    public function addGroup(Array $group){
        $this->group=$group;
    }
    public static function isStarter($eventId, $userId):bool{
        $mysqli = Db::connect();
        $result = $mysqli->query(sprintf("SELECT * FROM Events WHERE id=%s And starter_id=%s", $eventId, $userId));
        if ($result->fetch_assoc()){
            return true;
        }
        return false;

    }

    public function saveEvent($table='Events'): int{
        $mysqli = Db::connect();
        if (($typeId = Type::loadIdByName($this->type)) === 0 ){
            $typeId = Type::saveNewType($this->type);
        }
        if (!isset($this->id) || $table == 'Old') {
            $sql = 'CREATE TABLE IF NOT EXISTS ' . $table .
                " (id INT AUTO_INCREMENT PRIMARY KEY, type_id int, day varchar(16), start TIME, end TIME, min INT, max INT, ort VARCHAR(128), info VARCHAR(400), starter_id INT, rate INT, canceled BOOL);";
            $mysqli->query($sql);
  //          $sql= sprintf("insert into %s values(NULL, %s, %d, %s, %s, %d, %d, %s, %s, %d, %d, %d)", $table, $typeId, $this->day, $this->start, $this->end, $this->minPers, $this->maxPers, $this->ort, $this->info, $this->initiator, $this->rate, $this->canceled);
            if ($table =='Old'){
                $sql = "INSERT INTO Old VALUES(NULL,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            } else {
                $sql = "INSERT INTO Events VALUES(NULL,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            }
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('isssiissiii',  $typeId, $this->day, $this->start, $this->end, $this->minPers, $this->maxPers, $this->ort, $this->info, $this->initiator, $this->rate, $this->canceled);
            $stmt->execute();
            return $stmt->insert_id;
        }
        return 0;
    }
    public function delete(){
        $mysqli = Db::connect();
        $old = $this;
        $old->saveEvent('Old');
        if ((int)$this->rate>0){
            $day = date_create($this->day);
            do {
            $day->modify(sprintf("+ %s day",$this->rate));
                $this->day = date_format($day,"Y-m-d");
            } while (date_create($this->day . ' ' . $this->start) <= date_create());
            $posOfAddedString =strpos($this->info,"<br><span style='color:red'>");
            if ($posOfAddedString) {
                $this->info = substr($this->info, 0,$posOfAddedString);
            }
            $sql =sprintf("UPDATE Events SET day = '%s',info = '%s', canceled=false WHERE id = %s;",$this->day,$this->info,$this->id);
            echo $sql;
            $mysqli->query($sql);
            return new Event($this->type, $this->day, $this->start, $this->end, $this->minPers, $this->maxPers, count(UserToEvents::getUserIdsByEventId($this->id)), $this->ort, $this->info, $this->initiator, $this->rate,$this->canceled,$this->id);

        } else {
            echo "hier ist alles klar: ".$this->id;
            $sql = sprintf("DELETE FROM Events WHERE id=%s",$this->id);
            echo $sql;
            $mysqli->query($sql);
            echo "hier nicht mehr";
        }
        try {
            UserToEvents::deleteByEventId($this->id);
            EventsToGroups::delete('e', $this->id);
        } catch (Exception $e){}

    }
    public static function setCanceled(int $eventId){
        $mysqli = Db::connect();
        $sql = sprintf('UPDATE Events SET canceled = true WHERE id = %s ', $eventId);
        $mysqli->query($sql);
    }
    public static function setRateByEventId($id, $rate){
        $mysqli = Db::connect();
        $sql = 'UPDATE Events SET rate = ? WHERE id = ?; ';
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii',$rate, $id);
        $stmt->execute();
    }
    public static function addInfo($id, $text){
        $mysqli = Db::connect();
        $sql = 'UPDATE Events SET info = CONCAT(IFNULL(info,""), ?) WHERE id = ?; ';
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ss',$text, $id);
        $stmt->execute();
    }
}