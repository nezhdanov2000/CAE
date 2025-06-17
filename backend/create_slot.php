<?php

require_once 'db.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tutor') {
    die("❌ Access denied. Please login as a tutor.");
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
            echo 'Please fill in all fields.';
        } else {
            echo 'Please fill in all fields.';
        }
        exit();
    }

    // 1. Check if the course exists
    $stmt = $conn->prepare("SELECT Course_ID FROM Course WHERE Course_name = ?");
    $stmt->bind_param("s", $course_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $course_id = $row['Course_ID'];
    } else {
        // 2. Add the course
        $insert_course = $conn->prepare("INSERT INTO Course (Course_name) VALUES (?)");
        $insert_course->bind_param("s", $course_name);
        $insert_course->execute();
        $course_id = $conn->insert_id;
        $insert_course->close();
    }
    $stmt->close();

    // 3. Add the timeslot
    $stmt = $conn->prepare('INSERT INTO Timeslot (Date, Start_Time, End_Time, Course_ID) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('sssi', $date, $start_time, $end_time, $course_id);
    $success = $stmt->execute();
    $timeslot_id = $conn->insert_id;
    $stmt->close();

    if ($success) {
        // 4. Link the slot with the tutor
        $stmt2 = $conn->prepare('INSERT INTO Tutor_Creates (Tutor_ID, Timeslot_ID) VALUES (?, ?)');
        $stmt2->bind_param('ii', $tutor_id, $timeslot_id);
        $success2 = $stmt2->execute();
        $stmt2->close();
        if ($success2) {
            if (is_fetch_request()) {
                echo 'Timeslot successfully created!';
            } else {
                echo 'Timeslot successfully created!';
            }
        } else {
            if (is_fetch_request()) {
                http_response_code(500);
                echo 'Error linking the tutor.';
            } else {
                echo 'Error linking the tutor.';
            }
        }
    } else {
        if (is_fetch_request()) {
            http_response_code(500);
            echo 'Error creating the timeslot.';
        } else {
            echo 'Error creating the timeslot.';
        }
    }
    exit();
}

$conn->close();
