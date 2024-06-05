<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Rooms.php";
require_once "../models/Logs.php";
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['type'] !== 'recepcionist') {
    header('Location: index.php');
    exit();
}
$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);


$asideInfo = new AsideInfo();
$twigVariables = [];
$twigVariables['user'] = $_SESSION['user'];
$errors = 0;

$roomDb = new Rooms();
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
                if($roomDb->getRoomByNum($_POST['number']) === null || isset($_POST['confirm_update'])){ //Si la habitación se está creando comprobamos si ya existe una con ese nombre
                    $twigVariables['number'] = strip_tags($_POST['number']);
                } else {
                    $twigVariables['number_error'] = "Ya hay una habitación con ese número";
                    $errors++;

                }
            }
        }

        if (isset($_POST['price'])) {
            if (intval($_POST['price']) === 0) {
                $twigVariables['price_error'] = "El precio debe ser un número";
                $errors++;
            } elseif ($_POST['price'] < 0) {
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
            } elseif ($_POST['capacity'] < 0) {
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

        if ($errors === 0) { // Si no hay fallos ponemos los campos a readonly y avisamos al usuario de que los datos están bien
            $twigVariables['checked'] = true;
            $twigVariables['readonly'] = "readonly";
            $twigVariables['success'] = "Los datos se han validado correctamente";


        }

        if (isset($_POST['confirm_update'])) { // Si se está actualizando la habitación
            $twigVariables['updating'] = true;
            $twigVariables['id'] = $_POST['id'];
            $images = $roomDb->getRoomImages($_POST['id']);
            if(isset($_POST['deleted_images'])) {
                foreach ($images as $image) {
                    if (!in_array($image['id'], $_POST['deleted_images'])) { //Si la imagen no la vamos a borrar la mostramos en la confirmación de los datos
                        $twigVariables['images'][] = $image;
                    } else {
                        $_SESSION['deleted_images'][] = $image['id']; //ids de las imágenes a desvincular de una habitación
                    }
                }
            } else {
                $twigVariables['images'] = $images;

            }

        }

    } elseif (isset($_POST['create']) || isset($_POST['update'])) { // Si se ha pulsado el botón de crear habitación
        if (isset($_POST['number']) && isset($_POST['price']) && isset($_POST['capacity']) && isset($_POST['description'])) {
            $data = [];
            $data['number'] = strip_tags($_POST['number']);
            $data['price'] = floatval(strip_tags($_POST['price']));
            $data['capacity'] = intval(strip_tags($_POST['capacity']));
            $data['description'] = strip_tags($_POST['description']);
            if (isset($_POST['create'])) {
                $room_id = $roomDb->insertRoom($data);
            } else {
                $room_id = intval($_POST['id']);
                $roomDb->updateRoom($data, $room_id);
                if(isset($_SESSION['deleted_images'])){
                    $roomDb->deleteRoomImages($room_id, $_SESSION['deleted_images']);
                    unset($_SESSION['deleted_images']);
                }
            }

            $images = [];
            if ($_FILES['images']['size'][0] > 0) { //Si se han añadido imágenes las insertamos
                foreach ($_FILES['images']['tmp_name'] as $image) {
                    $img = base64_encode(file_get_contents($image));
                    $img_id = $roomDb->insertImage($img);
                    $roomDb->insertRoomImages($room_id, $img_id);
                }
            }
            $logDb = new Logs();
            if (isset($_POST['create'])) {
                $logDb->insertLog("Se ha creado la habitación con id: $room_id");
                $_SESSION['success'] = "La habitación se ha creado correctamente";
            } else {
                $logDb->insertLog("Se ha actualizado la habitación con id: $room_id");
                $_SESSION['success'] = "La habitación se ha actualizado correctamente";
            }

            header('Location: rooms.php'); // Si la habitación ha sido creada correctamente se vuelve a la página de habitaciones
        }
    }
}

if (isset($_GET['id'])) {

    $room = $roomDb->getRoomById($_GET['id']);
    $twigVariables['id'] = $room['id'];
    $twigVariables['number'] = $room['room_num'];
    $twigVariables['price'] = $room['price'];
    $twigVariables['capacity'] = $room['capacity'];
    $twigVariables['description'] = $room['description'];
    $twigVariables['images'] = $roomDb->getRoomImages($_GET['id']);
    $twigVariables['updating'] = true;
}

$twigVariables['aside'] = $asideInfo->getAsideInfo();

echo $twig->render('room_data.twig', $twigVariables);