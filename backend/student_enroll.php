<?php
session_start();
require_once 'db.php';

function is_fetch_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['user_id'] ?? null;
    $timeslot_id = $_POST['timeslot_id'] ?? '';

    if (!$student_id || !$timeslot_id) {
        if (is_fetch_request()) {
            http_response_code(400);
            echo 'Please select a slot.';
        } else {
            echo 'Please select a slot.';
        }
        exit();
    }

    // Check: is the student already enrolled in this slot
    $stmt = $conn->prepare('SELECT 1 FROM Student_Choice WHERE Student_ID = ? AND Timeslot_ID = ?');
    $stmt->bind_param('ii', $student_id, $timeslot_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        if (is_fetch_request()) {
            http_response_code(409);
            echo 'You are already enrolled in this slot.';
        } else {
            echo 'You are already enrolled in this slot.';
        }
        exit();
    }
    $stmt->close();

    // Enroll the student in the slot
    $stmt = $conn->prepare('INSERT INTO Student_Choice (Student_ID, Timeslot_ID) VALUES (?, ?)');
    $stmt->bind_param('ii', $student_id, $timeslot_id);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        if (is_fetch_request()) {
            echo 'You have successfully enrolled in the slot!';
        } else {
            echo 'You have successfully enrolled in the slot!';
        }
    } else {
        if (is_fetch_request()) {
            http_response_code(500);
            echo 'Error enrolling in the slot.';
        } else {
            echo 'Error enrolling in the slot.';
        }
    }
    exit();
}

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['error' => 'No access']);
    exit();
}

$student_id = $_SESSION['user_id'];
$timeslot_id = (int)($_POST['timeslot_id'] ?? 0);
$group_id = $timeslot_id;

if (!$timeslot_id) {
    echo json_encode(['error' => 'timeslot_id is not passed']);
    exit();
}

// Check: is the student already enrolled in this slot
$check = $conn->prepare("SELECT 1 FROM Student_Choice WHERE Student_ID = ? AND Timeslot_ID = ?");
$check->bind_param("ii", $student_id, $timeslot_id);
$check->execute();
$res = $check->get_result();
if ($res->num_rows > 0) {
    echo json_encode(['error' => 'Вы уже записаны на этот слот']);
    exit();
}

// Enroll the student in the slot
$insert = $conn->prepare("INSERT INTO Student_Choice (Student_ID, Timeslot_ID) VALUES (?, ?)");
$insert->bind_param("ii", $student_id, $timeslot_id);
$insert->execute();
$insert->close();

// Get the Tutor_ID
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

// If Appointment does not exist, create it
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

// Add the student to the group (group = timeslot_id)
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
