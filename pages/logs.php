<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Logs.php";

session_start();

if(!isset($_SESSION['user']) || $_SESSION['user']['type'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);
$twigVariables = [];
$twigVariables['user'] = $_SESSION['user'];

$asideInfo = new AsideInfo();

$twigVariables['aside'] = $asideInfo->getAsideInfo();

$logs = new Logs();
$twigVariables['logs'] = $logs->getLogs();

echo $twig->render('logs.twig', $twigVariables);