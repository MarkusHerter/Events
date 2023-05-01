<?php

class UserToEvents
{



    public static function createTable()
    {
        $mysqli = Db::connect();
        $mysqli->query('DROP TABLE if exists UserToEvents');
        $sql = 'CREATE TABLE UserToEvents (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, event_id INT)';
        $mysqli->query($sql);
        $mysqli->query("INSERT INTO UserToEvents VALUES(NULL, 1,1)");
        $mysqli->query("INSERT INTO UserToEvents VALUES(NULL, 2,1)");
    }

    public static function save($user_id, $event_id): bool
    {
        $mysqli = Db::connect();
        $stmt = $mysqli->prepare("INSERT INTO UserToEvents VALUES (Null,?,?)");
        $stmt->bind_param('ii', $user_id, $event_id);
        return $stmt->execute();
    }

    public static function delete($user_id, $event_id): bool
    {
        $mysqli = Db::connect();
        $stmt = $mysqli->prepare("DELETE FROM UserToEvents WHERE user_id=? AND event_id=?");
        $stmt->bind_param('ii', $user_id, $event_id);
        return $stmt->execute();
    }
    public static function deleteByEventId($event_id): bool
    {
        $mysqli = Db::connect();
        $stmt = $mysqli->prepare("DELETE FROM UserToEvents WHERE event_id=?");
        $stmt->bind_param('i',  $event_id);
        return $stmt->execute();
    }

    /**
     * @param int $id
     * @return array<int>
     */
    public static function getUserIdsByEventId(int $id): array
    {
        $user = array();
        $mysqli = Db::connect();
        $sql = sprintf('SELECT user_id FROM UserToEvents WHERE event_id = %s', $id);
        $result = $mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $user[] = $row['user_id'];
        }
        return $user;
    }

    public static function userIsIn($userId, $eventId){
        $mysqli = Db::connect();
        $sql = sprintf('SELECT * FROM UserToEvents WHERE user_id =%s AND event_id = %s', $userId, $eventId);
        $result = $mysqli->query($sql);
        if ($row = $result->fetch_assoc()){
            return true;
        }
        return false;
    }
    public static function getEventIdsByUserId($id):array{
        $events = array();
        $mysqli = Db::connect();
        $sql = sprintf('SELECT event_id FROM UserToEvents WHERE user_id = %s', $id);
        $result = $mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $events[] = $row['event_id'];
        }
        return $events;
    }

}