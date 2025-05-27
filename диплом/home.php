<?php
session_start();
$title = "История про стиль";

// Подключение к базе данных
$host = 'localhost';
$user = 'root'; // Ваше имя пользователя
$password = '12345'; // Ваш пароль
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

// Получение топ-5 часто добавляемых вещей из таблицы "Свой_гардероб"
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

// Закрываем соединение
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="style.css"> <!-- Подключаем ваш CSS файл -->
    <style>
        .navbar ul {
            display: flex;
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        .navbar li {
            margin: 0 15px;
        }

        .navbar a {
            color: #fff;
            text-decoration: none;
        }

        .content-wrapper {
            max-width: 800px; /* Максимальная ширина контейнера */
            margin: 20px auto; /* Центрирование контейнера */
            padding: 20px; /* Отступы внутри контейнера */
        }

        h1 {
            margin-bottom: 10px;
            text-align: center;
        }

        .top-items-section, .styles-section {
            margin: 20px 0;
        }

        .top-items-container, .style-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .top-item-box, .style-box {
            background-color: #FFF2EA; /* Цвет фона карточки */
            border-radius: 15px;
            padding: 10px; /* Уменьшенные отступы */
            margin: 10px;
            text-align: center; /* Центрирование текста */
            width: calc(25% - 20px); /* Ширина карточки (4 в ряд) */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Тень карточки */
            display: flex; /* Используем flexbox для выравнивания */
            flex-direction: column; /* Вертикальное направление */
            justify-content: space-between; /* Разделение пространства между элементами */
            height: auto; /* Автоматическая высота карточки */
            cursor: pointer; /* Указатель для кликабельности */
        }

        .top-item-box img, .style-box img {
            max-width: 100%; /* Максимальная ширина изображения */
            height: auto; /* Автоматическая высота для изображения */
            object-fit: cover; /* Обрезка изображения по размеру */
            border-radius: 8px; /* Скругление углов изображения */
            margin-bottom: 10px; /* Отступ снизу для изображения */
        }

        .accordion-content {
            padding: 15px;
            display: none;
            background-color: #FFF2EA;
            border-top: 1px solid #A1613B; /* Разделительная линия */
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
                <div class="tooltip">Профиль</div> <!-- Всплывающее сообщение -->
            </div>
        </nav>
    </div>
</header>

<div class="content-wrapper">

    <!-- Топ-5 часто добавляемых вещей -->
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

    <!-- Стили -->
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

<script>
    // Дополнительная логика может быть добавлена здесь, если необходимо
</script>
</body>
</html>