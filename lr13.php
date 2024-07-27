<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restaurant";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
˳����� ����:index.php 
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
    <title>���� ���������</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1 class="text-center my-4">���� ���������</h1>
    <h2 class="text-center mb-4">������� �������, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <div class="text-right mb-4">
        <a href="logout.php" class="btn btn-danger">�����</a>
        <a href="cart.php" class="btn btn-primary">����������� �����</a>
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
                                <p class="card-text">ֳ��: <?php echo htmlspecialchars($row['price']); ?> $</p>
                                <form method="post" action="">
                                    <input type="hidden" name="dish_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <button type="submit" class="btn btn-success">������ �� ������</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else : ?>
                <p class="text-center">���� ������.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>

<?php
$conn->close();
?>
˳����� ����:registr.php
<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "����� �� ����������.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $hashed_password);
        if ($stmt->execute()) {
            header("Location: login.php");
            exit;
        } else {
            echo "�������: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>���������</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1 class="text-center my-4">���������</h1>
    <form method="post" action="" class="form-signin">
        <div class="form-group">
            <label for="username">��'� �����������</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">������</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">ϳ����������� ������</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">��������������</button>
    </form>
</div>
</body>
</html>
˳����� ����:loginr.php
<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT id, password FROM users WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $hashed_password);
    if ($stmt->fetch()) {
        if (password_verify($password, $hashed_password)) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $id;
            header("Location: index.php");
            exit;
        } else {
            echo "������� ������.";
        }
    } else {
        echo "����������� � ����� ��'�� �� ��������.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>����</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1 class="text-center my-4">����</h1>
    <form method="post" action="" class="form-signin">
        <div class="form-group">
            <label for="username">��'� �����������</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">������</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">�����</button>
    </form>
    <p class="text-center mt-3">���� �� �� �����������, <a href="register.php">������� ���������</a>.</p>
</div>
</body>
</html>
˳����� ����:order.php
<?php
session_start();
require 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// �������� �� �������� ����� ��� ��������� ������ �� ������
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dish_id'])) {
    $dish_id = $_POST['dish_id'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][] = $dish_id;
    $success_message = "����� ������ ������ � �����!";
}

// ������� ����� ��� ���������� ����������
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order'])) {
    $user_id = $_SESSION['user_id'];
    $order_date = date('Y-m-d H:i:s');
    $total_price = 0;
    $dish_ids = implode(',', $_SESSION['cart']); // ������������ ����� �� ������

    // ���������� �������� ���� ����������
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

    // ������� ���������� � ������� orders
    $sql_order = "INSERT INTO orders (user_id, order_date, dish_ids, total_price) VALUES (?, ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("issd", $user_id, $order_date, $dish_ids, $total_price);
    $stmt_order->execute();
    $stmt_order->close();

    // �������� ������ ���� ���������� ����������
    unset($_SESSION['cart']);
    $order_success_message = "���������� ������ ���������!";
}

// ��������� ������ ������ � ���� �����
$sql = "SELECT * FROM menu";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>�����</title>
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
    <h1 class="text-center my-4">�����</h1>
    <a href="index.php" class="btn btn-secondary mb-4">����������� �� ����</a>
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
                                <p class="card-text">ֳ��: <?php echo $item['price']; ?> ���</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <p class="text-center">�������� ����: <?php echo $total_price; ?> ���</p>
                <button type="submit" name="order" class="btn btn-success btn-block">�������� ����������</button>
            <?php else : ?>
                <p class="text-center">��� ����� �������.</p>
            <?php endif; ?>
        </div>
    </form>
</div>
</body>
</html>

<?php
$conn->close();
?>
˳����� ����:cart.php
<?php
session_start();
require 'db.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$order_success_message = '';
$order_error_message = '';
$success_message = '';

// �������� �� �������� ����� ��� ��������� ������ �� ������
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dish_id'])) {
    $dish_id = $_POST['dish_id'];
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][] = $dish_id;
    $success_message = "����� ������ ������ � �����!";
}

// ������� ����� ��� ���������� ����������
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order'])) {
    $user_id = $_SESSION['user_id'];
    $order_date = date('Y-m-d H:i:s');
    $total_price = 0;
    $dish_ids = implode(',', $_SESSION['cart']); // ������������ ����� �� ������

    // ���������� �������� ���� ����������
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

    // ������� ���������� � ������� orders
    $sql_order = "INSERT INTO orders (user_id, order_date, dish_ids, total_price) VALUES (?, ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("issd", $user_id, $order_date, $dish_ids, $total_price);
    if ($stmt_order->execute()) {
        $order_success_message = "���������� ������ ���������!";
    } else {
        $order_error_message = "������� �� ��� ���������� ����������: " . $conn->error;
    }
    $stmt_order->close();

    // �������� ������ ���� ���������� ����������
    unset($_SESSION['cart']);
}

// ��������� ������ ������ � ���� �����
$sql = "SELECT * FROM menu";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>�����</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .success-message, .error-message {
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
        .error-message {
            background-color: #dc3545; 
        }
    </style>
</head>
<body>
<div class="container">
    <h1 class="text-center my-4">�����</h1>
    <a href="index.php" class="btn btn-secondary mb-4">����������� �� ����</a>
    <?php if (!empty($success_message)) : ?>
        <div class="success-message" id="successMessage"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if (!empty($order_success_message)) : ?>
        <div class="success-message" id="orderSuccessMessage"><?php echo $order_success_message; ?></div>
    <?php endif; ?>
    <?php if (!empty($order_error_message)) : ?>
        <div class="error-message" id="orderErrorMessage"><?php echo $order_error_message; ?></div>
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
                                <p class="card-text">ֳ��: <?php echo $item['price']; ?> ���</p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <p class="text-center">�������� ����: <?php echo $total_price; ?> ���</p>
                <button type="submit" name="order" class="btn btn-success btn-block">�������� ����������</button>
            <?php else : ?>
                <p class="text-center">��� ����� �������.</p>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var successMessage = document.querySelector('.success-message');
        var errorMessage = document.querySelector('.error-message');
        if (successMessage) {
            successMessage.style.display = 'block';
            setTimeout(function() {
                successMessage.style.display = 'none';
            }, 3000);
        }
        if (errorMessage) {
            errorMessage.style.display = 'block';
            setTimeout(function() {
                errorMessage.style.display = 'none';
            }, 3000);
        }
    });

    document.getElementById('orderForm').addEventListener('submit', function(e) {
        var orderSuccessMessage = document.getElementById('orderSuccessMessage');
        var orderErrorMessage = document.getElementById('orderErrorMessage');
        if (orderSuccessMessage || orderErrorMessage) {
            e.preventDefault();
            if (orderSuccessMessage) {
                orderSuccessMessage.style.display = 'block';
                setTimeout(function() {
                    orderSuccessMessage.style.display = 'none';
                    window.location.href = 'index.php';
                }, 3000);
            }
            if (orderErrorMessage) {
                orderErrorMessage.style.display = 'block';
                setTimeout(function() {
                    orderErrorMessage.style.display = 'none';
                }, 3000);
            }
        }
    });
</script>
</body>
</html>

<?php
$conn->close();
?>

