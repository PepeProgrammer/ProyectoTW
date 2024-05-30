<?php

class Rooms
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getRoom($capability, $checkin, $checkout)
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
                ) AND state = 'free' AND capability >= ? 
                ORDER BY capability ASC LIMIT 1;";

        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("ssssssi", $checkin, $checkin, $checkout, $checkout, $checkin, $checkout ,$capability);
        $prepare->execute();
        $result = $prepare->get_result();
        $room = $result->fetch_assoc();
        return $room;
    }

    public function findSmallerRooms($checkin, $checkout)
    {
        // buscamos habitaciones libres sin mirar la capcidad, para comunicar al usuario si existen habitaciones con menos capacidad
        $sql = "SELECT * FROM rooms
                WHERE id NOT IN ( 
                    SELECT room_id FROM bookings 
                    WHERE (checkin <= ? AND checkout >= ?) OR (checkin <= ? AND checkout >= ?) OR (checkin >= ? AND checkout <= ?)
                ) AND state = 'free'
                ORDER BY capability ASC LIMIT 1;";

        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("ssssss", $checkin, $checkin, $checkout, $checkout, $checkin, $checkout);
        $prepare->execute();
        $result = $prepare->get_result();
        $room = $result->fetch_assoc();
        return $room;
    }
}
