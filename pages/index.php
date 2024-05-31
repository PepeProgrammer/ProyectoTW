<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";

session_start();

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);


$asideInfo = new AsideInfo();
$twigVariables = [];

if(isset($_SESSION['user'])){
    $twigVariables['user'] = $_SESSION['user'];
}

$twigVariables['aside'] = $asideInfo->getAsideInfo();


echo $twig->render('index.twig', $twigVariables);