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
        $prepare = $this->db->prepare("SELECT images.id, image FROM images INNER JOIN room_img ON images.id = room_img.img_id WHERE room_img.room_id = ?");
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

    public function getRoomById($id){
        $prepare = $this->db->prepare("SELECT * FROM rooms WHERE id = ?");
        $prepare->bind_param("i", $id);
        $prepare->execute();
        return $prepare->get_result()->fetch_assoc();
    }

    public function updateRoom($data, $id){
        echo $id;
        $sql = "UPDATE rooms SET room_num = ?, price = ?, capacity = ?, description = ? WHERE id = ?";
        $prepare = $this->db->prepare($sql);
        $prepare->bind_param("sdisi", $data['number'], $data['price'], $data['capacity'], $data['description'], $id);
        try {
            $prepare->execute();
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return true;
    }

    public function deleteRoomImages($id, $images){
        $sql = "DELETE FROM room_img WHERE room_id = ? AND img_id = ?";
        $prepare = $this->db->prepare($sql);
        foreach ($images as $image) {
            $prepare->bind_param("ii", $id, $image);
            $prepare->execute();
        }
    }
}