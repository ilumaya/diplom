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
            $_SESSION['Логин'] = $username; // Сохраняем логин в сессии
            $message = "Вы успешно зарегистрированы!";
            $messageType = "success"; // Успех
            header("Location: test.php"); // Перенаправление на тест
            exit();
        } else {
            $message = "Ошибка при регистрации: " . $stmt->error;
            $messageType = "error"; // Ошибка
        }
    }

    $stmt->close();
}

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
            $_SESSION['Логин'] = $loginUsername; // Сохраняем логин в сессии

            // Проверка на администратора
            if ($loginUsername === 'admin' && $loginPassword === 'admin1234') {
                header("Location: admin.php"); // Перенаправление на страницу администратора
                exit();
            }

            $message = "Вы успешно вошли в аккаунт!";
            $messageType = "success"; // Успех
            header("Location: test.php"); // Перенаправление на тест
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
if (isset($_SESSION['Логин'])) {
    $username = $_SESSION['Логин'];
    $sql = "SELECT * FROM Свой_гардероб WHERE Логин = ? ORDER BY id_вещи DESC LIMIT 3";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $recentItems[] = $row;
    }
}

// Логика генерации случайного образа
$randomOutfit = [];
if (isset($_POST['generate_outfit'])) {
    // Получаем все категории
    $sql = "SELECT DISTINCT Категория FROM Свой_гардероб WHERE Логин = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $categoryResult = $stmt->get_result();

    while ($categoryRow = $categoryResult->fetch_assoc()) {
        $category = $categoryRow['Категория'];

        // Получаем случайный элемент из каждой категории
        $sql = "SELECT * FROM Свой_гардероб WHERE Логин = ? AND Категория = ?";
        $stmt2 = $conn->prepare($sql);
        $stmt2->bind_param("ss", $username, $category);
        $stmt2->execute();
        $itemsResult = $stmt2->get_result();
        $items = $itemsResult->fetch_all(MYSQLI_ASSOC);

        if (!empty($items)) {
            // Выбираем случайный элемент из этой категории
            $randomKey = array_rand($items);
            $randomOutfit[] = $items[$randomKey];
        }
    }

    if (empty($randomOutfit)) {
        $message = "Ваш гардероб пуст.";
        $messageType = "error"; // Ошибка
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
.message {
    padding: 10px;
    margin: 15px 0;
    border-radius: 5px;
}

.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.error {
    background-color: #ffdddd;
    color: #d8000c;
    border: 1px solid #d8000c;
}

.card-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    padding: 20px;
}

.card {
    background-color: #FFF2EA;
    border-radius: 15px;
    padding: 20px;
    margin: 10px;
    text-align: left;
    width: calc(25% - 20px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 400px;
}

.card img {
    max-width: 100%;
    height: 178px;
    object-fit: contain;
    border-radius: 8px;
    margin-bottom: 10px;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    border-radius: 10px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

h2 {
    text-align: center;
    color: #A1613B;
}

label {
    display: block;
    margin: 10px 0 5px;
}

input[type="text"],
input[type="password"] {
    width: 96%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

button {
    width: 100%;
    padding: 10px;
    background-color: #A1613B;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background-color: #8b4f31;
}

.content-wrapper {
    padding: 0 320px;
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

<main>
    <div class="content-wrapper">
        <div class="user-info">
            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['Логин'])): ?>
                <p class="message success">Добро пожаловать, <?php echo htmlspecialchars($_SESSION['Логин']); ?>!</p>
                <div class="user-actions">
                    <a href="?logout=true" class="button">Выйти из аккаунта</a>
                </div>
                <h2>Недавно добавленные вещи в гардероб:</h2>
                <div class="card-container">
                    <?php foreach ($recentItems as $item): ?>
                        <div class="card">
                            <h3><?php echo htmlspecialchars($item['Название']); ?></h3>
                            <p>Цвет: <?php echo htmlspecialchars($item['Цвет']); ?></p>
                            <p>Категория: <?php echo htmlspecialchars($item['Категория']); ?></p>
                            <p>Стиль: <?php echo htmlspecialchars($item['Стиль']); ?></p>
                            <img src="<?php echo htmlspecialchars($item['Фото']); ?>" alt="<?php echo htmlspecialchars($item['Название']); ?>">
                            <p><?php echo htmlspecialchars($item['Описание']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form action="" method="POST">
                    <button type="submit" name="generate_outfit">Сгенерировать случайный образ</button>
                </form>
                <?php if (!empty($randomOutfit)): ?>
                    <h2>Ваш случайный образ:</h2>
                    <div class="card-container">
                        <?php foreach ($randomOutfit as $item): ?>
                            <div class="card">
                                <h3><?php echo htmlspecialchars($item['Название']); ?></h3>
                                <p>Цвет: <?php echo htmlspecialchars($item['Цвет']); ?></p>
                                <p>Категория: <?php echo htmlspecialchars($item['Категория']); ?></p>
                                <p>Стиль: <?php echo htmlspecialchars($item['Стиль']); ?></p>
                                <img src="<?php echo htmlspecialchars($item['Фото']); ?>" alt="<?php echo htmlspecialchars($item['Название']); ?>">
                                <p><?php echo htmlspecialchars($item['Описание']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>Вы не зарегистрированы. <a href="#" id="registerBtn">Зарегистрируйтесь</a> или <a href="#" id="loginBtn">войдите</a>.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Модальные окна для регистрации и авторизации -->
<div id="registerModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeRegister">&times;</span>
        <h2>Регистрация</h2>
        <form action="profil.php" method="POST">
            <label for="username">Имя пользователя:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Зарегистрироваться</button>
        </form>
    </div>
</div>

<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeLogin">&times;</span>
        <h2>Авторизация</h2>
        <form action="profil.php" method="POST">
            <label for="loginUsername">Имя пользователя:</label>
            <input type="text" id="loginUsername" name="login_username" required>
            <label for="loginPassword">Пароль:</label>
            <input type="password" id="loginPassword" name="login_password" required>
            <button type="submit">Войти</button>
        </form>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>