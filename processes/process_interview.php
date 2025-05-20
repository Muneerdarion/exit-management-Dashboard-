<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Handle interview scheduling
if(isset($_POST['schedule_interview'])) {
    // Validate and sanitize inputs
    $exit_request_id = filter_input(INPUT_POST, 'exit_request_id', FILTER_SANITIZE_NUMBER_INT);
    $interview_date = filter_input(INPUT_POST, 'interview_date', FILTER_SANITIZE_STRING);
    $manager_id = filter_input(INPUT_POST, 'manager_id', FILTER_SANITIZE_NUMBER_INT);
    
    // Basic validation
    if(empty($exit_request_id) || empty($interview_date) || empty($manager_id)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ../exit_interview.php?exit_id=".$exit_request_id);
        exit;
    }
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // 1. Schedule the interview
        $stmt = $conn->prepare("INSERT INTO exit_interviews 
                              (exit_request_id, interview_date, manager_id) 
                              VALUES (?, ?, ?)");
        $stmt->execute([$exit_request_id, $interview_date, $manager_id]);
        
        // 2. Update exit request status to 'Interview Scheduled'
        $stmt = $conn->prepare("UPDATE exit_requests 
                              SET status = 'Interview Scheduled' 
                              WHERE id = ?");
        $stmt->execute([$exit_request_id]);
        
        // 3. Get manager details for notification (in a real system, you'd send an email)
        $stmt = $conn->prepare("SELECT name, email FROM managers WHERE id = ?");
        $stmt->execute([$manager_id]);
        $manager = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Commit transaction
        $conn->commit();
        
        // Store success message and manager details for display
        $_SESSION['message'] = "Exit interview scheduled successfully with ".$manager['name'];
        $_SESSION['interview_details'] = [
            'date' => $interview_date,
            'manager' => $manager['name'],
            'email' => $manager['email']
        ];
        
        header("Location: ../exit_interview.php?exit_id=".$exit_request_id);
        exit;
        
    } catch(PDOException $e) {
        // Rollback on error
        $conn->rollBack();
        $_SESSION['error'] = "Error scheduling interview: " . $e->getMessage();
        header("Location: ../exit_interview.php?exit_id=".$exit_request_id);
        exit;
    }
} 
// Handle interview feedback submission
elseif(isset($_POST['submit_feedback'])) {
    $interview_id = filter_input(INPUT_POST, 'interview_id', FILTER_SANITIZE_NUMBER_INT);
    $feedback = filter_input(INPUT_POST, 'feedback', FILTER_SANITIZE_STRING);
    $exit_request_id = filter_input(INPUT_GET, 'exit_id', FILTER_SANITIZE_NUMBER_INT);
    
    if(empty($interview_id) || empty($feedback)) {
        $_SESSION['error'] = "Feedback cannot be empty";
        header("Location: ../exit_interview.php?exit_id=".$exit_request_id);
        exit;
    }
    
    try {
        // Update interview with feedback and mark as conducted
        $stmt = $conn->prepare("UPDATE exit_interviews 
                              SET feedback = ?, conducted = 1 
                              WHERE id = ?");
        $stmt->execute([$feedback, $interview_id]);
        
        // In a real system, you might update the exit request status here
        // $stmt = $conn->prepare("UPDATE exit_requests SET status = 'Interview Completed' WHERE id = ?");
        // $stmt->execute([$exit_request_id]);
        
        $_SESSION['message'] = "Interview feedback submitted successfully";
        header("Location: ../exit_interview.php?exit_id=".$exit_request_id);
        exit;
        
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error submitting feedback: " . $e->getMessage();
        header("Location: ../exit_interview.php?exit_id=".$exit_request_id);
        exit;
    }
}