<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['error' => 'Нет доступа']);
    exit();
}

$student_id = $_SESSION['user_id'];
$timeslot_id = (int)($_POST['timeslot_id'] ?? 0);
$group_id = $timeslot_id;

if (!$timeslot_id) {
    echo json_encode(['error' => 'Не передан timeslot_id']);
    exit();
}

// Проверка: уже записан?
$check = $conn->prepare("SELECT 1 FROM Student_Choice WHERE Student_ID = ? AND Timeslot_ID = ?");
$check->bind_param("ii", $student_id, $timeslot_id);
$check->execute();
$res = $check->get_result();
if ($res->num_rows > 0) {
    echo json_encode(['error' => 'Вы уже записаны на этот слот']);
    exit();
}

// Запись в Student_Choice
$insert = $conn->prepare("INSERT INTO Student_Choice (Student_ID, Timeslot_ID) VALUES (?, ?)");
$insert->bind_param("ii", $student_id, $timeslot_id);
$insert->execute();
$insert->close();

// Получаем Tutor_ID
$tutor_q = $conn->prepare("SELECT Tutor_ID FROM Tutor_Creates WHERE Timeslot_ID = ?");
$tutor_q->bind_param("i", $timeslot_id);
$tutor_q->execute();
$tutor_res = $tutor_q->get_result();
$tutor_id = $tutor_res->fetch_assoc()['Tutor_ID'] ?? null;
$tutor_q->close();

if (!$tutor_id) {
    echo json_encode(['error' => 'Преподаватель не найден']);
    exit();
}

// Если Appointment ещё не существует — создаём
$check_appointment = $conn->prepare("SELECT 1 FROM Appointment WHERE Timeslot_ID = ?");
$check_appointment->bind_param("i", $timeslot_id);
$check_appointment->execute();
$result = $check_appointment->get_result();

if ($result->num_rows === 0) {
    $location = "Online";
    $appointment = $conn->prepare("
        INSERT INTO Appointment (Tutor_ID, Group_ID, Timeslot_ID, Location)
        VALUES (?, ?, ?, ?)
    ");
    $appointment->bind_param("iiis", $tutor_id, $group_id, $timeslot_id, $location);
    $appointment->execute();
    $appointment->close();
}

// Добавляем студента в группу (группа = timeslot_id)
$check_join = $conn->prepare("SELECT 1 FROM Student_Join WHERE Student_ID = ? AND Group_ID = ?");
$check_join->bind_param("ii", $student_id, $group_id);
$check_join->execute();
$res = $check_join->get_result();
if ($res->num_rows === 0) {
    $insert_join = $conn->prepare("INSERT INTO Student_Join (Student_ID, Group_ID) VALUES (?, ?)");
    $insert_join->bind_param("ii", $student_id, $group_id);
    $insert_join->execute();
    $insert_join->close();
}

echo json_encode(['success' => true]);
