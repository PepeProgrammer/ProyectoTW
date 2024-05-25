<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";
require_once "../models/Users.php";

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);


$asideInfo = new AsideInfo();
$twigVariables = [];
$twigVariables['aside'] = $asideInfo->getAsideInfo();

$name_error = ""; // no puede estar vacío
$sname_error = "";    // no puede estar vacío
$DNI_error = "";    // no puede estar vacío, DNI español, letra correcta (8 digitos+letra)
$email_error = "";  // no puede estar vacío y dirección valida
$password_error = "";  // no puede estar vacío y deben coincidir. +5 caracteres
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
$button_text = "Enviar datos";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['send'])) {
        $correct = true;

        if (isset($_POST['name'])) {
            $name = strip_tags($_POST['name']);
            $name = htmlentities($name, ENT_QUOTES, 'UTF-8');
            if ($name === "") {
                $name_error = "El nombre no puede estar vacío";
                $correct = false;
            }
        }

        if (isset($_POST['sname'])) {
            $sname = strip_tags($_POST['sname']);
            $sname = htmlentities($sname, ENT_QUOTES, 'UTF-8');
            if ($sname === "") {
                $sname_error = "Los apellidos no pueden estar vacío";
                $correct = false;
            }
        }

        if (isset($_POST['DNI'])) {
            $DNI = strip_tags($_POST['DNI']);
            $DNI = htmlentities($DNI, ENT_QUOTES, 'UTF-8');
            if ($DNI === "") {
                $DNI_error = "El DNI no puede estar vacío";
                $correct = false;
            } else {
                $letter = substr($DNI, -1);
                $numbers = substr($DNI, 0, -1);
                if (!is_numeric($numbers) || !ctype_alpha($letter) || strlen($DNI) != 9) {
                    $DNI_error = "El DNI no es válido";
                    $correct = false;
                }else{
                    $correct_letter = substr("TRWAGMYFPDXBNJZSQVHLCKE", $numbers % 23, 1);
                    if($correct_letter !== $letter) {
                        $DNI_error = "La letra del DNI no es correcta";
                        $correct = false;
                    }
                }
            }
        }

        if (isset($_POST['email'])) {
            $email = strip_tags($_POST['email']);
            $email = htmlentities($email, ENT_QUOTES, 'UTF-8');
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
            $password = strip_tags($_POST['password']);
            $password = htmlentities($password, ENT_QUOTES, 'UTF-8');
            $password2 = strip_tags($_POST['password2']);
            $password2 = htmlentities($password2, ENT_QUOTES, 'UTF-8');
            if ($password === "") {
                $password_error = "La clave no puede estar vacía";
                $correct = false;
            } elseif ($password !== $password2) {
                $password_error = "Deben coincidir ambas claves";
                $correct = false;
            } elseif (strlen($password) < 5) {
                $password_error = "La clave debe tener al menos 5 caracteres";
                $correct = false;
            }
        }

        if (isset($_POST['card'])) {
            $card = strip_tags($_POST['card']);
            $card = htmlentities($card, ENT_QUOTES, 'UTF-8');
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
                    if ($length === 16){
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
    }



    if ($correct) {
        $confirmation = "readonly";
        $button_text = "Confirmar datos";
    }

    if ($_POST['send'] === "Confirmar datos") {

        $data['name'] = $name;
        $data['lastname'] = $sname;
        $data['dni'] = $DNI;
        $data['email'] = $email;
        $data['pass'] = password_hash($password, PASSWORD_DEFAULT);
        $data['card'] = $card;
        $data['type'] = "client";

        // insertamos en la base de datos
        $userDb = new Users();
        $userDb->createUser($data);
        header('Location: index.php');
        exit;
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


echo $twig->render('registro.twig', $twigVariables);