<?php
session_start();

require_once "../models/Logs.php";

$logs = new Logs();
$logs->insertLog('Usuario cerró sesión. Id: ' . $_SESSION['user']['id']);

session_destroy();

header("Location: index.php");

exit();
?>