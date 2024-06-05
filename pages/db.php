<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Backup.php";
session_start();

if(!isset($_SESSION['user']) || $_SESSION['user']['type'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);


$asideInfo = new AsideInfo();
$twigVariables = [];

$twigVariables['user'] = $_SESSION['user'];


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
            $backup->delete($_SESSION['user']['id']);
            $_SESSION['message'] = 'Datos eliminados correctamente';
        } elseif (isset($_POST['restore']) && is_uploaded_file($_FILES['sql']['tmp_name'])) {

            if(explode('.',$_FILES['sql']['name'])[1] === 'sql') {
                $backup->delete($_SESSION['user']['id']);
                $backup->restore(@file_get_contents($_FILES['sql']['tmp_name']));
                $_SESSION['message'] = 'Datos restaurados correctamente';
            } else {
                $_SESSION['message'] = 'El archivo no es un archivo SQL';
            }

        }
        header("Location: {$_SERVER['SCRIPT_NAME']}", true, 303);
        exit();
    }

}

if(isset($_SESSION['message'])) {
    $twigVariables['message'] = $_SESSION['message'];
    unset($_SESSION['message']);
}


echo $twig->render('db.twig', $twigVariables);