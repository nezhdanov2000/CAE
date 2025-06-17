<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['error' => 'Нет доступа']);
    exit();
}

$student_id = $_SESSION['user_id'];
$timeslot_id = (int) ($_POST['timeslot_id'] ?? 0);

if ($timeslot_id === 0) {
    echo json_encode(['error' => 'Не указан слот']);
    exit();
}

// Delete the record from Student_Choice
$delete = $conn->prepare("DELETE FROM Student_Choice WHERE Student_ID = ? AND Timeslot_ID = ?");
$delete->bind_param("ii", $student_id, $timeslot_id);
$delete->execute();
$delete->close();



// You can also delete from Student_Join or Appointment if needed

echo json_encode(['success' => true]);
