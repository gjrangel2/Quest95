<?php
// Iniciar la sesión de PHP al principio de cualquier script que la use.
session_start();

// Incluir el archivo de configuración de la base de datos.
// Esto nos dará la variable $conn que es nuestra conexión a MySQL.
require_once 'config.php';

// Si el carrito no existe en la sesión, inicialízalo como un array vacío.
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// --- Lógica para eliminar productos del carrito (si se añade un botón para ello) ---
// Comprueba si se envió una solicitud POST y si el botón 'remove_from_cart' fue presionado.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    // Obtiene la clave del ítem del carrito (id_talla) que se quiere eliminar.
    $item_key_to_remove = $_POST['item_key']; // Ahora usamos la clave completa del ítem
    
    // Verifica si el ítem existe en el carrito antes de intentar eliminarlo.
    if (isset($_SESSION['cart'][$item_key_to_remove])) {
        unset($_SESSION['cart'][$item_key_to_remove]); // Elimina el elemento del carrito.
    }
    // Redireccionar para evitar el reenvío del formulario al recargar la página (PRG pattern).
    header('Location: index.php');
    exit(); // Termina la ejecución del script después de la redirección.
}

// --- Obtener productos de la base de datos ---
// Define la consulta SQL para seleccionar todos los productos de la tabla 'products'.
$sql = "SELECT id, name, price, image FROM products";
// Ejecuta la consulta SQL en la base de datos usando la conexión $conn.
$result = mysqli_query($conn, $sql);

// Inicializa un array para almacenar los productos que se obtendrán de la base de datos.
$products = [];
// Comprueba si la consulta devolvió resultados y si hay filas.
if (mysqli_num_rows($result) > 0) {
    // Itera sobre cada fila de resultados y las almacena en el array $products.
    // mysqli_fetch_assoc() recupera una fila de resultados como un array asociativo.
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}
// Libera la memoria asociada al resultado de la consulta.
mysqli_free_result($result);

// La conexión a la BD se cierra implícitamente al final del script si no se cierra manualmente.
// mysqli_close($conn); // Puedes descomentar esto si quieres cerrarla explícitamente aquí.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUEST LA CRUZ</title>
    <link rel="stylesheet" href="css/normalize.css">
    <link href="https://fonts.googleapis.com/css2?family=Staatliches&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Estilos adicionales para el resumen del carrito, si no están en styles.css */
        .cart-summary {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-top: 40px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .cart-summary h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .cart-item-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .cart-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #e9ecef;
        }
        .cart-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .cart-item__image {
            width: 70px;
            height: 70px;
            border-radius: 5px;
            object-fit: cover;
            margin-right: 15px;
            border: 1px solid #ddd;
        }
        .cart-item__details {
            flex-grow: 1;
        }
        .cart-item__name {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .cart-item__info {
            font-size: 0.9em;
            color: #666;
        }
        .cart-item__remove-form {
            margin-left: 15px;
        }
        .cart-item__remove-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85em;
            transition: background-color 0.3s ease;
        }
        .cart-item__remove-btn:hover {
            background-color: #c82333;
        }
        .cart-total {
            text-align: right;
            font-size: 1.3em;
            font-weight: bold;
            color: #28a745;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #28a745;
        }
        .empty-cart-message {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 20px;
            border: 1px dashed #adb5bd;
            border-radius: 5px;
        }
        .checkout-button-container {
            text-align: center;
            margin-top: 20px;
        }
        .checkout-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .checkout-button:hover {
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
        <a class="navegacion__enlace navegacion__enlace--activo" href="index.php">TIENDA</a>
        <a class="navegacion__enlace" href="nosotros.php">NOSOTROS</a>
    </nav>

    <main class="contenedor">
        <h1>NUESTROS PRODUCTOS</h1>

        <div class="grid">
            <?php
            // Bucle PHP para iterar sobre el array de productos obtenido de la base de datos.
            foreach ($products as $product):
            ?>
                <div class="producto">
                    <a href="producto.php?id=<?php echo htmlspecialchars($product['id']); ?>">
                        <img class="producto__imagen" src="<?php echo htmlspecialchars($product['image']); ?>" alt="imagen <?php echo htmlspecialchars($product['name']); ?>">
                        <div class="producto__informacion">
                            <p class="producto__nombre"><?php echo htmlspecialchars($product['name']); ?></p>
                            <p class="producto__precio">$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></p>
                        </div>
                    </a>
                </div> <?php endforeach; // Cierra el bucle foreach. ?>

            <div class="grafico grafico--camisas"></div> <div class="grafico grafico--node"></div>
        </div>
    </main>

    <div class="contenedor cart-summary">
        <h2>Carrito de Compras</h2>
        <?php
        $total_cart_price = 0;
        // Verifica si el carrito tiene elementos.
        if (!empty($_SESSION['cart'])):
        ?>
            <ul class="cart-item-list">
                <?php
                // Itera sobre cada elemento en el array de sesión del carrito.
                foreach ($_SESSION['cart'] as $item_key => $item):
                    // Calcula el subtotal para el item actual.
                    $item_subtotal = $item['price'] * $item['quantity'];
                    // Acumula al total general del carrito.
                    $total_cart_price += $item_subtotal;
                ?>
                    <li class="cart-item">
                        <img class="cart-item__image" src="<?php echo htmlspecialchars($item['image']); ?>" alt="Imagen de <?php echo htmlspecialchars($item['name']); ?>">
                        <div class="cart-item__details">
                            <p class="cart-item__name"><?php echo htmlspecialchars($item['name']); ?></p>
                            <p class="cart-item__info">Cantidad: <?php echo htmlspecialchars($item['quantity']); ?> <?php echo !empty($item['size']) ? ' | Talla: ' . htmlspecialchars($item['size']) : ''; ?></p>
                            <p class="cart-item__info">Precio unitario: $<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></p>
                            <p class="cart-item__info">Subtotal: $<?php echo htmlspecialchars(number_format($item_subtotal, 2)); ?></p>
                        </div>
                        <form action="index.php" method="POST" class="cart-item__remove-form">
                            <input type="hidden" name="item_key" value="<?php echo htmlspecialchars($item_key); ?>">
                            <button type="submit" name="remove_from_cart" class="cart-item__remove-btn">Eliminar</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="cart-total">
                Total: $<?php echo htmlspecialchars(number_format($total_cart_price, 2)); ?>
            </div>
            <div class="checkout-button-container">
                 <a href="checkout.php" class="checkout-button">Proceder al Pago</a>
            </div>
        <?php else: ?>
            <p class="empty-cart-message">Tu carrito de compras está vacío.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p class="footer__texto"> Todos los derechos reservados 2025.</p>
    </footer>

</body>
</html>