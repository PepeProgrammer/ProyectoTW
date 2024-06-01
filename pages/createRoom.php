<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Room.php";
require_once "../models/Logs.php";
session_start();

if(!isset($_SESSION['user']) || $_SESSION['user']['type'] !== 'recepcionist') {
    header('Location: index.php');
    exit();
}
$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);


$asideInfo = new AsideInfo();
$twigVariables = [];
$errors = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['check'])) { //comprobamos si se han introducimos bien los datos para avisar al usuario en caso contrario
//        echo '<pre>';
//        var_dump($_FILES);
//        echo '</pre>';

        if (isset($_POST['number'])) {
            if ($_POST['number'] === "") {
                $twigVariables['number_error'] = "El número de habitación no puede estar vacío";
                $errors++;
            } else {
                $twigVariables['number'] = strip_tags($_POST['number']);
            }
        }

        if (isset($_POST['price'])) {
            if (intval($_POST['price']) === 0) {
                $twigVariables['price_error'] = "El precio debe ser un número";
                $errors++;
            } elseif($_POST['price'] < 0) {
                $twigVariables['price_error'] = "El precio no puede ser negativo";
                $errors++;
            } else {
                $twigVariables['price'] = strip_tags($_POST['price']);
            }
        }

        if (isset($_POST['capacity'])) {
            if (floatval($_POST['capacity']) === 0.0) {
                $twigVariables['capacity_error'] = "La capacidad debe ser un número";
                $errors++;
            } elseif($_POST['capacity'] < 0) {
                $twigVariables['capacity_error'] = "La capacidad debe ser mayor a 0";
                $errors++;
            } else {
                $twigVariables['capacity'] = strip_tags($_POST['capacity']);
            }
        }
        if (isset($_POST['description'])) {
            if ($_POST['description'] === "") {
                $twigVariables['description_error'] = "La descripción no puede estar vacía";
                $errors++;
            } else {
                $twigVariables['description'] = strip_tags($_POST['description']);
            }
        }

        if($errors === 0) { // Si no hay fallos ponemos los campos a readonly y avisamos al usuario de que los datos están bien
            $twigVariables['checked'] = true;
            $twigVariables['readonly'] = "readonly";
            $twigVariables['success'] = "Los datos se han validado correctamente";


        }
    } elseif(isset($_POST['create'])) { // Si se ha pulsado el botón de crear habitación
        if (isset($_POST['number']) && isset($_POST['price']) && isset($_POST['capacity']) && isset($_POST['description'])) {
            $roomDb = new Room();
            $data = [];
            $data['number'] = strip_tags($_POST['number']);
            $data['price'] = strip_tags($_POST['price']);
            $data['capacity'] = strip_tags($_POST['capacity']);
            $data['description'] = strip_tags($_POST['description']);
            $room_id = $roomDb->insertRoom($data);
            $images = [];
            if($_FILES['images']['size'][0] > 0){ //Si se han añadido imágenes las insertamos
                foreach ($_FILES['images']['tmp_name'] as $image) {
                    $img = base64_encode(file_get_contents($image));
                    $img_id = $roomDb->insertImage($img);
                    $roomDb->insertRoomImages($room_id, $img_id);
                }
            }
            $logDb = new Logs();
            $logDb->insertLog( "Se ha creado la habitación con id: $room_id");
            $_SESSION['success'] = "La habitación se ha creado correctamente";
        }
    }
}

$twigVariables['aside'] = $asideInfo->getAsideInfo();

echo $twig->render('createRoom.twig', $twigVariables);