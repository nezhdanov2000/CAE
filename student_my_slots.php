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

$query = "
    SELECT t.Timeslot_ID, t.Date, t.Start_Time, t.End_Time, c.Course_name,
        CONCAT(u.Name, ' ', u.Surname) AS Tutor_Name
    FROM Student_Choice sc
    JOIN Timeslot t ON sc.Timeslot_ID = t.Timeslot_ID
    JOIN Course c ON t.Course_ID = c.Course_ID
    JOIN Tutor_Creates tc ON t.Timeslot_ID = tc.Timeslot_ID
    JOIN Tutor u ON tc.Tutor_ID = u.Tutor_ID
    WHERE sc.Student_ID = ?
    ORDER BY t.Date, t.Start_Time
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$slots = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($slots);
