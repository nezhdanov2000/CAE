<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode([]);
    exit();
}

$course_id = (int) ($_GET['course_id'] ?? 0);

$query = "
    SELECT t.Timeslot_ID, t.Date, t.Start_Time, t.End_Time,
           CONCAT(u.Name, ' ', u.Surname) AS Tutor_Name
    FROM Timeslot t
    JOIN Tutor_Creates tc ON t.Timeslot_ID = tc.Timeslot_ID
    JOIN Tutor u ON tc.Tutor_ID = u.Tutor_ID
    WHERE t.Course_ID = ?
    ORDER BY t.Date, t.Start_Time
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);
$stmt->execute();

$result = $stmt->get_result();
$slots = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($slots);
