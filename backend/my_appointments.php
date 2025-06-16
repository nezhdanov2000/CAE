<?php
session_start();
require_once 'db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tutor') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$tutor_id = $_SESSION['user_id'];

$query = "
    SELECT 
        t.Timeslot_ID,
        t.Date,
        t.Start_Time,
        t.End_Time,
        COUNT(sc.Student_ID) AS Student_Count
    FROM Timeslot t
    JOIN Tutor_Creates tc ON t.Timeslot_ID = tc.Timeslot_ID
    JOIN Student_Choice sc ON t.Timeslot_ID = sc.Timeslot_ID
    WHERE tc.Tutor_ID = ?
    GROUP BY t.Timeslot_ID, t.Date, t.Start_Time, t.End_Time
    ORDER BY t.Date, t.Start_Time
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tutor_id);
$stmt->execute();

$result = $stmt->get_result();
$slots = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($slots);
?> 