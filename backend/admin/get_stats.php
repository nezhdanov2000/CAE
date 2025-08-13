<?php
require_once 'auth.php';
require_once 'db.php';

// Проверяем авторизацию
require_admin_auth();

// Получаем статистику
$stats = [];

// Количество студентов
$stmt = $conn->prepare('SELECT COUNT(*) as count FROM Student');
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stats['students'] = $count;
$stmt->close();

// Количество преподавателей
$stmt = $conn->prepare('SELECT COUNT(*) as count FROM Tutor');
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stats['tutors'] = $count;
$stmt->close();

// Количество курсов
$stmt = $conn->prepare('SELECT COUNT(*) as count FROM Course');
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stats['courses'] = $count;
$stmt->close();

// Количество записей на сегодня
$stmt = $conn->prepare('SELECT COUNT(*) as count FROM Appointment a JOIN Timeslot t ON a.Timeslot_ID = t.Timeslot_ID WHERE DATE(t.Date) = CURDATE()');
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stats['appointments'] = $count;
$stmt->close();

send_json_success(['stats' => $stats]);

$conn->close();
?> 