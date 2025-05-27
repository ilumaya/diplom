<?php
session_start();
$title = "Мой гардероб";

// Подключение к базе данных
$host = "localhost"; // ваш хост
$user = "root"; // ваше имя пользователя
$password = "12345"; // ваш пароль
$dbname = "Гардероб";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Получение логина пользователя
$login = isset($_SESSION['Логин']) ? $_SESSION['Логин'] : 'гость';

// Проверка, был ли передан id
$message = ""; // Переменная для сообщения
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Проверяем, существует ли вещь в гардеробе
    $checkSql = "SELECT * FROM Свой_гардероб WHERE Логин = '$login' AND id_вещи = $id";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows > 0) {
        // Если вещь уже есть в гардеробе
        $message = "Эта вещь уже добавлена в ваш гардероб.";
    } else {
        // Получаем данные о вещи
        $itemSql = "SELECT * FROM Вещи WHERE id = $id";
        $itemResult = $conn->query($itemSql);

        if ($itemResult->num_rows > 0) {
            $item = $itemResult->fetch_assoc();

            // Добавляем вещь в свой гардероб
            $insertSql = "INSERT INTO Свой_гардероб (Логин, id_вещи, Название, Цвет, Категория, Стиль, Фото, Описание) 
                          VALUES ('$login', $item[id], '$item[Название]', '$item[Цвет]', '$item[Категория]', '$item[Стиль]', '$item[Фото]', '$item[Описание]')";
            $conn->query($insertSql);
            $message = "Вещь успешно добавлена в ваш гардероб.";
        }
    }
}

// Извлечение данных из таблицы Свой_гардероб
$wardrobeSql = "SELECT * FROM Свой_гардероб WHERE Логин = '$login'";
$wardrobeResult = $conn->query($wardrobeSql);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            min-height: 600px;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .card {
            background-color: #FFF2EA; /* Цвет фона карточки */
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: relative;
            width: calc(25% - 20px); /* Ширина карточки (4 в ряд) */
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            text-align: center; /* Центрируем текст */
        }
        .card h2 {
            font-size: 1.2em; /* Увеличиваем размер шрифта для названия */
            margin: 10px 0; /* Отступы для названия */
            color: #A1613B; /* Цвет текста названия */
        }
        .info {
            display: none; /* Скрываем информацию по умолчанию */
            position: absolute; /* Абсолютное позиционирование */
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.95); /* Изменен цвет фона для лучшей читаемости */
            border-radius: 8px; /* Скругленные углы */
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            text-align: left;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .card:hover .info {
            display: block;
            opacity: 1;
        }
        .message {
            margin: 10px 0;
            color: #A1613B;
            text-align: center;
        }
    </style>
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
    <?php if ($message): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>
    <div class="card-container">
        <?php if ($wardrobeResult->num_rows > 0): ?>
            <?php while($row = $wardrobeResult->fetch_assoc()): ?>
                <div class="card">
                    <h2><?php echo htmlspecialchars($row['Название']); ?></h2>
                    <img src="<?php echo htmlspecialchars($row['Фото']); ?>" alt="<?php echo htmlspecialchars($row['Название']); ?>" />
                    <div class="info">
                        <p>Цвет: <?php echo htmlspecialchars($row['Цвет']); ?></p>
                        <p>Категория: <?php echo htmlspecialchars($row['Категория']); ?></p>
                        <p>Стиль: <?php echo htmlspecialchars($row['Стиль']); ?></p>
                        <p><?php echo htmlspecialchars($row['Описание']); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Ваш гардероб пуст.</p>
        <?php endif; ?>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>

<?php
$conn->close();
?>