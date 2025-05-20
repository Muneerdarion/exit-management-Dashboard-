<?php
session_start();
require_once '../includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if(isset($_POST['submit'])) {
    // Process new exit request
    $employee_id = $_POST['employee_id'];
    $last_working_day = $_POST['last_working_day'];
    $reason = $_POST['reason'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO exit_requests (employee_id, request_date, last_working_day, reason) 
                              VALUES (?, CURDATE(), ?, ?)");
        $stmt->execute([$employee_id, $last_working_day, $reason]);
        
        $_SESSION['message'] = "Exit request submitted successfully";
        header("Location: ../exit_requests.php");
        exit;
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../exit_requests.php");
        exit;
    }
} elseif(isset($_GET['action']) && $_GET['action'] == 'complete' && isset($_GET['id'])) {
    // Mark exit as completed
    $exit_id = $_GET['id'];
    
    try {
        $stmt = $conn->prepare("UPDATE exit_requests SET status = 'Completed' WHERE id = ?");
        $stmt->execute([$exit_id]);
        
        $_SESSION['message'] = "Exit process marked as completed";
        header("Location: ../exit_requests.php");
        exit;
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();


session_start();
require_once '../includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if(isset($_POST['schedule_interview'])) {
    // Schedule new exit interview
    $exit_request_id = $_POST['exit_request_id'];
    $interview_date = $_POST['interview_date'];
    $manager_id = $_POST['manager_id'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO exit_interviews (exit_request_id, interview_date, manager_id) 
                              VALUES (?, ?, ?)");
        $stmt->execute([$exit_request_id, $interview_date, $manager_id]);
        
        // Update exit request status
        $stmt = $conn->prepare("UPDATE exit_requests SET status = 'Interview Scheduled' WHERE id = ?");
        $stmt->execute([$exit_request_id]);
        
        $_SESSION['message'] = "Exit interview scheduled successfully";
        header("Location: ../exit_interview.php?exit_id=".$exit_request_id);
        exit;
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../exit_interview.php?exit_id=".$exit_request_id);
        exit;
    }
} elseif(isset($_POST['submit_feedback'])) {
    // Submit interview feedback
    $interview_id = $_POST['interview_id'];
    $feedback = $_POST['feedback'];
    
    try {
        $stmt = $conn->prepare("UPDATE exit_interviews SET feedback = ?, conducted = 1 WHERE id = ?");
        $stmt->execute([$feedback, $interview_id]);
        
        $_SESSION['message'] = "Interview feedback submitted successfully";
        header("Location: ../exit_interview.php?exit_id=".$_GET['exit_id']);
        exit;
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../exit_interview.php?exit_id=".$_GET['exit_id']);
        exit;
    }
}
?>