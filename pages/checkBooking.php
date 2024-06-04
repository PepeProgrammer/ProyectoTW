<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Room.php";
require_once "../models/Booking.php";

session_start();

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);


$asideInfo = new AsideInfo();
$bookingDb = new Booking();
$roomDb = new Room();
$twigVariables = [];
$twigVariables['aside'] = $asideInfo->getAsideInfo();


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

$twigVariables['bookings'] = $bookingDb->getBookingsList();

echo $twig->render('checkBooking.twig', $twigVariables);