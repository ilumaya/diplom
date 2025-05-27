<?php
session_start();
$title = "Вещи";

// Подключение к базе данных
$host = "localhost"; // ваш хост
$user = "root"; // ваше имя пользователя
$password = "12345"; // ваш пароль
$dbname = "Гардероб";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Извлечение данных из таблицы Вещи
$sql = "SELECT * FROM Вещи";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="main-header">
    <div class="header-content">
        <nav class="navbar">
            <ul>
                <li><a href="home.php">Главная</a></li>
                <li><a href="wardrobe.php">Мой гардероб</a></li>
                <li><a href="things.php">Вещи</a></li>
                <li><a href="test.php">Тест на стиль</a></li>
            </ul>
            <div class="profile-icon">
                <a href="profil.php">
                    <img src="i.png" alt="Профиль" />
                </a>
            </div>
        </nav>
    </div>
</header>

<div class="container">
    <h1><?php echo $title; ?></h1>
    <div class="card-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-header">
                        <h2><?php echo $row['Название']; ?></h2>
                        <a href="wardrobe.php?id=<?php echo $row['id']; ?>" class="add-button">+</a>
                    </div>
                    <img src="<?php echo $row['Фото']; ?>" alt="<?php echo $row['Название']; ?>" />
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Нет доступных вещей.</p>
        <?php endif; ?>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>

<?php
$conn->close();
?>