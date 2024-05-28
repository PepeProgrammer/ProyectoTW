<?php
class Users
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function createUser($data)
    {
        $sql = "INSERT INTO users (name, lastname, dni, email, pass, card, type) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $prepare = $this->db->prepare($sql); //Prepara la consulta para prevenir ataque de inyección sql
        $prepare->bind_param("sssssis", $data['name'], $data['lastname'], $data['dni'], $data['email'], $data['pass'], $data['card'], $data['type']);
        try {
            $prepare->execute();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}