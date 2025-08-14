<?php
require_once 'backend/common/db.php';

echo "<h1>Инициализация базы данных CAE</h1>";

// Проверяем подключение
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
echo "<p style='color: green;'>✓ Подключение к базе данных успешно</p>";

// Создаем базу данных если её нет
$conn->query("CREATE DATABASE IF NOT EXISTS cae_database");
$conn->select_db("cae_database");

// Читаем SQL файл и выполняем его
$sql_file = 'backend/cae_structure.sql';
if (file_exists($sql_file)) {
    $sql = file_get_contents($sql_file);
    
    // Разбиваем на отдельные запросы
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            if ($conn->query($query)) {
                echo "<p style='color: green;'>✓ Выполнен запрос: " . substr($query, 0, 50) . "...</p>";
            } else {
                echo "<p style='color: red;'>✗ Ошибка в запросе: " . $conn->error . "</p>";
            }
        }
    }
} else {
    echo "<p style='color: red;'>✗ Файл SQL не найден: " . $sql_file . "</p>";
}

// Добавляем тестовые данные
echo "<h2>Добавление тестовых данных...</h2>";

// Добавляем курсы
$courses = [
    ['Математика'],
    ['Физика'],
    ['Химия'],
    ['Программирование'],
    ['Английский язык']
];

foreach ($courses as $course) {
    $stmt = $conn->prepare("INSERT IGNORE INTO Course (Course_name) VALUES (?)");
    $stmt->bind_param('s', $course[0]);
    if ($stmt->execute()) {
        echo "<p>✓ Добавлен курс: " . $course[0] . "</p>";
    }
    $stmt->close();
}

// Добавляем студентов
$students = [
    ['student1@test.com', 'password123', 'Иван', 'Иванов'],
    ['student2@test.com', 'password123', 'Петр', 'Петров'],
    ['student3@test.com', 'password123', 'Анна', 'Сидорова'],
    ['student4@test.com', 'password123', 'Мария', 'Козлова'],
    ['student5@test.com', 'password123', 'Алексей', 'Смирнов']
];

foreach ($students as $student) {
    $stmt = $conn->prepare("INSERT IGNORE INTO Student (Email, Password, Name, Surname) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $student[0], $student[1], $student[2], $student[3]);
    if ($stmt->execute()) {
        echo "<p>✓ Добавлен студент: " . $student[2] . " " . $student[3] . "</p>";
    }
    $stmt->close();
}

// Добавляем преподавателей
$tutors = [
    ['tutor1@test.com', 'password123', 'Дмитрий', 'Учитель'],
    ['tutor2@test.com', 'password123', 'Елена', 'Преподаватель'],
    ['tutor3@test.com', 'password123', 'Сергей', 'Профессор']
];

foreach ($tutors as $tutor) {
    $stmt = $conn->prepare("INSERT IGNORE INTO Tutor (Email, Password, Name, Surname) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $tutor[0], $tutor[1], $tutor[2], $tutor[3]);
    if ($stmt->execute()) {
        echo "<p>✓ Добавлен преподаватель: " . $tutor[2] . " " . $tutor[3] . "</p>";
    }
    $stmt->close();
}

// Добавляем связи преподаватель-курс
$tutor_courses = [
    [1, 1], // Дмитрий - Математика
    [1, 2], // Дмитрий - Физика
    [2, 3], // Елена - Химия
    [2, 4], // Елена - Программирование
    [3, 5]  // Сергей - Английский
];

foreach ($tutor_courses as $tc) {
    $stmt = $conn->prepare("INSERT IGNORE INTO Tutoring (Tutor_ID, Course_ID) VALUES (?, ?)");
    $stmt->bind_param('ii', $tc[0], $tc[1]);
    if ($stmt->execute()) {
        echo "<p>✓ Добавлена связь преподаватель-курс: " . $tc[0] . "-" . $tc[1] . "</p>";
    }
    $stmt->close();
}

// Добавляем таймслоты
$timeslots = [
    [1, '2024-01-15', '09:00:00', '10:30:00'],
    [2, '2024-01-15', '11:00:00', '12:30:00'],
    [3, '2024-01-16', '14:00:00', '15:30:00'],
    [4, '2024-01-16', '16:00:00', '17:30:00'],
    [5, '2024-01-17', '10:00:00', '11:30:00']
];

foreach ($timeslots as $ts) {
    $stmt = $conn->prepare("INSERT IGNORE INTO Timeslot (Course_ID, Date, Start_Time, End_Time) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $ts[0], $ts[1], $ts[2], $ts[3]);
    if ($stmt->execute()) {
        echo "<p>✓ Добавлен таймслот: " . $ts[1] . " " . $ts[2] . "-" . $ts[3] . "</p>";
    }
    $stmt->close();
}

// Добавляем записи на занятия
$appointments = [
    [1, 1, 1, 'Аудитория 101'],
    [2, 2, 2, 'Аудитория 102'],
    [3, 3, 3, 'Лаборатория 201']
];

foreach ($appointments as $app) {
    $stmt = $conn->prepare("INSERT IGNORE INTO Appointment (Tutor_ID, Group_ID, Timeslot_ID, Location) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('iiis', $app[0], $app[1], $app[2], $app[3]);
    if ($stmt->execute()) {
        echo "<p>✓ Добавлена запись: " . $app[3] . "</p>";
    }
    $stmt->close();
}

// Добавляем логи администратора
$admin_logs = [
    ['Вход в систему', 'Успешная авторизация'],
    ['Просмотр статистики', 'Просмотрена общая статистика системы'],
    ['Проверка данных', 'Проверены данные пользователей']
];

foreach ($admin_logs as $log) {
    $stmt = $conn->prepare("INSERT INTO Admin_Log (Admin_ID, Action, Details) VALUES (1, ?, ?)");
    $stmt->bind_param('ss', $log[0], $log[1]);
    if ($stmt->execute()) {
        echo "<p>✓ Добавлен лог: " . $log[0] . "</p>";
    }
    $stmt->close();
}

echo "<h2>Инициализация завершена!</h2>";
echo "<p><a href='test_db.php'>Проверить данные</a></p>";

$conn->close();
?>
