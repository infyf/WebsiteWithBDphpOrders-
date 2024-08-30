<?php
session_start();
require 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dish_id'])) {
    $dish_id = $_POST['dish_id'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][] = $dish_id;
    $success_message = "Товар успішно додано в кошик!";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order'])) {
    $user_id = $_SESSION['user_id'];
    $order_date = date('Y-m-d H:i:s');
    $total_price = 0;
    $dish_ids = implode(',', $_SESSION['cart']); 

    foreach ($_SESSION['cart'] as $dish_id) {
        $sql_item = "SELECT price FROM menu WHERE id=?";
        $stmt_item = $conn->prepare($sql_item);
        $stmt_item->bind_param("i", $dish_id);
        $stmt_item->execute();
        $result_item = $stmt_item->get_result();
        $item = $result_item->fetch_assoc();
        $stmt_item->close();
        $total_price += $item['price'];
    }
    $sql_order = "INSERT INTO orders (user_id, order_date, dish_ids, total_price) VALUES (?, ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("issd", $user_id, $order_date, $dish_ids, $total_price);
    $stmt_order->execute();
    $stmt_order->close();

    unset($_SESSION['cart']);
    $order_success_message = "Замовлення успішно оформлено!";
}

$sql = "SELECT * FROM menu";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Кошик</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .success-message {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #28a745;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            z-index: 9999;
            display: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center my-4">Кошик</h1>
    <a href="index.php" class="btn btn-secondary mb-4">Повернутися до меню</a>
    <?php if (isset($success_message)) : ?>
        <div class="success-message" id="successMessage"><?php echo $success_message; ?></div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('successMessage').style.display = 'block';
                setTimeout(function() {
                    document.getElementById('successMessage').style.display = 'none';
                }, 3000);
            });
        </script>
    <?php endif; ?>
    <?php if (isset($order_success_message)) : ?>
        <div class="success-message" id="orderSuccessMessage"><?php echo $order_success_message; ?></div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('orderSuccessMessage').style.display = 'block';
                setTimeout(function() {
                    document.getElementById('orderSuccessMessage').style.display = 'none';
                }, 3000);
            });
        </script>
    <?php endif; ?>
    <form method="post" action="" id="orderForm">
        <div class="row">
            <?php if (!empty($_SESSION['cart'])) : ?>
                <?php 
                $total_price = 0;
                foreach ($_SESSION['cart'] as $dish_id) : ?>
                    <?php
                    $sql = "SELECT * FROM menu WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $dish_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $item = $result->fetch_assoc();
                    $stmt->close();
                    $total_price += $item['price'];
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="<?php echo $item['image_url']; ?>" class="card-img-top" alt="<?php echo $item['name']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $item['name']; ?></h5>
                                <p class="card-text"><?php echo $item['description']; ?></p>
                                <p class="card-text">Ціна: <?php echo $item['price']; ?> грн</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <p class="text-center">Загальна сума: <?php echo $total_price; ?> грн</p>
                <button type="submit" name="order" class="btn btn-success btn-block">Оформити замовлення</button>
            <?php else : ?>
                <p class="text-center">Ваш кошик порожній.</p>
            <?php endif; ?>
        </div>
    </form>
</div>
</body>
</html>

<?php
$conn->close();
?>
