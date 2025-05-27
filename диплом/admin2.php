<?php
session_start();
$title = "Гардероб пользователей";
$message = ""; // Переменная для сообщений

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

// Получение всех пользователей для выбора
$users = [];
$sql = "SELECT DISTINCT Логин FROM Пользователи";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $users[] = $row['Логин'];
}

// Обработка выбора пользователя
$selectedUser = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_user'])) {
    $selectedUser = $_POST['user'];
}

// Обработка добавления вещи
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $id = intval($_POST['id']); // Ввод ID
    $name = $_POST['name'];
    $color = $_POST['color'];
    $category = $_POST['category'];
    $style = $_POST['style'];
    $description = $_POST['description'];

    // Загрузка изображения
    $photo = $_FILES['photo']['name'];
    $target_dir = "uploads/"; // Директория для загрузки
    $target_file = $target_dir . basename($photo);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Проверка существования директории и прав
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true); // Создаем директорию, если ее нет
    }

    // Проверка на существование файла
    if (file_exists($target_file)) {
        $message = "Извините, файл с таким именем уже существует.";
        $uploadOk = 0;
    }

    // Проверка формата файла
    if ($uploadOk == 1 && !in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        $message = "Извините, только JPG, JPEG, PNG и GIF файлы допускаются.";
        $uploadOk = 0;
    }

    // Если всё в порядке, загружаем файл
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $sql = "INSERT INTO Вещи (id, Название, Цвет, Категория, Стиль, Фото, Описание) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssss", $id, $name, $color, $category, $style, $target_file, $description);

            if ($stmt->execute()) {
                $message = "Вещь успешно добавлена!";
            } else {
                $message = "Ошибка при добавлении вещи: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Ошибка при загрузке файла. Проверьте права доступа к директории.";
        }
    }
}

// Получение всех вещей из гардероба выбранного пользователя
$items = [];
if ($selectedUser) {
    $sql = "SELECT * FROM Свой_гардероб WHERE Логин = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selectedUser);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();
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
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .main-header {
            background-color: #DDC0AE;
            color: #ffffff;
            padding: 50px 0;
            text-align: center; /* Центрирование содержимого в заголовке */
        }

        .header-content {
            padding: 0 20px;
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
        }

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
            font-weight: bold; /* Сделать текст жирным */
        }

        .profile-icon {
            margin-left: auto;
        }

        .profile-icon img {
            width: 30px; /* Размер иконки профиля */
            height: auto;
        }



        h1 {
            margin-bottom: 10px;
            color: #A1613B; /* Цвет заголовка */
        }

        h2 {
            color: #A1613B; /* Цвет подзаголовка */
        }

        .message {
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
            border: 1px solid transparent; /* Добавлено для поддержки разных типов сообщений */
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .error {
            background-color: #ffdddd;
            color: #d8000c;
            border-color: #d8000c;
        }

        .table {
            width: 100%;
            border-collapse: collapse; /* Убираем двойные границы */
            margin-top: 20px; /* Отступ сверху для таблицы */
        }

        .table th, .table td {
            padding: 12px; /* Увеличенные отступы для ячеек */
            text-align: left;
            border-bottom: 1px solid #ddd; /* Линия под ячейками */
        }

        .table th {
            background-color: #A1613B; /* Цвет фона заголовков таблицы */
            color: white; /* Цвет текста заголовков */
        }

        .table tr:hover {
            background-color: #f1f1f1; /* Цвет строки при наведении */
        }

        .button {
            display: inline-block; /* Чтобы кнопка была блочным элементом */
            padding: 10px 20px; /* Отступы внутри кнопки */
            background-color: #A1613B; /* Цвет фона кнопки */
            color: white; /* Цвет текста */
            text-align: center; /* Центрирование текста */
            text-decoration: none; /* Убираем подчеркивание */
            border-radius: 5px; /* Скругление углов */
            border: none; /* Убираем границы */
            cursor: pointer; /* Указываем, что это кнопка */
            transition: background-color 0.3s, transform 0.2s; /* Плавный переход цвета и эффекта сжатия */
        }

        .button:hover {
            background-color: #8b4f31; /* Цвет кнопки при наведении */
            transform: scale(1.05); /* Увеличение кнопки при наведении */
        }

        .button:active {
            transform: scale(0.95); /* Уменьшение кнопки при нажатии */
        }

        select {
            padding: 10px; /* Отступы внутри выпадающего списка */
            border: 1px solid #ccc; /* Граница */
            border-radius: 5px; /* Скругление углов */
            background-color: #fff; /* Фоновый цвет */
            color: #333; /* Цвет текста */
            width: 100%; /* Ширина */
            margin-top: 10px; /* Отступ сверху */
            appearance: none; /* Убираем стандартные стили */
            background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23333"%3E%3Cpath d="M7 10l5 5 5-5z"/%3E%3C/svg%3E'); /* Стрелка для выпадающего списка */
            background-repeat: no-repeat; /* Убираем повтор */
            background-position: right 10px center; /* Позиция стрелки */
            background-size: 12px; /* Размер стрелки */
        }
    </style>
</head>
<body>

<header class="main-header">
    <div class="header-content">
        <nav class="navbar">
            <ul>
                <li><a href="admin.php">Добавление вещей</a></li>
                <li><a href="admin2.php">Гардероб пользователей</a></li>
            </ul>   
            <div class="profile-icon">
                <a href="profil_admin.php">
                    <img src="i.png" alt="Профиль" />
                </a>
            </div>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <h1><?php echo $title; ?></h1>

        <?php if (isset($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <h2>Выберите пользователя</h2>
        <form action="" method="POST">
            <select name="user" required>
                <option value="">-- Выберите пользователя --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo htmlspecialchars($user); ?>" <?php echo ($selectedUser === $user) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="select_user" class="button">Выбрать</button>
        </form>

        <?php if ($selectedUser): ?>
            <h2>Гардероб пользователя: <?php echo htmlspecialchars($selectedUser); ?></h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
                        <th>Цвет</th>
                        <th>Категория</th>
                        <th>Стиль</th>
                        <th>Фото</th>
                        <th>Описание</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['id_вещи']); ?></td>
                            <td><?php echo htmlspecialchars($item['Название']); ?></td>
                            <td><?php echo htmlspecialchars($item['Цвет']); ?></td>
                            <td><?php echo htmlspecialchars($item['Категория']); ?></td>
                            <td><?php echo htmlspecialchars($item['Стиль']); ?></td>
                            <td><img src="<?php echo htmlspecialchars($item['Фото']); ?>" alt="<?php echo htmlspecialchars($item['Название']); ?>" width="50"></td>
                            <td><?php echo htmlspecialchars($item['Описание']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<script src="script.js"></script>
</body>
</html>