<?php
require_once "../vendor/autoload.php";
require_once "../models/AsideInfo.php";

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);


$asideInfo = new AsideInfo();
$twigVariables = [];
$twigVariables['aside'] = $asideInfo->getAsideInfo();

$error_nombre = ""; // no puede estar vacío
$error_ape = "";    // no puede estar vacío
$error_DNI = "";    // no puede estar vacío, DNI español, letra correcta (8 digitos+letra)
$error_email = "";  // no puede estar vacío y dirección valida
$error_clave = "";  // no puede estar vacío y deben coincidir. +5 caracteres
$error_tarjeta = ""; // no puede estar vacío y algoritmo de Luhn
$envio_correcto = false;

$nombre = "";
$ape = "";
$DNI = "";
$email = "";
$clave = "";
$clave2 = "";
$confirmacion = "";
$texto_boton = "Enviar datos";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['enviar'])) {
        $envio_correcto = true;

        if (isset($_POST['nombre'])) {
            $nombre = strip_tags($_POST['nombre']);
            $nombre = htmlentities($nombre, ENT_QUOTES, 'UTF-8');
            if ($nombre === "") {
                $error_nombre = "<span class='error'>El nombre no puede estar vacío</span>";
                $envio_correcto = false;
            }
        }

        if (isset($_POST['ape'])) {
            $ape = strip_tags($_POST['ape']);
            $ape = htmlentities($ape, ENT_QUOTES, 'UTF-8');
            if ($ape === "") {
                $error_ape = "<span class='error'>Los apellidos no pueden estar vacío</span>";
                $envio_correcto = false;
            }
        }

        if (isset($_POST['DNI'])) {
            $DNI = strip_tags($_POST['DNI']);
            $DNI = htmlentities($DNI, ENT_QUOTES, 'UTF-8');
            if ($DNI === "") {
                $error_DNI = "<span class='error'>El DNI no puede estar vacío</span>";
                $envio_correcto = false;
            } else {
                $letra = substr($DNI, -1);
                $numeros = substr($DNI, 0, -1);
                if (!is_numeric($numeros) || !ctype_alpha($letra) || strlen($DNI) != 9) {
                    $error_DNI = "<span class='error'>El DNI no es válido</span>";
                    $envio_correcto = false;
                }else{
                    $letra_correcta = substr("TRWAGMYFPDXBNJZSQVHLCKE", $numeros % 23, 1);
                    if($letra_correcta !== $letra) {
                        $error_DNI = "<span class='error'>La letra del DNI no es correcta</span>";
                        $envio_correcto = false;
                    }
                }
            }
        }

        if (isset($_POST['email'])) {
            $email = strip_tags($_POST['email']);
            $email = htmlentities($email, ENT_QUOTES, 'UTF-8');
            if ($email === "") {
                $error_email = "<span class='error'>Debe indicar un email de contacto</span>";
                $envio_correcto = false;
            } else {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error_email = "<span class='error'>El email no es válido</span>";
                    $envio_correcto = false;
                }
            }
        }

        if (isset($_POST['clave']) && isset($_POST['clave2'])) {
            $clave = strip_tags($_POST['clave']);
            $clave = htmlentities($clave, ENT_QUOTES, 'UTF-8');
            $clave2 = strip_tags($_POST['clave2']);
            $clave2 = htmlentities($clave2, ENT_QUOTES, 'UTF-8');
            if ($clave === "") {
                $error_clave = "<span class='error'>La clave no puede estar vacía</span>";
                $envio_correcto = false;
            } elseif ($clave !== $clave2) {
                $error_clave = "<span class='error'>Deben coincidir ambas claves</span>";
                $envio_correcto = false;
            } elseif (strlen($clave) < 5) {
                $error_clave = "<span class='error'>La clave debe tener al menos 5 caracteres</span>";
                $envio_correcto = false;
            }
        }

        if (isset($_POST['tarjeta'])) {
            $tarjeta = strip_tags($_POST['tarjeta']);
            $tarjeta = htmlentities($tarjeta, ENT_QUOTES, 'UTF-8');
            if ($tarjeta === "") {
                $error_tarjeta = "<span class='error'>Debe indicar un número de tarjeta</span>";
                $envio_correcto = false;
            } else {
                $tarjeta = str_replace(" ", "", $tarjeta); // por si alguien inserta espacios en blanco
                if (!ctype_digit($tarjeta)) {
                    $error_tarjeta = "<span class='error'>El número de tarjeta no es válido</span>";
                    $envio_correcto = false;
                } else {
                    $suma = 0;
                    $longitud = strlen($tarjeta);
                    if ($longitud === 16){
                        for ($i = 0; $i < $longitud; $i++) {
                            $digito = $tarjeta[$longitud - $i - 1];
                            if ($i % 2 == 1) {
                                $digito *= 2;
                                if ($digito > 9) {
                                    $digito = 1 + ($digito % 10);
                                }
                            }
                            $suma += $digito;
                        }
                        if ($suma % 10 != 0) {
                            $error_tarjeta = "<span class='error'>El número de tarjeta no es válido</span>";
                            $envio_correcto = false;
                        }
                    } else {
                        $error_tarjeta = "<span class='error'>El número de tarjeta no es válido</span>";
                        $envio_correcto = false;
                    }

                }
            }
        }
    }

    if ($envio_correcto) {
        $confirmacion = "readonly";
        $texto_boton = "Confirmar datos";
    }
}

// insertamos en $twigVariables todas las variables definidas
$twigVariables['nombre'] = $nombre;
$twigVariables['ape'] = $ape;
$twigVariables['DNI'] = $DNI;
$twigVariables['email'] = $email;
$twigVariables['clave'] = $clave;
$twigVariables['clave2'] = $clave2;
$twigVariables['error_nombre'] = $error_nombre;
$twigVariables['error_ape'] = $error_ape;
$twigVariables['error_DNI'] = $error_DNI;
$twigVariables['error_email'] = $error_email;
$twigVariables['error_clave'] = $error_clave;
$twigVariables['error_tarjeta'] = $error_tarjeta;
$twigVariables['envio_correcto'] = $envio_correcto;
$twigVariables['confirmacion'] = $confirmacion;
$twigVariables['texto_boton'] = $texto_boton;


echo $twig->render('registro.twig', $twigVariables);