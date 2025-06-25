<?php
session_start();
require_once 'config.php'; // Ahora $pdo es nuestra conexión PDO, NO $conn de MySQLi

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    $item_key_to_remove = $_POST['item_key'];
    
    if (isset($_SESSION['cart'][$item_key_to_remove])) {
        unset($_SESSION['cart'][$item_key_to_remove]);
    }
    header('Location: index.php');
    exit();
}

// --- Obtener productos de la base de datos con PDO ---
try {
    // Aquí el cambio: Usamos $pdo->query() en lugar de mysqli_query()
    $stmt = $pdo->query("SELECT id, name, price, image FROM products");
    // Aquí el cambio: Usamos $stmt->fetchAll(PDO::FETCH_ASSOC) en lugar de mysqli_fetch_assoc() en un bucle
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener productos: " . $e->getMessage());
}

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
        /* (Tus estilos adicionales para el carrito, etc., aquí) */
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
            <?php foreach ($products as $product): ?>
                <div class="producto">
                    <a href="producto.php?id=<?php echo htmlspecialchars($product['id']); ?>">
                        <img class="producto__imagen" src="<?php echo htmlspecialchars($product['image']); ?>" alt="imagen <?php echo htmlspecialchars($product['name']); ?>">
                        <div class="producto__informacion">
                            <p class="producto__nombre"><?php echo htmlspecialchars($product['name']); ?></p>
                            <p class="producto__precio">$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>

            <div class="grafico grafico--camisas"></div>
            <div class="grafico grafico--node"></div>
        </div>
    </main>

    <div class="contenedor cart-summary">
        <h2>Carrito de Compras</h2>
        <?php
        $total_cart_price = 0;
        if (!empty($_SESSION['cart'])):
        ?>
            <ul class="cart-item-list">
                <?php foreach ($_SESSION['cart'] as $item_key => $item):
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
        <p class="footer__texto"> Front End Store - Todos los derechos reservados 2024.</p>
    </footer>

</body>
</html>