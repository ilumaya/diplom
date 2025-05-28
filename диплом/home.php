<?php
session_start();
$title = "История про стиль";

// Подключение к базе данных
$host = 'db'; // Используем имя сервиса из docker-compose
$user = 'root';
$password = '12345';
$dbname = 'Гардероб';

$conn = new mysqli($host, $user, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Получение стилей из базы данных
$styles = [];
$sql = "SELECT назваине, описание, фото FROM Стили";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $styles[] = $row;
    }
}

// Получение топ-5 часто добавляемых вещей
$topItems = [];
$sqlTop = "
    SELECT Название, Фото, COUNT(*) as количество 
    FROM Свой_гардероб 
    GROUP BY Название, Фото 
    ORDER BY количество DESC 
    LIMIT 5";
$resultTop = $conn->query($sqlTop);

if ($resultTop->num_rows > 0) {
    while ($row = $resultTop->fetch_assoc()) {
        $topItems[] = $row;
    }
}

$conn->close();
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
    <nav class="navbar">
        <ul>
            <li><a href="home.php">Главная</a></li>
            <li><a href="wardrobe.php">Мой гардероб</a></li>
            <li><a href="things.php">Вещи</a></li>
            <li><a href="test.php">Тест на стиль</a></li>
        </ul>
    </nav>
</header>

<div class="content-wrapper">
    <section class="top-items-section">
        <h2>Топ 5 часто добавляемых вещей</h2>
        <div class="top-items-container">
            <?php foreach ($topItems as $item): ?>
                <div class="top-item-box">
                    <img src="<?php echo htmlspecialchars($item['Фото']); ?>" alt="<?php echo htmlspecialchars($item['Название']); ?>">
                    <div class="top-item-title"><?php echo htmlspecialchars($item['Название']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="styles-section">
        <h2>История стилей</h2>
        <div class="style-container">
            <?php foreach ($styles as $style): ?>
                <div class="style-box" onclick="this.querySelector('.accordion-content').style.display = this.querySelector('.accordion-content').style.display === 'block' ? 'none' : 'block'">
                    <img src="<?php echo htmlspecialchars($style['фото']); ?>" alt="<?php echo htmlspecialchars($style['назваине']); ?>">
                    <div class="style-title"><?php echo htmlspecialchars($style['назваине']); ?></div>
                    <div class="accordion-content">
                        <p><?php echo htmlspecialchars($style['описание']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

</body>
</html>
