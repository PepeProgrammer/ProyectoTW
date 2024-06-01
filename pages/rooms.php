<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Room.php";
session_start();

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);


$asideInfo = new AsideInfo();
$roomDb = new Room();
$twigVariables = [];
$twigVariables['aside'] = $asideInfo->getAsideInfo();

$twigVariables['rooms'] = $roomDb->getRooms();
foreach ($twigVariables['rooms'] as $key => $room) {
    $twigVariables['rooms'][$key]['images'] = $roomDb->getRoomImages($room['id']);
}
//echo '<pre>';
//var_dump($twigVariables['rooms']);
//echo '</pre>';
//exit();
echo $twig->render('rooms.twig', $twigVariables);