<?php
// Iniciar la sesión de PHP. Crucial para el carrito.
session_start();

// Incluir el archivo de configuración de la base de datos.
require_once 'config.php';

// Si el carrito no existe en la sesión, inicialízalo como un array vacío.
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Obtener el ID del producto de la URL (parámetro GET).
// filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) es una forma segura de obtener y validar un entero de la URL.
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Variable para almacenar los detalles del producto que se mostrará en la página.
$current_product = null;

// Validar que se recibió un ID de producto válido.
if ($product_id) {
    // Preparar la consulta SQL para obtener un producto específico por su ID.
    // Usamos una consulta preparada para prevenir inyecciones SQL.
    $sql = "SELECT id, name, price, image, description, sizes FROM products WHERE id = ?";
    // Prepara la declaración SQL. mysqli_prepare() devuelve un objeto de declaración.
    $stmt = mysqli_prepare($conn, $sql);

    // Si la preparación de la declaración fue exitosa.
    if ($stmt) {
        // Enlaza el parámetro 'id' (el '?' en la consulta) con la variable $product_id.
        // 'i' indica que el parámetro es un entero (integer).
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        // Ejecuta la declaración preparada.
        mysqli_stmt_execute($stmt);
        // Obtiene el resultado de la ejecución.
        $result = mysqli_stmt_get_result($stmt);

        // Si se encontró el producto, recupera sus datos.
        if (mysqli_num_rows($result) > 0) {
            $current_product = mysqli_fetch_assoc($result);
            // Convertir la cadena de tallas separada por comas en un array.
            $current_product['sizes'] = explode(',', $current_product['sizes']);
        }
        // Cierra la declaración.
        mysqli_stmt_close($stmt);
    }
}

// Si el producto no se encuentra (ID no válido o no existe), redirige al index.
if (!$current_product) {
    header('Location: index.php');
    exit();
}

// --- Lógica para agregar productos al carrito ---
// Comprueba si la solicitud es POST (se envió el formulario) y si el botón 'add_to_cart' fue presionado.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    // Obtener la cantidad, asegurándose de que sea un entero y al menos 1.
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($quantity < 1) { $quantity = 1; }

    // Obtener la talla seleccionada.
    $size = isset($_POST['size']) ? htmlspecialchars($_POST['size']) : '';

    // Validar que se seleccionó una talla si el producto tiene tallas definidas.
    // Solo se valida si el array de tallas no está vacío y la talla seleccionada no es una de las válidas.
    if (!empty($current_product['sizes']) && !in_array($size, $current_product['sizes'])) {
        // Podrías añadir un mensaje de error o ignorar la adición al carrito.
        // echo "<p style='color: red;'>Por favor, seleccione una talla válida.</p>";
        // No procesamos la adición al carrito si la talla no es válida.
    } else {
        // La clave del carrito ahora incluye el ID del producto y la talla
        // para diferenciar, por ejemplo, "Camiseta S" de "Camiseta M".
        // htmlspecialchars() para $size_key previene XSS si el valor de la talla viniera de una fuente no confiable.
        $cart_item_key = $current_product['id'] . '_' . $size;

        // Si el producto (con la talla específica) ya está en el carrito.
        if (isset($_SESSION['cart'][$cart_item_key])) {
            // Incrementa la cantidad.
            $_SESSION['cart'][$cart_item_key]['quantity'] += $quantity;
        } else {
            // Si es un nuevo artículo, añadirlo al carrito con todos sus detalles.
            $_SESSION['cart'][$cart_item_key] = [
                'id' => $current_product['id'], // Guardar el ID original del producto
                'name' => $current_product['name'],
                'price' => (float)$current_product['price'], // Asegurarse de que el precio sea flotante
                'image' => $current_product['image'],
                'quantity' => $quantity,
                'size' => $size // Talla seleccionada por el usuario
            ];
        }

        // Redireccionar para evitar el reenvío del formulario (Post/Redirect/Get).
        header('Location: producto.php?id=' . $current_product['id']);
        exit(); // Termina la ejecución del script.
    }
}

// --- Lógica para eliminar productos del carrito ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $item_key_to_remove = $_POST['item_key']; // Obtenemos la clave completa del ítem del carrito
    
    if (isset($_SESSION['cart'][$item_key_to_remove])) {
        unset($_SESSION['cart'][$item_key_to_remove]);
    }
    header('Location: producto.php?id=' . $current_product['id']); // Redirige a la misma página
    exit();
}

// La conexión a la BD se cierra implícitamente al final del script.
// mysqli_close($conn); // Puedes descomentar si quieres cerrar explícitamente.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($current_product['name']); ?> - QUEST</title>
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
        <h1><?php echo htmlspecialchars($current_product['name']); ?></h1>

        <div class="camisa">
            <img class="camisa__imagen" src="<?php echo htmlspecialchars($current_product['image']); ?>" alt="imagen del producto <?php echo htmlspecialchars($current_product['name']); ?>">
            <div>
                <p><?php echo htmlspecialchars($current_product['description']); ?></p>
                <p class="producto__precio">$<?php echo htmlspecialchars(number_format($current_product['price'], 2)); ?></p>

                <form class="formulario" action="producto.php?id=<?php echo htmlspecialchars($current_product['id']); ?>" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($current_product['id']); ?>">

                    <select class="formulario__campo" name="size" required>
                        <option disabled selected>--SELECCIONAR TALLA--</option>
                        <?php
                        // Genera las opciones de talla dinámicamente si el producto tiene tallas definidas.
                        if (!empty($current_product['sizes'])) {
                            foreach ($current_product['sizes'] as $size):
                                // htmlspecialchars() para escapar la talla al mostrarla.
                                // trim() para eliminar espacios en blanco si los hubiera en los datos de la BD
                        ?>
                                <option value="<?php echo htmlspecialchars(trim($size)); ?>"><?php echo htmlspecialchars(trim($size)); ?></option>
                        <?php
                            endforeach;
                        } else {
                            // Si no hay tallas definidas (o la columna 'sizes' está vacía), podrías poner una opción por defecto o deshabilitar el select.
                            echo '<option value="">Sin Talla Aplicable</option>';
                        }
                        ?>
                    </select>

                    <input class="formulario__campo" type="number" name="quantity" placeholder="CANTIDAD" min="1" value="1" required>
                    <input class="formulario__submit" type="submit" name="add_to_cart" value="AGREGAR AL CARRITO">
                </form>
            </div>
        </div>
    </main>

    <div class="contenedor cart-summary">
        <h2>Carrito de Compras</h2>
        <?php
        $total_cart_price = 0;
        if (!empty($_SESSION['cart'])):
        ?>
            <ul class="cart-item-list">
                <?php
                foreach ($_SESSION['cart'] as $item_key => $item):
                    $item_subtotal = $item['price'] * $item['quantity'];
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
                        <form action="producto.php?id=<?php echo htmlspecialchars($current_product['id']); ?>" method="POST" class="cart-item__remove-form">
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
        <p class="footer__texto"> QUEST LA CRUZ - Todos los derechos reservados 2022.</p>
    </footer>

</body>
</html>