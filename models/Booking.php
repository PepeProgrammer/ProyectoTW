<?php
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

    public function confirmBooking($id)
    {
        if( !$this->getBookingById($id) ) {
            return false;
        }

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
}