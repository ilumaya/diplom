<?php
session_start();
$title = "История про стиль";
$message = ""; // Переменная для сообщений
$messageType = ""; // Переменная для типа сообщения

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

// Функция для проверки пароля
function isValidPassword($password) {
    return preg_match('/^[a-zA-Z0-9]{7,}$/', $password);
}

// Обработка регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Проверка уникальности логина
    $sql = "SELECT * FROM Пользователи WHERE Логин = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "Этот логин уже занят. Пожалуйста, выберите другой.";
        $messageType = "error"; // Ошибка
    } elseif (!isValidPassword($password)) {
        $message = "Пароль должен содержать минимум 7 символов и состоять только из английских букв и цифр.";
        $messageType = "error"; // Ошибка
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Хэшируем пароль

        // SQL запрос для вставки нового пользователя
        $sql = "INSERT INTO Пользователи (Логин, Пароль) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $hashedPassword);

        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            $message = "Вы успешно зарегистрированы!";
            $messageType = "success"; // Успех
        } else {
            $message = "Ошибка при регистрации: " . $stmt->error;
            $messageType = "error"; // Ошибка
        }
    }

    $stmt->close();
}

// Проверка, авторизован ли пользователь
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

// Обработка авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_username']) && isset($_POST['login_password'])) {
    $loginUsername = $_POST['login_username'];
    $loginPassword = $_POST['login_password'];

    // Проверка существования пользователя
    $sql = "SELECT * FROM Пользователи WHERE Логин = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $loginUsername);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $message = "Вы не зарегистрированы. Пожалуйста, зарегистрируйтесь.";
        $messageType = "error"; // Ошибка
    } else {
        $user = $result->fetch_assoc();
        if (password_verify($loginPassword, $user['Пароль'])) {
            $_SESSION['username'] = $loginUsername;

            // Перенаправление на admin.php для администратора
            if ($loginUsername === 'admin') {
                header("Location: admin.php");
                exit();
            }

            $message = "Вы успешно вошли в аккаунт!";
            $messageType = "success"; // Успех
            header("Location: profil.php");
            exit();
        } else {
            $message = "Неверный пароль. Пожалуйста, попробуйте снова.";
            $messageType = "error"; // Ошибка
        }
    }

    $stmt->close();
}

// Логика выхода из аккаунта
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: profil.php");
    exit();
}

// Получение недавних вещей из базы данных
$recentItems = [];
if ($username) {
    $sql = "SELECT * FROM Свой_гардероб WHERE Логин = ? ORDER BY id_вещи DESC LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $recentItems[] = $row;
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
    <link rel="stylesheet" href="style.css">
    <style> 
        /* Добавлены отступы по бокам для всего контента */
        .content-wrapper {
            padding: 0 320px; /* Отступы по бокам */
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
    <div class="content-wrapper">
        <div class="user-info">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['Логин'])): ?>
                <p class="message success">Добро пожаловать, <?php echo htmlspecialchars($username); ?>!</p>
                <div class="user-actions">
                    <a href="?logout=true" class="button">Выйти из аккаунта</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="script.js"></script>
</body>
</html>