
<?php
// Define las constantes para la conexión a la base de datos PostgreSQL.
// DB_HOST: La dirección del servidor de la base de datos (generalmente 'localhost').
define('DB_HOST', 'localhost');
// DB_PORT: El puerto por defecto de PostgreSQL es 5432.
define('DB_PORT', '5432');
// DB_USER: El nombre de usuario para acceder a la base de datos.
define('DB_USER', 'postgres'); // ¡CAMBIA ESTO!
// DB_PASS: La contraseña para el usuario de la base de datos.
define('DB_PASS', 'rangel1991'); // ¡CAMBIA ESTO!
// DB_NAME: El nombre de la base de datos que creamos en PostgreSQL.
define('DB_NAME', 'quest_tienda');

// Variable para almacenar la conexión PDO.
$pdo = null;

try {
    // Cadena de conexión (DSN) para PostgreSQL usando PDO.
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;

    // Crea una nueva instancia de PDO.
    // PDO es más flexible ya que permite usar el mismo código con diferentes bases de datos
    // cambiando solo el DSN y el driver.
    $pdo = new PDO($dsn, DB_USER, DB_PASS);

    // Configura el modo de error de PDO para lanzar excepciones.
    // Esto es muy útil para depuración y manejo de errores.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Opcional: Establecer el conjunto de caracteres a UTF-8.
    // Esto se puede hacer en el DSN o con una consulta.
    $pdo->exec("SET NAMES 'UTF8'");
    $pdo->exec("SET client_encoding TO 'UTF8'");

    // Si la conexión es exitosa, puedes imprimir un mensaje (solo para depuración).
    // echo "Conexión a PostgreSQL exitosa!";

} catch (PDOException $e) {
    // Si la conexión falla, muestra un mensaje de error y termina la ejecución del script.
    // $e->getMessage() proporciona el mensaje de error de la excepción.
    die("Fallo en la conexión a la base de datos PostgreSQL: " . $e->getMessage());
}

// La variable $pdo (la conexión a la base de datos) estará disponible en cualquier
// archivo que incluya 'config.php'.

// Nota: Con PDO, no necesitas cerrar explícitamente la conexión;
// se cerrará automáticamente cuando el script termine.
?>

