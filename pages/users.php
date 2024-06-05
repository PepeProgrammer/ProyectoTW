<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Users.php";
require_once "../models/Logs.php";

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['type'] === 'client') {
    header('Location: index.php');
    exit();
}

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$twigVariables = [];
$twigVariables['user'] = $_SESSION['user'];

$userDb = new Users();
$logs = new Logs();

if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
        $user = $userDb->getUser($_GET['delete']);
        if ($user and !($user['type'] !== 'client' and $_SESSION['user']['type'] === 'recepcionist')){
            if($userDb->deleteUser($_GET['delete'])){
                $logs->insertLog("Usuario eliminado. Id: " . $_GET['delete']);
                $_SESSION['success'] = "Usuario y todas sus reservas eliminadas correctamente. Id: ${$_GET['delete']}";
            } else {
                $_SESSION['error'] = "Error al eliminar el usuario";
            }

        }
}
if(isset($_SESSION['success'])){
    $twigVariables['message'] = $_SESSION['success'];
    unset($_SESSION['success']);
}


if($_SESSION['user']['type'] === 'admin'){
    $twigVariables['users'] = $userDb->getUsers();
} else {
    $twigVariables['users'] = $userDb->getUsersByType('client');
}

$asideInfo = new AsideInfo();
$twigVariables['aside'] = $asideInfo->getAsideInfo();


echo $twig->render('users.twig', $twigVariables);