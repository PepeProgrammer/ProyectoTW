<?php

require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Booking.php";
require_once "../models/Rooms.php";
require_once "../models/Users.php";
require_once "../models/Logs.php";

session_start();


$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$asideInfo = new AsideInfo();
$twigVariables = [];
$twigVariables['aside'] = $asideInfo->getAsideInfo();

$twigVariables['checkin'] = "";
$twigVariables['checkout'] = "";
$twigVariables['checkin_error'] = ""; // el dia de entrada no puede ser anterior al actual
$twigVariables['checkout_error'] = ""; // el dia de salida no puede ser anterior al de entrada
$correct = false;
$twigVariables['showFinding'] = false;
$twigVariables['confirmation'] = "";
$twigVariables['recepcionistView'] = false;

$logs = new Logs();


if (!isset($_SESSION['user']) or ($_SESSION['user']['type'] === "admin")) {
    header("Location: index.php");
    exit;
} else {
    $twigVariables['user'] = $_SESSION['user'];
}

if ($_SESSION['user']['type'] === "recepcionist") {
    $twigVariables['recepcionist_view'] = true;
    $userDb = new Users();
    $twigVariables['users'] = $userDb->getUsers();
    if (isset($_POST['user_id'])) {
        $userid_bd = $_POST['user_id'];
    }
} else {
    $userid_bd = $_SESSION['user']['id'];
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['accept'])) {  // si acepta la reserva, la confirmamos (tenemos en cuenta que ha podido ser borrada)
        $booking = new Booking();

        if( $booking->confirmBooking($_POST['booking_id']) ) {
            $twigVariables['confirmation'] = "readonly";
            $twigVariables['success'] = "Reserva realizada correctamente";
            $logs->insertLog("Reserva confirmada. Id: " . $_POST['booking_id'] );
        } else {
            $twigVariables['error'] = "Disculpe, no se ha podido realizar la reserva";
        }
    }

    if (isset($_POST['decline'])) {
        $booking = new Booking();   // si la rechaza, la eliminamos
        $booking->deleteBooking($_POST['booking_id']);
        $logs->insertLog("Eliminamos reserva. Id: " . $_POST['booking_id'] );
        header("Location: bookings.php");
        exit;

    } else if (isset($_POST['send'])) {
        $correct = true;

        if (isset($_POST['comments'])) {
            $twigVariables['comments'] = strip_tags($_POST['comments']);
        } else {
            $twigVariables['comments'] = "";
        }

        if(isset($_POST['people_num'])) {
            $people_num = strip_tags($_POST['people_num']);
            if($people_num === "") {
                $twigVariables['people_num'] = 1;
            }else{
                $twigVariables['people_num'] = $people_num;
            }
        }

        if (isset($_POST['checkin'])) {
            $twigVariables['checkin'] = strip_tags($_POST['checkin']);
            if ($twigVariables['checkin'] === "") {
                $twigVariables['checkin_error'] = "Debe indicar una fecha de entrada";
                $correct = false;
            } else {
                $checkin = $twigVariables['checkin'];
                $checkin = strtotime($checkin);
                $now = strtotime(date("Y-m-d"));
                if ($checkin < $now) {
                    $twigVariables['checkin_error'] = "La fecha de entrada no puede ser anterior a la actual";
                    $correct = false;
                }
            }
        }

        if (isset($_POST['checkout'])) {
            $twigVariables['checkout'] = strip_tags($_POST['checkout']);
            if ($twigVariables['checkout'] === "") {
                $twigVariables['checkout_error'] = "Debe indicar una fecha de salida";
                $correct = false;
            } else {
                $checkout = $twigVariables['checkout'];
                $checkout = strtotime($checkout);
                if($twigVariables['checkin'] !== "" && isset($checkin)) { // si no se ha introducido la fecha de entrada, no comprobamos
                    if ($checkout < $checkin) {
                        $twigVariables['checkout_error'] = "La fecha de salida no puede ser anterior a la de entrada";
                        $correct = false;
                    }
                }
            }
        }
    }

    if ($correct) { // si todo ha ido bien, buscamos habitación:
        $twigVariables['confirmation'] = "readonly";
        // buscamos en la base de datos

        $roomDb = new Rooms();
        $room = $roomDb->getRoom($twigVariables['people_num'], $twigVariables['checkin'], $twigVariables['checkout']);

        if ($room === null) {
            $room = $roomDb->findSmallerRooms($twigVariables['checkin'], $twigVariables['checkout']);
            if($room === null){
                $twigVariables['error'] = "Disculpe, no disponemos de habitaciones libres para las fechas seleccionadas";
            }else{
                $twigVariables['error'] = "Disculpe, no disponemos de habitaciones con capacidad suficiente para " . $twigVariables['people_num'] . " personas en las fechas seleccionadas";
            }
        } else {
            $twigVariables['room'] = $room;
            $bookingdb = new Booking();
            $booking_id = $bookingdb->createBooking([
                'user_id' => $userid_bd,
                'room_id' => $twigVariables['room']['id'],
                'people_num' => $twigVariables['people_num'],
                'comments' => $twigVariables['comments'],
                'checkin' => $twigVariables['checkin'],
                'checkout' => $twigVariables['checkout'],
                'state' => 'pending',
                'timestamp' => date("Y-m-d H:i:s")
            ]);
            $twigVariables['booking_id'] = $booking_id;
            $twigVariables['showFinding'] = true;
            $roomImages = $roomDb->getRoomImages($room['id']);
            $twigVariables['roomImages'] = $roomImages;
            $logs->insertLog("Nueva reserva pendiente. Id: " . $booking_id );
        }
    }


}

echo $twig->render('createBooking.twig', $twigVariables);