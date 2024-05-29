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

    public function getUser($id)
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("i", $id);
        try {
            $prepare->execute();
            return $prepare->get_result()->fetch_assoc();
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("s", $email);
        try {
            $prepare->execute();
            return $prepare->get_result()->fetch_assoc();
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function getUsers(): array
    {
        $sql = "SELECT * FROM users";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getUsersByType($type)
    {
        $sql = "SELECT * FROM users WHERE type = ?";
        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("s", $type);
        try {
            $prepare->execute();
            return $prepare->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function updateUser($data, $id)
    {
        if ($data['pass'] !== "") { //Si se actualiza la contraseña debemos poder modificarla
            $sql = "UPDATE users SET name = ?, lastname = ?, dni = ?, email = ?, pass = ?, card = ?";
            $bind = "ssssss";
        } else {
            $sql = "UPDATE users SET name = ?, lastname = ?, dni = ?, email = ?, card = ?";
            $bind = "sssss";
        }

        if (isset($data['type'])) {
            $sql .= ", type = ?";
            $bind .= "s";
        }

        $sql .= " WHERE id = ?";
        $bind .= "i";
        $prepare = $this->db->prepare($sql);

        if ($data['pass'] !== "") {
            if (isset($data['type']))
                $prepare->bind_param($bind, $data['name'], $data['lastname'], $data['dni'], $data['email'], $data['pass'], $data['card'], $data['type'], $id);
            else
                $prepare->bind_param($bind, $data['name'], $data['lastname'], $data['dni'], $data['email'], $data['pass'], $data['card'], $id);
        } else {
            if (isset($data['type']))
                $prepare->bind_param($bind, $data['name'], $data['lastname'], $data['dni'], $data['email'], $data['card'], $data['type'], $id);
            else
                $prepare->bind_param($bind, $data['name'], $data['lastname'], $data['dni'], $data['email'], $data['card'], $id);
        }

        try {
            $prepare->execute();
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
        return true;
    }


}