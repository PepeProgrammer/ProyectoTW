<?php
require_once '../models/Database.php';

class AsideInfo
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    private function getRoomsNum()
    {
        $prepare = $this->db->prepare("SELECT COUNT(*) FROM rooms");
        $prepare->execute();
        $rooms = $prepare->get_result()->fetch_all();
        return $rooms[0][0];
    }

    private function getFreeRoomsNum() {
        $prepare = $this->db->prepare("SELECT COUNT(*) FROM rooms WHERE id NOT IN 
                                 (SELECT room_id FROM bookings WHERE state = 'confirmed' and checkin <= CURDATE() 
                                                                 and checkout >= CURDATE())");
        $prepare->execute();
        $rooms = $prepare->get_result()->fetch_all();
        return $rooms[0][0];
    }

    private function getMaxPeople() {
        $prepare = $this->db->prepare("SELECT SUM(capability) FROM rooms");
        $prepare->execute();
        $num = $prepare->get_result()->fetch_all();
        return $num[0][0];
    }

    private function getPeopleHosted() {
        $prepare = $this->db->prepare("SELECT SUM(people_num) FROM bookings WHERE state = 'confirmed' and checkin <= CURDATE() and checkout >= CURDATE()");
        $prepare->execute();
        $num = $prepare->get_result()->fetch_all();
        return $num[0][0];
    }

    public function getAsideInfo() {
        $aside = [];
        $aside['rooms'] = $this->getRoomsNum();
        $aside['freeroms'] = $this->getFreeRoomsNum();
        $aside['max'] = $this->getMaxPeople();
        $aside['hosted'] = $this->getPeopleHosted();
        return $aside;
    }

}