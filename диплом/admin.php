<?php
session_start();
$title = "Добавление вещей";
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

// Обработка удаления вещи
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $itemId = intval($_POST['item_id']);

    $sql = "DELETE FROM Вещи WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemId);

    if ($stmt->execute()) {
        $message = "Вещь успешно удалена!";
    } else {
        $message = "Ошибка при удалении вещи: " . $stmt->error;
    }
    $stmt->close();
}

// Получение всех вещей
$items = [];
$sql = "SELECT * FROM Вещи";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
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

        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea {
            width: 100%; /* Занять всю ширину контейнера */
            padding: 10px; /* Отступы внутри полей */
            border: 1px solid #ccc; /* Светлая граница */
            border-radius: 5px; /* Скругление углов */
            margin-bottom: 15px; /* Отступ снизу для расстояния между полями */
            box-sizing: border-box; /* Учитываем отступы в ширине */
            transition: border-color 0.3s; /* Плавный переход цвета границы */
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            border-color: #A1613B; /* Цвет границы при фокусе */
            outline: none; /* Убираем стандартный outline */
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
            margin-top: 10px; /* Отступ сверху для кнопки */
        }

        .button:hover {
            background-color: #8b4f31; /* Цвет кнопки при наведении */
            transform: scale(1.05); /* Увеличение кнопки при наведении */
        }

        .button:active {
            transform: scale(0.95); /* Уменьшение кнопки при нажатии */
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

        /* Стили для кастомной кнопки выбора файла */
        .custom-file-input {
            display: none; /* Скрываем оригинальный input */
        }

        .file-label {
            display: inline-block;
            padding: 10px 20px;
            background-color: #A1613B;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 15px; /* Отступ снизу для расстояния между полями */
            margin-top: 10px; /* Отступ сверху для кнопки выбора файла */
        }

        .file-label:hover {
            background-color: #8b4f31;
        }

        /* Modal styles */
        .modal {
            display: none; /* Скрыт по умолчанию */
            position: fixed; /* Открыт поверх всего */
            z-index: 1; /* На переднем плане */
            left: 0;
            top: 0;
            width: 100%; /* Полная ширина */
            height: 100%; /* Полная высота */
            overflow: auto; /* Включить прокрутку при необходимости */
            background-color: rgb(0,0,0); /* Черный фон */
            background-color: rgba(0,0,0,0.8); /* Черный фон с прозрачностью */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% сверху и центрирование */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Ширина модального окна */
            border-radius: 8px; /* Скругление углов */
            position: relative; /* Позиционирование для close button */
        }

        .modal-image {
            width: 100%; /* Ширина изображения в модальном окне */
            max-width: 500px; /* Максимальная ширина */
            margin: 20px 0; /* Отступы вокруг изображения */
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

        <h2>Добавить вещь</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="number" name="id" placeholder="ID" required>
            <input type="text" name="name" placeholder="Название" required>
            <input type="text" name="color" placeholder="Цвет" required>
            <input type="text" name="category" placeholder="Категория" required>
            <input type="text" name="style" placeholder="Стиль" required>
            <label class="file-label" for="photo">Выберите файл</label>
            <input type="file" name="photo" id="photo" class="custom-file-input" accept="image/*" required>
            <textarea name="description" placeholder="Описание" required></textarea>
            <button type="submit" name="add_item" class="button">Добавить</button>
        </form>

        <h2>Список вещей</h2>
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
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['id']); ?></td>
                        <td><?php echo htmlspecialchars($item['Название']); ?></td>
                        <td><?php echo htmlspecialchars($item['Цвет']); ?></td>
                        <td><?php echo htmlspecialchars($item['Категория']); ?></td>
                        <td><?php echo htmlspecialchars($item['Стиль']); ?></td>
                        <td>
                            <img src="<?php echo htmlspecialchars($item['Фото']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['Название']); ?>" 
                                 width="50" 
                                 onclick="showModal('<?php echo htmlspecialchars($item['id']); ?>')">
                        </td>
                        <td><?php echo htmlspecialchars($item['Описание']); ?></td>
                        <td>
                            <form action="" method="POST" style="display:inline;">
                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="delete_item" class="button">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal -->
<div id="itemModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Детали вещи</h2>
        <div id="modalBody"></div>
    </div>
</div>

<script>
function showModal(itemId) {
    // Fetch item details via AJAX
    fetch('get_item_details.php?id=' + itemId)
        .then(response => response.json())
        .then(data => {
            // Populate modal with item details
            document.getElementById('modalBody').innerHTML = `
                <p><strong>Название:</strong> ${data.name}</p>
                <p><strong>Цвет:</strong> ${data.color}</p>
                <p><strong>Категория:</strong> ${data.category}</p>
                <p><strong>Стиль:</strong> ${data.style}</p>
                <p><strong>Описание:</strong> ${data.description}</p>
                <p><strong>Количество пользователей:</strong> ${data.user_count}</p>
                <img src="${data.photo}" alt="${data.name}" class="modal-image">
            `;
            // Show modal
            document.getElementById('itemModal').style.display = "block";
        });
}

function closeModal() {
    document.getElementById('itemModal').style.display = "none";
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    if (event.target == document.getElementById('itemModal')) {
        closeModal();
    }
}
uploads
</script>

</body>
</html>