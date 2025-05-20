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

<h2>Final Settlement</h2>

<?php if($exit_id): 
    // Get exit request details
    $stmt = $conn->prepare("SELECT er.*, e.name as employee_name, e.employee_id, e.hire_date 
                          FROM exit_requests er 
                          JOIN employees e ON er.employee_id = e.id 
                          WHERE er.id = ?");
    $stmt->execute([$exit_id]);
    $exit_request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if settlement already recorded
    $stmt = $conn->prepare("SELECT * FROM final_settlements WHERE exit_request_id = ?");
    $stmt->execute([$exit_id]);
    $settlement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calculate months worked
    $hire_date = new DateTime($exit_request['hire_date']);
    $last_day = new DateTime($exit_request['last_working_day']);
    $months_worked = $hire_date->diff($last_day)->m + ($hire_date->diff($last_day)->y * 12);
?>

<div class="card mt-4">
    <div class="card-header">
        Final Settlement for <?php echo $exit_request['employee_name']; ?> (ID: <?php echo $exit_request['employee_id']; ?>)
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Employee Details</h5>
                <ul class="list-group">
                    <li class="list-group-item">Hire Date: <?php echo $exit_request['hire_date']; ?></li>
                    <li class="list-group-item">Last Working Day: <?php echo $exit_request['last_working_day']; ?></li>
                    <li class="list-group-item">Total Months Worked: <?php echo $months_worked; ?></li>
                </ul>
            </div>
        </div>
        
        <?php if($settlement): ?>
            <div class="alert alert-info">
                Final settlement already processed on <?php echo $settlement['settlement_date']; ?>
            </div>
            <ul class="list-group mb-3">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Pending Salary Months
                    <span class="badge bg-primary"><?php echo $settlement['pending_salary_months']; ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Other Dues
                    <span class="badge bg-primary">$<?php echo number_format($settlement['other_dues'], 2); ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Settlement Status
                    <span class="badge bg-<?php echo $settlement['settlement_completed']?'success':'warning'; ?>">
                        <?php echo $settlement['settlement_completed']?'Completed':'Pending'; ?>
                    </span>
                </li>
            </ul>
            
            <?php if(!$settlement['settlement_completed']): ?>
                <form action="processes/process_settlement.php" method="POST">
                    <input type="hidden" name="settlement_id" value="<?php echo $settlement['id']; ?>">
                    <button type="submit" class="btn btn-success" name="complete_settlement">Mark as Completed</button>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <form action="processes/process_settlement.php" method="POST">
                <input type="hidden" name="exit_request_id" value="<?php echo $exit_id; ?>">
                <input type="hidden" name="months_worked" value="<?php echo $months_worked; ?>">
                
                <div class="mb-3">
                    <label for="last_salary_date" class="form-label">Last Salary Paid Until</label>
                    <input type="date" class="form-control" id="last_salary_date" name="last_salary_date" required>
                </div>
                <div class="mb-3">
                    <label for="pending_salary_months" class="form-label">Pending Salary Months</label>
                    <input type="number" class="form-control" id="pending_salary_months" name="pending_salary_months" min="0" required>
                </div>
                <div class="mb-3">
                    <label for="other_dues" class="form-label">Other Dues (if any)</label>
                    <input type="number" step="0.01" class="form-control" id="other_dues" name="other_dues" min="0" value="0">
                </div>
                <button type="submit" class="btn btn-primary" name="submit_settlement">Process Settlement</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>

<div class="card mt-4">
    <div class="card-header">
        Pending Settlements
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee</th>
                    <th>Last Working Day</th>
                    <th>Settlement Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT er.id, e.name as employee_name, er.last_working_day, 
                                     fs.id as settlement_id, fs.settlement_completed
                                     FROM exit_requests er
                                     JOIN employees e ON er.employee_id = e.id
                                     LEFT JOIN final_settlements fs ON er.id = fs.exit_request_id
                                     WHERE er.status != 'Completed' OR (fs.id IS NOT NULL AND fs.settlement_completed = 0)
                                     ORDER BY er.last_working_day");
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $settlement_status = $row['settlement_id'] ? 
                        ($row['settlement_completed'] ? 
                            "<span class='badge bg-success'>Completed</span>" : 
                            "<span class='badge bg-warning'>Pending Completion</span>") : 
                        "<span class='badge bg-danger'>Not Processed</span>";
                    
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['employee_name']}</td>
                            <td>{$row['last_working_day']}</td>
                            <td>{$settlement_status}</td>
                            <td>
                                <a href='final_settlement.php?exit_id={$row['id']}' class='btn btn-sm btn-info'>View/Process</a>
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