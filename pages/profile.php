<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Users.php";

session_start();

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$userDb = new Users();
$asideInfo = new AsideInfo();
$twigVariables = [];
$twigVariables['aside'] = $asideInfo->getAsideInfo();
$twigVariables['user'] = $userDb->getUser($_SESSION['user']['id']);

if(isset($_SESSION['success'])){
    $twigVariables['success'] = $_SESSION['success'];
    unset($_SESSION['success']);

}
echo $twig->render('profile.twig', $twigVariables);
