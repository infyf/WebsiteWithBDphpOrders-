<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dish_id'])) {
    $dish_id = $_POST['dish_id'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][] = $dish_id;
}

$sql = "SELECT * FROM menu";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Меню ресторану</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1 class="text-center my-4">Меню ресторану</h1>
    <h2 class="text-center mb-4">Ласкаво просимо, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <div class="text-right mb-4">
        <a href="logout.php" class="btn btn-danger">Вийти</a>
        <a href="cart.php" class="btn btn-primary">Переглянути кошик</a>
    </div>

    <div class="menu-section">
        <div class="row">
            <?php if ($result->num_rows > 0) : ?>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                                <p class="card-text">Ціна: <?php echo htmlspecialchars($row['price']); ?> $</p>
                                <form method="post" action="">
                                    <input type="hidden" name="dish_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <button type="submit" class="btn btn-success">Додати до кошика</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <p class="text-center">Меню порожнє.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>

<?php
$conn->close();
?>
