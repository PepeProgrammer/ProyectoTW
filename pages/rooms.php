<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Rooms.php";
require_once "../models/Logs.php";

session_start();

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);


$asideInfo = new AsideInfo();
$roomDb = new Rooms();
$logs = new Logs();

$twigVariables = [];
$twigVariables['aside'] = $asideInfo->getAsideInfo();


if(isset($_SESSION['user'])) {
    $twigVariables['user'] = $_SESSION['user'];

}

if(isset($_POST['delete'])) {
    if($roomDb->deleteRoom($_POST['delete']) !== false) {
        $twigVariables['success'] = "Habitación eliminada correctamente";
        $logs->insertLog("Habitación y todas sus reservas eliminadas. Id: " . $_POST['delete']);
    } else {
        $twigVariables['error'] = "Error al eliminar la habitación";
    }
}

$twigVariables['rooms'] = $roomDb->getRooms();
foreach ($twigVariables['rooms'] as $key => $room) {
    $twigVariables['rooms'][$key]['images'] = $roomDb->getRoomImages($room['id']);
}

if(isset($_SESSION['success'])) {
    $twigVariables['success'] = $_SESSION['success'];
    unset($_SESSION['success']);
}
echo $twig->render('rooms.twig', $twigVariables);