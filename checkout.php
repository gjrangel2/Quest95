<?php
// Iniciar la sesión de PHP.
session_start();
// Incluir el archivo de configuración de la base de datos (aunque no se use directamente aquí, es buena práctica).
require_once 'config.php'; 

// Calcular el total del carrito para mostrarlo en la página de checkout.
$cart_total_amount = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_total_amount += $item['price'] * $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proceder al Pago</title>
    <link rel="stylesheet" href="css/normalize.css">
    <link href="https://fonts.googleapis.com/css2?family=Staatliches&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Estilos específicos para la página de checkout */
        .checkout-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .checkout-container h1 {
            color: #333;
            margin-bottom: 25px;
        }
        .checkout-container p {
            font-size: 1.1em;
            color: #555;
            line-height: 1.6;
        }
        .back-to-shop {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 25px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .back-to-shop:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="index.php">
            <img class="header__logo" src="img/logoquest.png" alt="Logotipo">
        </a>
    </header>

    <nav class="navegacion">
        <a class="navegacion__enlace" href="index.php">TIENDA</a>
        <a class="navegacion__enlace" href="nosotros.php">NOSOTROS</a>
    </nav>

    <main class="contenedor checkout-container">
        <h1>¡Gracias por tu compra!</h1>
        <p>Hemos recibido tu pedido y estamos procesándolo. Pronto recibirás un correo de confirmación con los detalles.</p>
        <p>El total de tu compra fue de: <strong>$<?php echo htmlspecialchars(number_format($cart_total_amount, 2)); ?></strong></p>
        <a href="index.php" class="back-to-shop">Volver a la Tienda</a>
        <?php
        // Opcional: Limpiar el carrito después de "checkout"
        unset($_SESSION['cart']); // Vacía el carrito después de "comprar"
        ?>
    </main>

    <footer>
        <p class="footer__texto"> QUEST LA CRUZ - Todos los derechos reservados 2024.</p>
    </footer>
</body>
</html>