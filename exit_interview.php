<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/header.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$exit_id = isset($_GET['exit_id']) ? $_GET['exit_id'] : null;
?>

<h2>Exit Interviews</h2>

<?php if($exit_id): 
    // Get exit request details
    $stmt = $conn->prepare("SELECT er.*, e.name as employee_name, e.employee_id 
                          FROM exit_requests er 
                          JOIN employees e ON er.employee_id = e.id 
                          WHERE er.id = ?");
    $stmt->execute([$exit_id]);
    $exit_request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if interview already scheduled
    $stmt = $conn->prepare("SELECT * FROM exit_interviews WHERE exit_request_id = ?");
    $stmt->execute([$exit_id]);
    $interview = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="card mt-4">
    <div class="card-header">
        Schedule Exit Interview for <?php echo $exit_request['employee_name']; ?> (ID: <?php echo $exit_request['employee_id']; ?>)
    </div>
    <div class="card-body">
        <?php if($interview): ?>
            <div class="alert alert-info">
                Interview already scheduled for <?php echo date('M d, Y h:i A', strtotime($interview['interview_date'])); ?>
                with manager ID <?php echo $interview['manager_id']; ?>
            </div>
            
            <?php if($interview['conducted']): ?>
                <div class="alert alert-success">
                    Interview already conducted. Feedback: <?php echo $interview['feedback']; ?>
                </div>
            <?php else: ?>
                <form action="processes/process_interview.php" method="POST">
                    <input type="hidden" name="interview_id" value="<?php echo $interview['id']; ?>">
                    <div class="mb-3">
                        <label for="feedback" class="form-label">Interview Feedback</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" name="submit_feedback">Submit Feedback</button>I'll continue with the remaining code for the HRM Exit Management System. Here's the continuation from where we left off:

                </form>
            <?php endif; ?>
        <?php else: ?>
            <form action="processes/process_interview.php" method="POST">
                <input type="hidden" name="exit_request_id" value="<?php echo $exit_id; ?>">
                <div class="mb-3">
                    <label for="interview_date" class="form-label">Interview Date & Time</label>
                    <input type="datetime-local" class="form-control" id="interview_date" name="interview_date" required>
                </div>
                <div class="mb-3">
                    <label for="manager_id" class="form-label">Assign Manager</label>
                    <select class="form-select" id="manager_id" name="manager_id" required>
                        <option value="">Select Manager</option>
                        <?php
                        $stmt = $conn->query("SELECT id, name, department FROM managers");
                        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='{$row['id']}'>{$row['name']} ({$row['department']})</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" name="schedule_interview">Schedule Interview</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>

<div class="card mt-4">
    <div class="card-header">
        Upcoming Exit Interviews
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee</th>
                    <th>Interview Date</th>
                    <th>Manager</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT ei.id, e.name as employee_name, ei.interview_date, m.name as manager_name, 
                                     ei.conducted, er.id as exit_id
                                     FROM exit_interviews ei
                                     JOIN exit_requests er ON ei.exit_request_id = er.id
                                     JOIN employees e ON er.employee_id = e.id
                                     JOIN managers m ON ei.manager_id = m.id
                                     ORDER BY ei.interview_date");
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['employee_name']}</td>
                            <td>".date('M d, Y h:i A', strtotime($row['interview_date']))."</td>
                            <td>{$row['manager_name']}</td>
                            <td><span class='badge bg-".($row['conducted']?'success':'warning')."'>".($row['conducted']?'Completed':'Pending')."</span></td>
                            <td>
                                <a href='exit_interview.php?exit_id={$row['exit_id']}' class='btn btn-sm btn-info'>View</a>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>