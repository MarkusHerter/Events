<?php

class Db
{
    private static mysqli $db;

    public static function connect()
    {
        if (!isset(self::$db)) {
            self::$db = new mysqli(DB_SERVER, DB_USER, DB_PASSWD, DB_NAME);
        }
        return self::$db;
    }
    public static function createDb(){
        $conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWD);
    // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

// Create database
/*        $sql = "CREATE DATABASE ".DB_NAME;
        if ($conn->query($sql) === TRUE) {
            echo "Database created successfully";
        } else {
            echo "Error creating database: " . $conn->error;
        }*/
        User::createTable();
        Group::createTable();
        Event::createTable();
        Type::createTable();
        UserToGroups::createTable();
        UserToEvents::createTable();
        EventsToGroups::createTable();
    }
}