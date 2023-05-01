<?php

class EventsToGroups
{
    public static function createTable()
    {
        $mysqli = Db::connect();
        $mysqli->query('DROP TABLE if exists EventsToGroups');
        $sql = 'CREATE TABLE EventsToGroups (id INT AUTO_INCREMENT PRIMARY KEY, event_id INT, group_id INT)';
        $mysqli->query($sql);
        $mysqli->query("INSERT INTO EventsToGroups VALUES(NULL, 1,1)");
        $mysqli->query("INSERT INTO EventsToGroups VALUES(NULL, 2,2)");
    }

    public static function save($event_id, $group_id): bool
    {
        $mysqli = Db::connect();
        $stmt = $mysqli->prepare("INSERT INTO EventsToGroups VALUES (Null,?,?)");
        $stmt->bind_param('ii', $event_id, $group_id);
        return $stmt->execute();
    }

    public static function delete($given, $id1, $id2=null): bool
    {
        $mysqli = Db::connect();
        if ($given==="eg"){
            $stmt = $mysqli->prepare("DELETE FROM EventsToGroups WHERE event_id = ? AND group_id=?");
            $stmt->bind_param('ii', $id1, $id2);
        }
        if ($given==='g'){
            $stmt = $mysqli->prepare("DELETE FROM EventsToGroups WHERE group_id=?");
            $stmt->bind_param('i', $id1);
        }
        if ($given==='e'){
            $stmt = $mysqli->prepare("DELETE FROM EventsToGroups WHERE event_id=?");
            $stmt->bind_param('i', $id1);
        }
        return $stmt->execute();
    }

    /**
     * @param int $id
     * @return array<int>
     */
    public static function getGroupIdByEvent(int $id): array
    {
        $groups = array();
        $mysqli = Db::connect();
        $sql = sprintf('SELECT group_id FROM EventsToGroups WHERE event_id = %s', $id);
        $result = $mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $groups[] = $row['group_id'];
        }
        return $groups;
    }
    /**
     * @param int $id
     * @return array<int>
     */
    public static function loadEventIds(int $group_id): array
    {
        $events = array();
        $mysqli = Db::connect();
        $sql = sprintf('SELECT event_id FROM EventsToGroups WHERE group_id = %s', $group_id);
        $result = $mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $events[] = $row['event_id'];
        }
        return $events;
    }

}