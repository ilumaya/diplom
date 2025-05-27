<?php
session_start();
$title = "Тест на определение стиля";

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['Логин'])) {
    header("Location: profil.php");
    exit();
}

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

// Определение вопросов
$questions = [
    "Какую одежду вы предпочитаете носить?" => ["Классика", "Спортивный", "Кэжуал"],
    "Какой ваш любимый аксессуар?" => ["Элегантный", "Гранж", "Бохо"],
    "Какой цвет вам больше нравится?" => ["Классика", "Спортивный", "Минимализм"],
    "Какой стиль обуви вам ближе?" => ["Классика", "Спортивный", "Кэжуал"],
    "Какой из этих принтов вам нравится больше?" => ["Элегантный", "Гранж", "Бохо"]
];

// Обработка формы теста
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_test'])) {
    $login = $_SESSION['Логин'];
    $styleScore = array_fill_keys([
        'Классика', 'Спортивный', 'Кэжуал', 'Бохо', 
        'Минимализм', 'Романтический', 'Гранж', 
        'Элегантный', 'Уличный'
    ], 0);

    $answers = $_POST['answers'] ?? [];
    $unanswered = [];

    // Проверка, существуют ли ответы
    foreach ($questions as $question => $options) {
        $index = array_search($question, array_keys($questions));
        if ($index !== false && isset($answers[$index])) {
            $styleScore[$answers[$index]]++;
        } else {
            $unanswered[] = $question;
        }
    }

    if (empty($unanswered)) {
        // Определение стиля с наибольшим количеством баллов
        $resultStyle = array_keys($styleScore, max($styleScore))[0];

        // Вставка результата в базу данных
        $sql = "INSERT INTO Тест (логин, результат) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $login, $resultStyle);

        if ($stmt->execute()) {
            $message = "Ваш стиль: " . $resultStyle . ". Результат успешно сохранен!";
        } else {
            $message = "Ошибка при сохранении результата: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Пожалуйста, ответьте на все вопросы: " . implode(", ", $unanswered);
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

        h2 {
            color: #333;
            margin-top: 20px;
        }
        .message {
            padding: 10px;
            margin: 15px 0;
            border-radius: 5px;
            background-color: #d4edda;
            color: #155724;
            text-align: center;
        }
        .question {
            margin-top: 20px;
        }
        .option-container {
            padding: 15px;
            border: 2px solid #A1613B;
            border-radius: 5px;
            margin: 10px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .option-container:hover {
            background-color: #f0e0db;
        }
        .selected {
            background-color: #A1613B;
            color: white;
        }
        button[type="submit"] {
            padding: 12px 20px;
            background-color: #A1613B;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.3s;
            margin-top: 20px;
            width: 100%;
        }
        button[type="submit"]:hover {
            background-color: #8b4f31;
            transform: scale(1.05);
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
    <?php if (isset($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <h2>Какой стиль вам ближе?</h2>

        <?php
        foreach ($questions as $question => $options) {
            echo "<div class='question'><h3>$question</h3>";
            foreach ($options as $value) {
                echo "<div class='option-container' data-value='$value' onclick='selectOption(this)'>$value</div>";
            }
            echo "<input type='hidden' name='answers[]' class='answer-input' value=''>"; // Скрытое поле для ответа
            echo "</div>";
        }
        ?>

        <button type="submit" name="submit_test">Отправить</button>
    </form>
</div>

<script>
    function selectOption(element) {
        const question = element.closest('.question');
        
        // Удаляем выделение у всех опций
        question.querySelectorAll('.option-container').forEach(opt => opt.classList.remove('selected'));
        
        // Выделяем текущую опцию
        element.classList.add('selected');

        // Получаем значение выбранной опции
        const value = element.getAttribute('data-value');

        // Устанавливаем значение в скрытое поле для ответа
        const input = question.querySelector('.answer-input');
        input.value = value;
    }
</script>
</body>
</html>