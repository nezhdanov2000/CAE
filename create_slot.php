<?php

require_once 'db.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tutor') {
    die("❌ Доступ запрещён. Пожалуйста, войдите как преподаватель.");
}

$tutor_id = $_SESSION['user_id'];

$course_name = trim($_POST['course_name']);
$date = $_POST['date'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];


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
$insert_timeslot = $conn->prepare(
    "INSERT INTO Timeslot (Date, Start_Time, End_Time, Course_ID) VALUES (?, ?, ?, ?)"
);
$insert_timeslot->bind_param("sssi", $date, $start_time, $end_time, $course_id);
$insert_timeslot->execute();
$timeslot_id = $conn->insert_id;
$insert_timeslot->close();

// 4. Связываем слот с преподавателем
$insert_link = $conn->prepare("INSERT INTO Tutor_Creates (Tutor_ID, Timeslot_ID) VALUES (?, ?)");
$insert_link->bind_param("ii", $tutor_id, $timeslot_id);
$insert_link->execute();
$insert_link->close();

// 5. Если преподаватель ещё не связан с курсом — добавим
$check = $conn->prepare("SELECT 1 FROM Tutoring WHERE Tutor_ID = ? AND Course_ID = ?");
$check->bind_param("ii", $tutor_id, $course_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    $insert_tutoring = $conn->prepare("INSERT INTO Tutoring (Tutor_ID, Course_ID) VALUES (?, ?)");
    $insert_tutoring->bind_param("ii", $tutor_id, $course_id);
    $insert_tutoring->execute();
    $insert_tutoring->close();
}

$check->close();
$conn->close();

echo "✅ Таймслот успешно создан.";
?>
