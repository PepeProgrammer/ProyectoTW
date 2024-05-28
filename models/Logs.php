<?php

class Logs
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getLogs()
    {
        $prepare = $this->db->prepare("SELECT * FROM logs");
        $prepare->execute();
        return $prepare->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function insertLog($log)
    {
        $prepare = $this->db->prepare("INSERT INTO logs (description) VALUES (?)");
        $prepare->bind_param('s', $log);
        $prepare->execute();
    }
}