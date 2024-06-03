<?php


class Room
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function insertRoom($data)
    {
        $sql = "INSERT INTO rooms (room_num, price, capacity, description) VALUES (?, ?, ?, ?)";
        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("sdis", $data['number'], $data['price'], $data['capacity'], $data['description']);
        try {
            $prepare->execute();
        } catch (Exception $e) {
            return false;
        }
        return $prepare->insert_id;
    }

    public function insertImage($image)
    {
        $sql = "INSERT INTO images (image) VALUES (?)";
        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("s", $image);
        try {
            $prepare->execute();
        } catch (Exception $e) {
            return false;
        }
        return $prepare->insert_id;

    }

    public function insertRoomImages($room_id, $image_id)
    {
        $sql = "INSERT INTO room_img (room_id, img_id) VALUES (?, ?)";
        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("ii", $room_id, $image_id);
        try {
            $prepare->execute();
        } catch (Exception $e) {
            return false;
        }
        return $prepare->insert_id;
    }


    public function getRooms()
    {
        $prepare = $this->db->prepare("SELECT * FROM rooms ORDER BY capacity ASC ");
        $prepare->execute();
        return $prepare->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getRoomImages($id)
    {
        $prepare = $this->db->prepare("SELECT image FROM images INNER JOIN room_img ON images.id = room_img.img_id WHERE room_img.room_id = ?");
        $prepare->bind_param("i", $id);
        $prepare->execute();
        return $prepare->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteRoom($id)
    {
        $sql = "DELETE FROM rooms WHERE id = ?";
        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("i", $id);
        try {
            $prepare->execute();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function findSmallerRooms($checkin, $checkout)
    {
        // buscamos habitaciones libres sin mirar la capcidad, para comunicar al usuario si existen habitaciones con menos capacidad
        $sql = "SELECT * FROM rooms
                WHERE id NOT IN ( 
                    SELECT room_id FROM bookings 
                    WHERE (checkin <= ? AND checkout >= ?) OR (checkin <= ? AND checkout >= ?) OR (checkin >= ? AND checkout <= ?)
                ) AND state = 'free'
                ORDER BY capacity ASC LIMIT 1;";

        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("ssssss", $checkin, $checkin, $checkout, $checkout, $checkin, $checkout);
        $prepare->execute();
        $result = $prepare->get_result();
        $room = $result->fetch_assoc();
        return $room;
    }

    public function getRoom($capacity, $checkin, $checkout)
    {
        // hacemos limpieza de las reservas caducadas llamando al metodo cleanBookings()
        $booking = new Booking();
        $booking->cleanBookings();

        // generamos una busqueda de la habitación libre teniendo en cuenta la capacidad. (mayor a la exigida y obtenemos la de menor capacidad)
        // y teniendo en cuenta que no se solape con ninguna reserva ya existente.
        $sql = "SELECT * FROM rooms
                WHERE id NOT IN ( 
                    SELECT room_id FROM bookings 
                    WHERE (checkin <= ? AND checkout >= ?) OR (checkin <= ? AND checkout >= ?) OR (checkin >= ? AND checkout <= ?)
                ) AND state = 'free' AND capacity >= ? 
                ORDER BY capacity ASC LIMIT 1;";

        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("ssssssi", $checkin, $checkin, $checkout, $checkout, $checkin, $checkout, $capacity);
        $prepare->execute();
        $result = $prepare->get_result();
        $room = $result->fetch_assoc();
        return $room;
    }
}