<?php

require_once 'db.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tutor') {
    die("❌ Доступ запрещён. Пожалуйста, войдите как преподаватель.");
}

$tutor_id = $_SESSION['user_id'];

$course_name = trim($_POST['course_name'] ?? '');
$date = $_POST['date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';

function is_fetch_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $course_name = trim($_POST['course_name'] ?? '');
    $tutor_id = $_SESSION['user_id'] ?? null;

    if (!$date || !$start_time || !$end_time || !$course_name || !$tutor_id) {
        if (is_fetch_request()) {
            http_response_code(400);
            echo 'Пожалуйста, заполните все поля.';
        } else {
            echo 'Пожалуйста, заполните все поля.';
        }
        exit();
    }

    // 1. Проверка: существует ли курс
    $stmt = $conn->prepare("SELECT Course_ID FROM Course WHERE Course_name = ?");
    $stmt->bind_param("s", $course_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $course_id = $row['Course_ID'];
    } else {
        // 2. Добавляем курс
        $insert_course = $conn->prepare("INSERT INTO Course (Course_name) VALUES (?)");
        $insert_course->bind_param("s", $course_name);
        $insert_course->execute();
        $course_id = $conn->insert_id;
        $insert_course->close();
    }
    $stmt->close();

    // 3. Добавляем таймслот
    $stmt = $conn->prepare('INSERT INTO Timeslot (Date, Start_Time, End_Time, Course_ID) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('sssi', $date, $start_time, $end_time, $course_id);
    $success = $stmt->execute();
    $timeslot_id = $conn->insert_id;
    $stmt->close();

    if ($success) {
        // 4. Связываем слот с преподавателем
        $stmt2 = $conn->prepare('INSERT INTO Tutor_Creates (Tutor_ID, Timeslot_ID) VALUES (?, ?)');
        $stmt2->bind_param('ii', $tutor_id, $timeslot_id);
        $success2 = $stmt2->execute();
        $stmt2->close();
        if ($success2) {
            if (is_fetch_request()) {
                echo 'Таймслот успешно создан!';
            } else {
                echo 'Таймслот успешно создан!';
            }
        } else {
            if (is_fetch_request()) {
                http_response_code(500);
                echo 'Ошибка при привязке преподавателя.';
            } else {
                echo 'Ошибка при привязке преподавателя.';
            }
        }
    } else {
        if (is_fetch_request()) {
            http_response_code(500);
            echo 'Ошибка при создании таймслота.';
        } else {
            echo 'Ошибка при создании таймслота.';
        }
    }
    exit();
}

$conn->close();
