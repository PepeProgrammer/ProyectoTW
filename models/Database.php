<?php
require '../extra/dbcredentials.php';
class Database
{
    private static $instance;

    private function __construct()
    {
    }

    private static function connect()
    {
        try {
            $db = new mysqli(DBHOST, DBUSER, DBPASSWORD, DBDATABASE, DBPORT);
        } catch (Exception $e) {
            echo $e->getMessage();
            throw $e;
        }
        return $db;
    }

    public static function getInstance()
    {
        if (!self::$instance)
            self::$instance = self::connect();
        return self::$instance;
    }
}