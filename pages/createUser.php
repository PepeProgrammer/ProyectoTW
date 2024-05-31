<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Users.php";
require_once "../models/Logs.php";

session_start();

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

$twigVariables = [];
if (isset($_SESSION['user'])) {
    $twigVariables['user'] = $_SESSION['user'];
}

$asideInfo = new AsideInfo();


if (isset($_SESSION['user']) && $_SESSION['user']['type'] === 'client' && !isset($_GET['profile']) && !isset($_SESSION['update'])) { //solo no redireccionamos si el cliente quiere ver su perfil
    header('Location: index.php');
    exit();
}

$logs = new Logs();

$twigVariables['aside'] = $asideInfo->getAsideInfo();
$twigVariables['title'] = 'Registro de usuarios';
$name_error = ""; // no puede estar vacío
$sname_error = "";    // no puede estar vacío
$DNI_error = "";    // no puede estar vacío, DNI español, letra correcta (8 digitos+letra)
$email_error = "";  // no puede estar vacío y dirección válida
$password_error = "";  // no puede estar vacío y deben coincidir. +3 caracteres
$card_error = ""; // no puede estar vacío y algoritmo de Luhn
$correct = false;

$name = "";
$sname = "";
$DNI = "";
$email = "";
$password = "";
$password2 = "";
$card = "";
$confirmation = "";
$types = ['client', 'recepcionist', 'admin'];

if (!isset($_SESSION['update'])) {
    $button_text = "Enviar datos";
} else {
    $button_text = 'Modificar datos';
}

$userDb = new Users();
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_SESSION['user']) && $_SESSION['user']['type'] !== "client") {
    if (isset($_GET['id'])) {
        $user = $userDb->getUser($_GET['id']);
        if ($user and !($user['type'] !== 'client' and $_SESSION['user']['type'] === 'recepcionist')) { //Con esto evitamos que un recepcionsta pueda modificar a un administrador por la fuerza
            $name = $user['name'];
            $sname = $user['lastname'];
            $DNI = $user['dni'];
            $email = $user['email'];
            $card = $user['card'];
            $twigVariables['title'] = 'Modificación de usuario';
            $twigVariables['type'] = $user['type'];
            $button_text = 'Modificar datos';
            $_SESSION['update'] = $_GET['id']; // para saber que estamos actualizando un usuario
        }

    }
}


if (isset($_GET['profile'])) {

    $user = $userDb->getUser($_SESSION['user']['id']);
    $name = $user['name'];
    $sname = $user['lastname'];
    $DNI = $user['dni'];
    $email = $user['email'];
    $card = $user['card'];
    $twigVariables['title'] = 'Perfil de usuario';
    $button_text = 'Modificar datos';
    $confirmation = "readonly";
    $_SESSION['update'] = $twigVariables['auto_update'] = $_SESSION['user']['id'];
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['send'])) {
        $correct = true;

        if (isset($_POST['name'])) {
            $name = strip_tags($_POST['name']);
            if ($name === "") {
                $name_error = "El nombre no puede estar vacío";
                $correct = false;
            }
        }

        if (isset($_POST['sname'])) {
            $sname = strip_tags($_POST['sname']);
            if ($sname === "") {
                $sname_error = "Los apellidos no pueden estar vacío";
                $correct = false;
            }
        }

        if (isset($_POST['DNI'])) {
            $DNI = strip_tags($_POST['DNI']);
            if ($DNI === "") {
                $DNI_error = "El DNI no puede estar vacío";
                $correct = false;
            } else {
                $letter = substr($DNI, -1);
                $numbers = substr($DNI, 0, -1);
                if (!is_numeric($numbers) || !ctype_alpha($letter) || strlen($DNI) != 9) {
                    $DNI_error = "El DNI no es válido";
                    $correct = false;
                } else {
                    $correct_letter = substr("TRWAGMYFPDXBNJZSQVHLCKE", $numbers % 23, 1);
                    if ($correct_letter !== $letter) {
                        $DNI_error = "La letra del DNI no es correcta";
                        $correct = false;
                    }
                }
            }
        }

        if (isset($_POST['email'])) {
            $email = strip_tags($_POST['email']);
            if ($email === "") {
                $email_error = "Debe indicar un email de contacto";
                $correct = false;
            } else {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $email_error = "El email no es válido";
                    $correct = false;
                }
            }
        }
        if (isset($_POST['password']) && isset($_POST['password2'])) {
            $password = $_POST['password'];
            $password2 = strip_tags($_POST['password2']);

            if (!isset($_SESSION['update'])) { //En caso de que estemos actualizando podemos dejar la contraseña vacía
                $password = strip_tags($_POST['password']);
                if ($password === "") {
                    $password_error = "La clave no puede estar vacía";
                    $correct = false;
                } elseif ($password !== $password2) {
                    $password_error = "Deben coincidir ambas claves";
                    $correct = false;
                } elseif (strlen($password) < 3) {
                    $password_error = "La clave debe tener al menos 3 caracteres";
                    $correct = false;
                }
            } else {
                if ($password !== $password2) {
                    $password_error = "Deben coincidir ambas claves";
                    $correct = false;
                } elseif (strlen($password) > 0 && strlen($password) < 3) {
                    $password_error = "La clave debe tener al menos 3 caracteres";
                    $correct = false;
                }
            }

        }

        if (isset($_POST['card'])) {
            $card = strip_tags($_POST['card']);
            if ($card === "") {
                $card_error = "Debe indicar un número de tarjeta";
                $correct = false;
            } else {
                $card = str_replace(" ", "", $card); // por si alguien inserta espacios en blanco
                if (!ctype_digit($card)) {
                    $card_error = "El número de tarjeta no es válido";
                    $correct = false;
                } else {
                    $sum = 0;
                    $length = strlen($card);
                    if ($length === 16) {
                        for ($i = 0; $i < $length; $i++) {
                            $digit = $card[$length - $i - 1];
                            if ($i % 2 == 1) {
                                $digit *= 2;
                                if ($digit > 9) {
                                    $digit = 1 + ($digit % 10);
                                }
                            }
                            $sum += $digit;
                        }
                        if ($sum % 10 != 0) {
                            $card_error = "El número de tarjeta no es válido";
                            $correct = false;
                        }
                    } else {
                        $card_error = "El número de tarjeta no es válido";
                        $correct = false;
                    }

                }
            }
        }

        if (isset($_POST['type'])) {
            if (!in_array($_POST['type'], $types)) {
                $correct = false;
                $twigVariables['type_error'] = "El tipo de usuario no es válido";
            } else {
                $twigVariables['type_hidden'] = $_POST['type'];
                $twigVariables['type'] = $_POST['type'];

            }
        }
    }


    if ($correct) {
        $confirmation = "readonly";
        $button_text = "Confirmar datos";
        $twigVariables['correct_text'] = "Se han introducido los datos correctamente. Pulse el botón para confirmar";
    }

    if ($_POST['send'] === "Confirmar datos") {

        $data['name'] = $name;
        $data['lastname'] = $sname;
        $data['dni'] = $DNI;
        $data['email'] = $email;
        $data['card'] = $card;
        if (isset($_POST['type'])) {
            $data['type'] = $_POST['type'];
        } else
            $data['type'] = "client";

        $userDb = new Users();
        if (!isset($_SESSION['update'])) {
            $data['pass'] = password_hash($password, PASSWORD_DEFAULT);
            if (!$userDb->createUser($data)) {
                $twigVariables['error'] = "Error al insertar el usuario en la base de datos";
                $confirmation = "";
            } else {
                if (isset($_SESSION['user'])) {
                    $logs->insertLog("Nuevo usuario creado por " . $_SESSION['user']['id'] . ". Email: " . $email);
                    $_SESSION['success'] = "Usuario creado correctamente";
                    header('Location: users.php');
                } else {
                    $logs->insertLog("Nuevo usuario creado. Email: " . $email);
                    header('Location: index.php');
                }
                exit;
            }
        } else {

            if ($_POST['password'] === "") {
                $data['pass'] = "";
            } else {
                $data['pass'] = password_hash($password, PASSWORD_DEFAULT);
            }

            if (isset($_POST['type'])) {
                $data['type'] = $_POST['type'];
            }

            if (!$userDb->updateUser($data, $_SESSION['update'])) {
                $twigVariables['error'] = "Error al actualizar el usuario en la base de datos";
                $confirmation = "";
            } else {
                $logs->insertLog("Usuario modificado. Id: " . $_SESSION['update']);
                $_SESSION['success'] = "Usuario modificado correctamente";
                unset($_SESSION['update']);
                if ($_SESSION['user']['type'] !== 'client') {
                    header('Location: users.php');
                } else {
                    header('Location: profile.php');
                }
                exit;
            }
        }


    }

}

// insertamos en $twigVariables todas las variables definidas
$twigVariables['name'] = $name;
$twigVariables['sname'] = $sname;
$twigVariables['DNI'] = $DNI;
$twigVariables['email'] = $email;
$twigVariables['card'] = $card;
$twigVariables['password'] = $password;
$twigVariables['password2'] = $password2;
$twigVariables['name_error'] = $name_error;
$twigVariables['sname_error'] = $sname_error;
$twigVariables['DNI_error'] = $DNI_error;
$twigVariables['email_error'] = $email_error;
$twigVariables['password_error'] = $password_error;
$twigVariables['card_error'] = $card_error;
$twigVariables['correct'] = $correct;
$twigVariables['confirmation'] = $confirmation;
$twigVariables['button_text'] = $button_text;


echo $twig->render('createUser.twig', $twigVariables);