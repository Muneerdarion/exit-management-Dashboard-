<?php
session_start();
require_once '../includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if(isset($_POST['submit_settlement'])) {
    // Process final settlement
    $exit_request_id = $_POST['exit_request_id'];
    $last_salary_date = $_POST['last_salary_date'];
    $pending_salary_months = $_POST['pending_salary_months'];
    $other_dues = $_POST['other_dues'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO final_settlements (exit_request_id, last_salary_date, pending_salary_months, other_dues) 
                              VALUES (?, ?, ?, ?)");
        $stmt->execute([$exit_request_id, $last_salary_date, $pending_salary_months, $other_dues]);
        
        $_SESSION['message'] = "Final settlement processed successfully";
        header("Location: ../final_settlement.php?exit_id=".$exit_request_id);
        exit;
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../final_settlement.php?exit_id=".$exit_request_id);
        exit;
    }
} elseif(isset($_POST['complete_settlement'])) {
    // Mark settlement as completed
    $settlement_id = $_POST['settlement_id'];
    
    try {
        $stmt = $conn->prepare("UPDATE final_settlements SET settlement_completed = 1, settlement_date = CURDATE() WHERE id = ?");
        $stmt->execute([$settlement_id]);
        
        // Also mark exit request as completed
        $stmt = $conn->prepare("UPDATE exit_requests SET status = 'Completed' 
                              WHERE id = (SELECT exit_request_id FROM final_settlements WHERE id = ?)");
        $stmt->execute([$settlement_id]);
        
        $_SESSION['message'] = "Settlement marked as completed";
        header("Location: ../final_settlement.php?exit_id=".$_GET['exit_id']);
        exit;
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../final_settlement.php?exit_id=".$_GET['exit_id']);
        exit;
    }
}
?>