<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Backup.php";
session_start();

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);


$asideInfo = new AsideInfo();
$twigVariables = [];
$twigVariables['aside'] = $asideInfo->getAsideInfo();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $backup = new Backup();
    if (isset($_POST['backup'])) {
        $sql = $backup->obtain();
        header("Content-Type: text/plain");
        header('Content-Disposition: attachment; filename="backup.sql"');
        echo $sql;
        return;
    } else {

        if (isset($_POST['delete'])) {
            $backup->delete();
        } elseif (isset($_POST['restore']) && is_uploaded_file($_FILES['sql']['tmp_name'])) {
            $backup->restore(@file_get_contents($_FILES['sql']['tmp_name']));
        }
        header("Location: {$_SERVER['SCRIPT_NAME']}", true, 303);
        exit();
    }

}


echo $twig->render('db.twig', $twigVariables);