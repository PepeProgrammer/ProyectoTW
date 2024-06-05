<?php
require_once "../models/Booking.php";

$expiration = 600; //las cookies duran 10 minutos
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking = new Booking();
    $data = [];
    if (isset($_POST['search']) && $_POST['search'] !== "") {
        $data['search'] = $_POST['search'];
        setcookie('search', $_POST['search'], time() + $expiration, '/'); //La barra es para que el dominio de la cookie sea la raíz del proyecto y poderla usar en otros archivos
    } else {
        setcookie('search', 'data_empty', time() + $expiration, '/');
    }
    if (isset($_POST['checkin']) && $_POST['checkin'] !== "") {
        $data['checkin'] = $_POST['checkin'];
        setcookie('checkin', $_POST['checkin'], time() + $expiration, '/');
    } else {
        setcookie('checkin', 0, time() + $expiration, '/');
    }
    if (isset($_POST['checkout']) && $_POST['checkout'] !== "") {
        $data['checkout'] = $_POST['checkout'];
        setcookie('checkout', $_POST['checkout'], time() + $expiration, '/');

    } else {
        setcookie('checkout', 0, time() + $expiration, '/');
    }
    if (isset($_POST['orderby'])) {
        $data['orderby'] = $_POST['orderby'];
        setcookie('orderby', $_POST['orderby'], time() + $expiration, '/');


    }
    if (isset($_POST['order'])) {
        $data['order'] = $_POST['order'];
        setcookie('order', $_POST['order'], time() + $expiration, '/');
    }



    if($_SESSION['user']['type'] === 'recepcionist') {
        $bookings = $booking->getBookingsFiltered($data);
    } else {
        $bookings = $booking->getBookingsFiltered($data, $_SESSION['user']['id']);
    }
    echo json_encode($bookings);
    exit();
}