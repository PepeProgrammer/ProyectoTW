<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../controllers/Database.php';
$db = Database::getInstance();
$prepare = $db->prepare("SELECT * FROM users");
$prepare->execute();
$usuarios = $prepare->get_result()->fetch_all();

var_dump($usuarios);
