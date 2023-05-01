<?php

class User
{
    private string $name;
    private string $passwd;
    private int $id;
    private array $groups;
    private array $allEvents;
    public string $email;


    /**
     * @param string $name
     * @param String $passwd
     * @param ?int $id
     */
    public function __construct(string $name, string $email, string $passwd,  ?int $id=null)
    {
        $this->name = $name;
        $this->passwd = $passwd;
        $this->email = $email;
        if (isset($id)) {
            $this->id = $id;
        }
    }

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

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    //-------------------------------User Anlegen-------------------------------------------
    public function save(): void
    {
        if (!$this->isNameValid()){
            throw new Exception("Name ist ungültig. Keine Lehrzeichen, nur Buchstaben und Zahlen sind erlaubt.");
        }
        if (!$this->isEmailAvailable()){
            throw new Exception("Email-Adresse existiert bereits");
        }
        if (!$this->isPasswdValid()){
            throw new Exception("Passwort ist ungültig. Mindestens acht Zeichen, mindestens eine Zahl und ein Sonderzeichen");
        }
        if (!filter_var($this->email,FILTER_VALIDATE_EMAIL)){
            throw new Exception ("Email-Adresse ist ungültig.");
        }
        if(!isset($this->id)){
            $this->insert();
        } else {
            $this->update();
        }
    }

    private function isNameValid(): bool
    {
        // $name muss mindestens ein Zeichen enthalten, alle Zeichen müssen alphanumerisch sein
        if (strlen($this->name) > 0 && ctype_alnum($this->name)) {
            return true;
        }
        return false;
    }

    private function isPasswdValid(): bool
    {
        if (strlen($this->passwd) > 8 && preg_match('~[0-9]+~', $this->passwd) && !ctype_alnum($this->passwd)) {
            return true;
        }
        return false;
    }

    private function isEmailAvailable():bool
    {
        // überprüfen, ob es den Namen schon in der Tabelle user gibt
        $mysqli = Db::connect();
        if (!isset($this->id)) {
            $sql = "SELECT id FROM user WHERE email=?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("s", $this->email);
        } else {
            $sql = "SELECT id FROM user WHERE email=? AND NOT id = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("si", $this->email, $this->id);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            return false;
        }
        return true;
    }
    public static function newPassword($email, $passwd){
        $mysqli = Db::connect();
        $sql = "UPDATE user SET passwd=? WHERE email=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $passwd, $email);
        $stmt->execute();
    }
    public static function emailExists($email):bool
    {
        // überprüfen, ob es den Namen schon in der Tabelle user gibt
        $mysqli = Db::connect();
        $sql = "SELECT id FROM user WHERE email=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()) {
            return true;
        }
        return false;
    }
    private function insert(){
        $passHash = password_hash($this->passwd, PASSWORD_DEFAULT,['cost' => 12]);
        $mysqli = Db::connect();
        $sql = "INSERT INTO user(id, name, email, passwd) VALUES( NULL, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sss", $this->name, $this->email,$passHash);
        $stmt->execute();
        $this->id = $stmt->insert_id;
    }

    private function update(){
        $mysqli = Db::connect();
        $sql = "UPDATE user SET name=?, passwd=? WHERE id=?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ssi", $this->name, $this->email,$this->passwd, $this->id);
        $stmt->execute();
    }


//------------------------------Ende User anlegen--------------------------------
    public static function createTable()
    {
        $mysqli = Db::connect();
        $mysqli->query('DROP TABLE if exists user');
        $sql = 'CREATE TABLE user (id INT AUTO_INCREMENT PRIMARY KEY, name Varchar(32), passwd VARCHAR(128), email VARCHAR(64), pic MEDIUMBLOB)';
        $mysqli->query($sql);
        $name = password_hash('passwort123', PASSWORD_DEFAULT);
        $mysqli->query(sprintf("INSERT INTO user VALUES(NULL, 'Klausi33','%s', 'm.herter@web.de', NULL)", $name));
        $mysqli->query(sprintf("INSERT INTO user VALUES(NULL, 'Sibylle','%s', 's.s@s.s' , NULL)", $name));

    }
//---------------------------------------------------------------------------------

    public static function getIdByEmailAndPasswd($email, $passwd) {
        $mysqli = Db::connect();
        $stmt = $mysqli->prepare("SELECT id, passwd FROM user WHERE email=?");
        $stmt->bind_param('s',$email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (password_verify($passwd, $row['passwd'])) {
                return $row['id'];
            }
        }
        throw new Exception("Email oder Passwort nicht korrekt");
    }
    public static function isCorrectPassword($id, $passwd): bool{
        $mysqli = Db::connect();
        $stmt = $mysqli->prepare("SELECT passwd FROM user WHERE id=?");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (password_verify($passwd, $row['passwd'])) {
                return true;
            }
        }
        return false;
    }
    public static function changePassword($id, $passwd){
        $user = new User ('lala','mama', $passwd);
        if ($user->isPasswdValid()) {
            $passHash = password_hash($passwd, PASSWORD_DEFAULT,['cost' => 12]);
            $mysqli = Db::connect();
            $stmt = $mysqli->prepare("UPDATE user SET passwd=? WHERE id=?");
            $stmt->bind_param('ss',$passHash, $id);
            $stmt->execute();
        } else throw new Exception('Neues Passwort ist nicht gültig. Es muss 9 Zeichen lang sein, mindestens eine Zahl und ein Sonderzeichen enthalten!');

    }

/*    public function loadAllEvents():array
    {
        $this->allEvents = [];
        $eventIds = [];
        foreach ($this->groups as $id) {
            $eventIds = array_merge($eventIds, EventsToGroups::loadEventIds($id));
        }
        foreach ($eventIds as $id) {
            $this->allEvents[] = Event::loadById($id);
        }
        return $this->allEvents;

    }*/

    public static function getNameById(int $id){
        $mysqli = Db::connect();
        $sql = sprintf("Select * FROM user WHERE id = %s", $id);
        $result = $mysqli->query($sql);
        $userName = null;
        if ($row = $result->fetch_assoc()){
            $userName = $row['name'];
        }
        return $userName;
    }
    public static function getNameAndPicById(int $id){
        $mysqli = Db::connect();
        $sql = sprintf("Select * FROM user WHERE id = %s", $id);
        $result = $mysqli->query($sql);
        $user = null;
        if ($row = $result->fetch_assoc()){
            $user = [$row['name'],$row['pic']];
        }
        return $user;
    }
    public static function savePicInUser(int $id, string $filename){
        $mysqli = Db::connect();
        $handle = fopen($filename,"rb");
        $base64 =fread($handle,filesize($filename));
     //   $base64 = str_replace(' ','+', $base64);
        echo $base64;
        echo filesize($filename)." ";
        $sql = "UPDATE user SET pic = ? WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('si',$base64, $id);
        return $stmt->execute();
    }
}