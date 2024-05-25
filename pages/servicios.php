<?php

require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);


$asideInfo = new AsideInfo();
$twigVariables = [];
$twigVariables['aside'] = $asideInfo->getAsideInfo();


echo $twig->render('servicios.twig', $twigVariables);