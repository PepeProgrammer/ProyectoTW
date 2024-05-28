<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Users.php";

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['type'] === 'client') {
    header('Location: index.php');
    exit();
}

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$twigVariables = [];
$twigVariables['user'] = $_SESSION['user'];

if(isset($_SESSION['success'])){
    $twigVariables['message'] = $_SESSION['success'];
    unset($_SESSION['success']);
}
$user = new Users();

if($_SESSION['user']['type'] === 'admin'){
    $twigVariables['users'] = $user->getUsers();
} else {
    $twigVariables['users'] = $user->getUsersByType('client');
}

$asideInfo = new AsideInfo();
$twigVariables['aside'] = $asideInfo->getAsideInfo();


echo $twig->render('users.twig', $twigVariables);