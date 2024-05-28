<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Logs.php";

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);


$asideInfo = new AsideInfo();
$twigVariables = [];
$twigVariables['aside'] = $asideInfo->getAsideInfo();

$logs = new Logs();
$twigVariables['logs'] = $logs->getLogs();

echo $twig->render('logs.twig', $twigVariables);