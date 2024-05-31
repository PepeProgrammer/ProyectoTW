<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Logs.php";
require_once "../models/Users.php";

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

session_start();
$twigVariables = [];

if($_SERVER['REQUEST_METHOD'] == 'POST') {


    $logs = new Logs();
    $userDb = new Users();
    $user = $userDb->getUserByEmail($_POST['user']);
    if($user && password_verify($_POST['password'], $user['pass'])) {
        $_SESSION['user'] = $user;
        unset($_SESSION['user']['pass']); //No queremos guardar la contraseña en la sesión
        $logs->insertLog('Usario inició sesión. Email: ' . $user['email']);
        header('Location: index.php');
        exit();
    } else {
        $twigVariables['email'] = $_POST['user'];
        $twigVariables['error'] = 'Usuario o contraseña incorrectos';
        $logs->insertLog("Intento de inicio de sesión fallido. Email: {$_POST['user']}");
    }
}
$asideInfo = new AsideInfo();

$twigVariables['aside'] = $asideInfo->getAsideInfo();


echo $twig->render('login.twig', $twigVariables);