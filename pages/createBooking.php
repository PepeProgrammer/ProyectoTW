<?php

require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Booking.php";
require_once "../models/Rooms.php";

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$asideInfo = new AsideInfo();
$twigVariables = [];
$twigVariables['aside'] = $asideInfo->getAsideInfo();

$twigVariables['checkin'] = "";
$twigVariables['checkout'] = "";
$twigVariables['checkin_error'] = ""; // el dia de entrada no puede ser anterior al actual
$twigVariables['checkout_error'] = ""; // el dia de salida no puede ser anterior al de entrada
$twigVariables['error'] = "Si ves esto, algo ha ido mal";
$correct = false;
$twigVariables['showFinding'] = false;
$twigVariables['confirmation'] = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['accept'])) {

        // comprobar que sigue existiendo la habitación y no se ha reservado ya

        // insertar en la base de datos la reserva


        $booking = new Booking();
        $booking->confirmBooking($_POST['booking_id']);
        echo "Has pulsado el botón ACEPTAR";
    } else if (isset($_POST['decline'])) {
        header("Location: index.php");
        exit;

    } else if (isset($_POST['send'])) {
        $correct = true;

        if (isset($_POST['comments'])) {
            $twigVariables['comments'] = strip_tags($_POST['comments']);
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
                if($twigVariables['checkin'] !== "") { // si no se ha introducido la fecha de entrada, no comprobamos
                    if ($checkout < $checkin) {
                        $twigVariables['checkout_error'] = "La fecha de salida no puede ser anterior a la de entrada";
                        $correct = false;
                    }
                }
            }
        }
    }

    if ($correct) {
        $twigVariables['confirmation'] = "readonly";
        $twigVariables['showFinding'] = true;
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
            $booking = new Booking();
            $booking->createBooking([
                'user_id' => 1, // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
                'room_id' => $twigVariables['room']['id'],
                'people_num' => $twigVariables['people_num'],
                'comments' => $twigVariables['comments'],
                'checkin' => $twigVariables['checkin'],
                'checkout' => $twigVariables['checkout'],
                'state' => 'pending',
                'timestamp' => date("Y-m-d H:i:s")
            ]);
            $twigVariables['booking_id'] = $booking;
        }
    }


}

var_dump($twigVariables['room']);
echo $twig->render('createBooking.twig', $twigVariables);