<?php
// Define las constantes para la conexión a la base de datos MySQL.
// HOST: La dirección del servidor de la base de datos (generalmente 'localhost' si está en la misma máquina).
define('DB_HOST', 'localhost');
// USER: El nombre de usuario para acceder a la base de datos (por defecto 'root' en XAMPP/WAMP).
define('DB_USER', 'root');
// PASS: La contraseña para el usuario de la base de datos (por defecto vacía en XAMPP/WAMP).
define('DB_PASS', 'rangel1991');
// NAME: El nombre de la base de datos que creamos.
define('DB_NAME', 'quest_tienda');

// Intenta establecer una nueva conexión a la base de datos utilizando la extensión MySQLi.
// mysqli_connect() toma los parámetros de host, usuario, contraseña y nombre de la base de datos.
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Comprueba si la conexión a la base de datos fue exitosa.
// mysqli_connect_errno() devuelve el código de error de la última llamada a mysqli_connect().
// Si hay un error, el número de error será diferente de 0.
if (mysqli_connect_errno()) {
    // Si la conexión falla, muestra un mensaje de error y termina la ejecución del script.
    // mysqli_connect_error() devuelve una descripción del último error de conexión.
    die("Fallo en la conexión a la base de datos: " . mysqli_connect_error());
}

// Opcional: Establecer el conjunto de caracteres a UTF-8 para evitar problemas con tildes y caracteres especiales.
mysqli_set_charset($conn, "utf8");

// Nota: Esta conexión estará disponible en cualquier archivo que incluya 'config.php'.
// Es importante cerrar la conexión cuando ya no se necesite, aunque en scripts cortos PHP lo hace automáticamente al finalizar.
?>