<?php

class Group
{
    public int $id;
    public string $name;
    public string $passwd;
    public int $privacy;
    public int $owner;

    /**
     * @param ?int $id
     * @param string $name
     */
    public function __construct(string $name, string $passwd, int $privacy, int $owner, int $id= null)
    {
        if (isset($id)) {
            $this->id = $id;
        }
        $this->name = $name;
        $this->passwd = $passwd;
        $this->privacy = $privacy;
        $this->owner = $owner;
    }
//---------------------------------Getters und Setters---------------------------------------------
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

//-------------------------------------Ende Getters und Setters-------------------------------------
    public static function createTable()
    {
        $mysqli = Db::connect();
        $mysqli->query('DROP TABLE if exists `groups`');
        $sql = 'CREATE TABLE `groups` (id INT AUTO_INCREMENT PRIMARY KEY, name Varchar(64),passwd Varchar(12), invitations VARCHAR(1024), privacy INT, user_id INT)';
        $mysqli->query($sql);
        $mysqli->query("INSERT INTO `groups` VALUES(NULL, 'Die Aktionisten','abcd33x2c','',0,1)");
        $mysqli->query("INSERT INTO `groups` VALUES(NULL, 'Die Entwickler','fkla227mf','',0,2)");
    }
    public function save():bool{
        $mysqli = Db::connect();
        $sql = "select * from groups WHERE name= ? AND passwd=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ss',$this->name, $this->passwd);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()){
            return false;
        } else {
            $sql = "INSERT INTO groups VALUES(NULL, ?,?,'[]', ?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param('ssii',$this->name, $this->passwd, $this->privacy, $this->owner);
            $stmt->execute();
            $this->id = $stmt->insert_id;
            return true;
        }
    }
    public static function deleteGroup($id):bool{
        $mysqli = Db::connect();
        $sql="DELETE FROM groups WHERE id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i',$id);
        return $stmt->execute();
    }
    /**
     * @return array<Group>
     */
    public static function loadGroupsByUserId($id): array {
        $groupIds = UserToGroups::loadGroupIds($id);
        $allGroups = [];
        foreach ($groupIds as $id) {
            $allGroups[] = self::loadById($id);
        }
        return $allGroups;
    }
    public static function loadIdByNameAndPass($name, $pass):int{
        $mysqli = Db::connect();
        $sql = "select * from groups WHERE name = ? AND passwd=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ss',$name, $pass);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()){
            return $row['id'];
        } else {
            return 0;
        }
    }

    public static function loadById($id): Group{
        $mysqli = Db::connect();
        $sql = "select * from `groups` WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            // Fehler bei der Vorbereitung der Anweisung
            echo "Error preparing statement: " . $mysqli->error . "\n";
            return null;
        }
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return new Group($row['name'],$row['passwd'], $row['privacy'],$row['user_id'], $row['id']);
        }else {
                throw new Exception("Gruppe ist nicht vorhanden");
        }
    }

    public function checkInvitation($code): bool{
        $mysqli = Db::connect();
        $sql = sprintf("SELECT invitations FROM groups WHERE id = %s",$this->id);
        $result = $mysqli->query($sql);
        $invitations =  json_decode($result->fetch_assoc()['invitations']);
        if ($key = array_search($code, $invitations)){
            unset($invitations[$key]);
            $invitations = json_encode(array_values($invitations));
            $sql = sprintf("UPDATE groups SET invitations = '%s' where id = %s", $invitations, $this->id);
            $mysqli->query($sql);
            return true;
        }
        return false;
    }
    public static function createInvitationCode($groupId): string{
        function newRandString($length = 12):string
        {
            $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMOPQRSTUVWXYZ';
            $charsLength = strlen($chars);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $chars[rand(0, $charsLength - 1)];
            }
            return $randomString;
        }
        $mysqli = Db::connect();
        $sql = sprintf("SELECT invitations FROM groups WHERE id = %s",$groupId);
        $result = $mysqli->query($sql);
        $invitations=  json_decode($result->fetch_assoc()['invitations']);
        if (count($invitations) >10){
            unset($invitations[0]);
            $invitations = array_values($invitations);
        }
        $code = newRandString();
        array_push($invitations, $code);
        $sql = sprintf("UPDATE groups SET invitations = '%s' where id = %s", json_encode($invitations), $groupId);
        $mysqli->query($sql);
        return $code;

    }
}