<?php
require_once "../models/Booking.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking = new Booking();
    $data = [];
    if (isset($_POST['search'])) {
        $data['search'] = $_POST['search'];
    }
    if (isset($_POST['checkin'])) {
        $data['checkin'] = $_POST['checkin'];
    }
    if (isset($_POST['checkout'])) {
        $data['checkout'] = $_POST['checkout'];
    }
    if(isset($_POST['orderby'])) {
        $data['orderby'] = $_POST['orderby'];

    }
    if(isset($_POST['order'])) {
        $data['order'] = $_POST['order'];
    }



    $bookings = $booking->getBookingFiltered($data);
    echo json_encode($bookings);
    exit();
}