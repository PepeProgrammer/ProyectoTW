<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Rooms.php";
require_once "../models/Booking.php";

session_start();

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);


$asideInfo = new AsideInfo();
$bookingDb = new Booking();
$roomDb = new Rooms();
$twigVariables = [];
$twigVariables['aside'] = $asideInfo->getAsideInfo();

if(!isset($_SESSION['user']) || $_SESSION['user']['type'] === 'admin') {
    header('Location: index.php');
    exit();
}

if(isset($_SESSION['user'])) {
    $twigVariables['user'] = $_SESSION['user'];
}

if(isset($_POST['delete'])) {
    if($bookingDb->deleteBooking($_POST['delete']) !== false) {
        $twigVariables['success'] = "Reserva eliminada correctamente";
    } else {
        $twigVariables['error'] = "Error al eliminar la reserva";
    }
}

$filters = [];

if(isset($_COOKIE['search'])) { // si es igual a 1 tiene la cookie de sesión
    if($_COOKIE['search'] !== 'data_empty'){
        $twigVariables['filters']['search'] = $_COOKIE['search'];
    } else {
        $twigVariables['filters']['search'] = "";
    }
    if(isset($_COOKIE['checkin'])){
        $twigVariables['filters']['checkin'] = $_COOKIE['checkin'];
    }
    if(isset($_COOKIE['checkout'])){
        $twigVariables['filters']['checkout'] = $_COOKIE['checkout'];
    }

    $twigVariables['filters']['orderby'] = $_COOKIE['orderby'];
    $twigVariables['filters']['order'] = $_COOKIE['order'];
    $filters = $twigVariables['filters'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['hidden_id']) and isset($_POST['comments'])){
        if($_POST['hidden_id'] != ""){
            $comment = strip_tags($_POST['comments']);

            if( $bookingDb->modifyBooking($_POST['hidden_id'],$comment) ){
                $twigVariables['success'] = "Comentario modificado correctamente";
            } else {
                $twigVariables['error'] = "Error al añadir el comentario";
            }
        }
    }
}

if($_SESSION['user']['type'] === "recepcionist") {
    $twigVariables['bookings'] = $bookingDb->getBookingsFiltered($filters);

} else {
    $twigVariables['bookings'] = $bookingDb->getBookingsFiltered($filters, $_SESSION['user']['id']);
}

echo $twig->render('bookings.twig', $twigVariables);