<?php

class Type
{
    private int $id;
    public string $name;

    /**
     * @param ?int $id
     * @param string $name
     */
    public function __construct(?int $id, string $name)
    {
        if (isset($id)){
            $this->id = $id;
        }
        $this->name = $name;
    }


    public static function createTable()
    {
        $mysqli = Db::connect();
        $mysqli->query('DROP TABLE if exists Type');
        $sql = 'CREATE TABLE Type (id INT AUTO_INCREMENT PRIMARY KEY, name Varchar(64))';
        $mysqli->query($sql);
        $mysqli->query("INSERT INTO Type VALUES(NULL, 'Tischtennis')");
        $mysqli->query("INSERT INTO Type VALUES(NULL, 'Brettspiele')");
    }

    public static function loadNameById($id){
        $mysqli = Db::connect();
        $sql = "select name from Type WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['name'];
        }else {
            throw new Exception("Type ist nicht vorhanden");
        }
    }
    public static function loadIdByName($name):int{
        $mysqli = Db::connect();
        $sql = "select id from Type WHERE name = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s',$name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['id'];
        }else {
            return 0;
        }
    }
    public static function saveNewType($name):int{
        $mysqli = Db::connect();
        $sql ="INSERT INTO Type VALUES(NULL, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $name);
        $stmt->execute();
        return $stmt->insert_id;
    }
}