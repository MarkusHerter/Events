<?php

class UserToGroups
{



    public static function createTable()
    {
        $mysqli = Db::connect();
        $mysqli->query('DROP TABLE if exists UserToGroups');
        $sql = 'CREATE TABLE UserToGroups (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, group_id INT)';
        $mysqli->query($sql);
        $mysqli->query("INSERT INTO UserToGroups VALUES(NULL, 1,1)");
        $mysqli->query("INSERT INTO UserToGroups VALUES(NULL, 2,1)");
        $mysqli->query("INSERT INTO UserToGroups VALUES(NULL, 1,2)");
    }

    public static function save($userId, $groupId): bool
    {
        $mysqli = Db::connect();
        $stmt = $mysqli->prepare("SELECT * FROM UserToGroups WHERE group_id=? AND user_id=?");
        $stmt->bind_param('ss',  $groupId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->fetch_assoc()){
            return false;
        } else {
            $stmt = $mysqli->prepare("INSERT INTO UserToGroups VALUES (Null,?,?)");
            $stmt->bind_param('ii', $userId, $groupId);
            $stmt->execute();
            return true;
        }

    }

    public static function delete($user_id, $group_id): bool
    {
        $mysqli = Db::connect();
        $stmt = $mysqli->prepare("DELETE FROM UserToGroups WHERE group_id=? AND user_id=?");
        $stmt->bind_param('ii', $group_id, $user_id);
        return $stmt->execute();
    }

    public static function groupIsIn($groupId):bool{
        $mysqli = Db::connect();
        $stmt = $mysqli->prepare("SELECT * FROM UserToGroups WHERE group_id=?");
        $stmt->bind_param('s',  $groupId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()){
            return true;
        } else {
            return false;
        }
    }
    /**
     * @param int $id
     * @return array<int>
     */
    public static function loadGroupIds(int $user_id): array
    {
        $groups = array();
        $mysqli = Db::connect();
        $sql = sprintf('SELECT group_id FROM UserToGroups WHERE user_id = %s', $user_id);
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
    public static function getUserIdsByGroup(int $id): array
    {
        $user = array();
        $mysqli = Db::connect();
        $sql = sprintf('SELECT user_id FROM UserToGroups WHERE group_id = %s', $id);
        $result = $mysqli->query($sql);
        while ($row = $result->fetch_assoc()) {
            $user[] = $row['user_id'];
        }
        return $user;
    }
    public static function UserIsInGroup($userId, $groupId):bool{
        $mysqli = Db::connect();
        $stmt = $mysqli->prepare("SELECT * FROM UserToGroups WHERE user_id = ? AND group_id=?");
        $stmt->bind_param('ii', $userId, $groupId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()){
            return true;
        } else {
            return false;
        }
    }

}