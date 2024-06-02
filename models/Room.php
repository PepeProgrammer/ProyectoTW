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

    public function getRoomImages($id) {
        $prepare = $this->db->prepare("SELECT image FROM images INNER JOIN room_img ON images.id = room_img.img_id WHERE room_img.room_id = ?");
        $prepare->bind_param("i", $id);
        $prepare->execute();
        return $prepare->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}