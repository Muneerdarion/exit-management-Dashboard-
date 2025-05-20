<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/header.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
?>

<h2>Exit Requests</h2>

<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <button class="btn btn-sm btn-outline-primary me-2 filter-btn" data-status="all">All</button>
            <button class="btn btn-sm btn-outline-warning me-2 filter-btn" data-status="Pending">Pending</button>
            <button class="btn btn-sm btn-outline-success filter-btn" data-status="Completed">Completed</button>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newExitModal">New Exit Request</button>
    </div>
    <div class="card-body">
        <table class="table table-striped" id="exitRequestsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee</th>
                    <th>Requested On</th>
                    <th>Last Working Day</th>
                    <th>Status</th>
                    <th>Reason</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT er.id, e.employee_id, e.name, er.request_date, er.last_working_day, er.status, er.reason 
                        FROM exit_requests er 
                        JOIN employees e ON er.employee_id = e.id";
                
                if($status_filter != 'all') {
                    $sql .= " WHERE er.status = :status";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':status', $status_filter);
                    $stmt->execute();
                } else {
                    $stmt = $conn->query($sql);
                }
                
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['name']} (ID: {$row['employee_id']})</td>
                            <td>{$row['request_date']}</td>
                            <td>{$row['last_working_day']}</td>
                            <td><span class='badge bg-".($row['status']=='Pending'?'warning':'success')."'>{$row['status']}</span></td>
                            <td>{$row['reason']}</td>
                            <td>
                                <a href='exit_interview.php?exit_id={$row['id']}' class='btn btn-sm btn-info'>Schedule Interview</a>
                                <a href='processes/process_exit.php?action=complete&id={$row['id']}' class='btn btn-sm btn-success'>Complete</a>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- New Exit Modal -->
<div class="modal fade" id="newExitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Exit Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="processes/process_exit.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="employee_id" class="form-label">Employee</label>
                        <select class="form-select" id="employee_id" name="employee_id" required>
                            <option value="">Select Employee</option>
                            <?php
                            $stmt = $conn->query("SELECT id, name, employee_id FROM employees");
                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='{$row['id']}'>{$row['name']} (ID: {$row['employee_id']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="last_working_day" class="form-label">Last Working Day</label>
                        <input type="date" class="form-control" id="last_working_day" name="last_working_day" required>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Exit</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="submit">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Filter buttons functionality
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const status = this.getAttribute('data-status');
        window.location.href = `exit_requests.php?status=${status}`;
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>