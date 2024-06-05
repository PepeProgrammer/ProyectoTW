<?php
require_once '../models/Database.php';

class Booking
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function createBooking($data)
    {
        $sql = "INSERT INTO bookings (user_id, room_id, people_num, comments, checkin, checkout, state, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $prepare = $this->db->prepare($sql); //Prepara la consulta para prevenir ataque de inyección sql
        $prepare->bind_param("iiisssss", $data['user_id'], $data['room_id'], $data['people_num'], $data['comments'], $data['checkin'], $data['checkout'], $data['state'], $data['timestamp']);
        try {
            $prepare->execute();
        } catch (Exception $e) {
            return false;
        }
        return $prepare->insert_id;
    }

    public function cleanBookings()
    {
        $now = date("Y-m-d H:i:s"); // Obtenemos la fecha y hora actual
        // AQUI PODEMOS MODIFICAR EL TIEMPO DE ESPERA (restamos en segundos)       v
        $nowMinus30Seconds = date("Y-m-d H:i:s", strtotime($now) - 30); // Le restamos 30 segundos

        // Eliminamos todas las reservas cuyo campo "state" == waiting y su campo "timestamp" > fecha y hora actual - 30s
        $sql = "DELETE FROM bookings WHERE state = 'pending' AND timestamp < ?";
        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("s", $nowMinus30Seconds);
        try {
            $prepare->execute();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function getBookings()
    {
        $prepare = $this->db->prepare("SELECT * FROM bookings ORDER BY timestamp ASC ");
        $prepare->execute();
        return $prepare->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getBookingsFiltered($data = [], $user = -1)
    {
        if(empty($data)) {
            $data['search'] = "";
            $data['checkin'] = "";
            $data['checkout'] = "";
        }

        if(!isset($data['search']) || trim($data['search']) == "") {
            $search = "%";
        } else {
            $search = "%" . $data['search'] . "%"; //con esto nos aseguramos de que al buscar el texto pueda aparecer cualquiera que contenga esa cadena
        }
        
        if(!isset($data['orderby']) || $data['orderby'] == 'old') {
            $orderby = 'checkin';
        } elseif($data['orderby'] == 'day_num') {
            $orderby = 'DATEDIFF(checkout, checkin)';
        }

        if(isset($data['order'])) {
            $order = $data['order'];
        } else {
            $order = 'ASC';
        }

        if (!isset($data['checkin']) || $data['checkin'] == "" || $data['checkin'] == 0) {
            $data['checkin'] = "1980-01-01";
        }
        if (!isset($data['checkout']) || $data['checkout'] == "" || $data['checkout'] == 0) {
            $data['checkout'] = "2200-12-31";
        }


        if($user === -1){
            $sql = "SELECT b.*, u.email, r.room_num FROM bookings AS b INNER JOIN users AS u ON b.user_id = u.id INNER JOIN rooms AS r ON b.room_id = r.id WHERE b.state = 'confirmed' AND b.comments LIKE ? AND b.checkin >= ? AND b.checkout <= ? ORDER BY $orderby $order";
            $prepare = $this->db->prepare($sql);
            $prepare->bind_param("sss", $search, $data['checkin'], $data['checkout']);
        } else {
            $sql = "SELECT b.*, u.email, r.room_num FROM bookings AS b INNER JOIN users AS u ON b.user_id = u.id INNER JOIN rooms AS r ON b.room_id = r.id WHERE b.state = 'confirmed' AND b.comments LIKE ? AND b.checkin >= ? AND b.checkout <= ? AND b.user_id = ? ORDER BY $orderby $order";
            $prepare = $this->db->prepare($sql);
            $prepare->bind_param("sssi", $search, $data['checkin'], $data['checkout'], $user);
        }

        try {
            $prepare->execute();
        } catch (Exception $e) {
            return false;
        }
        return $prepare->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function confirmBooking($id)
    {
        $this->cleanBookings();
        if (!$this->getBookingById($id)) {
            return false;
        }
        // Cambiamos el estado de la reserva a confirmado
        $sql = "UPDATE bookings SET state = 'confirmed' WHERE id = ?";
        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("i", $id);
        try {
            $prepare->execute();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function getBookingById($id)
    {
        // comprobar que sigue existiendo la reserva
        $sql = "SELECT * FROM bookings WHERE id = ?";
        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("i", $id);
        $prepare->execute();
        $result = $prepare->get_result()->fetch_assoc();
        if ($result === null) {
            return false;
        }
        return $result;

    }

    public function deleteBooking($id)
    {
        $sql = "DELETE FROM bookings WHERE id = ?";
        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("i", $id);
        try {
            $prepare->execute();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function modifyBooking($id,$comment)
    {
        $sql = "UPDATE bookings SET comments = ? WHERE id = ?";
        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("si", $comment, $id);
        try {
            $prepare->execute();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}