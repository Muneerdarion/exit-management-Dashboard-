<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/header.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get counts for dashboard
$pending_exits = $conn->query("SELECT COUNT(*) FROM exit_requests WHERE status='Pending'")->fetchColumn();
$completed_exits = $conn->query("SELECT COUNT(*) FROM exit_requests WHERE status='Completed'")->fetchColumn();
?>

<h2>Dashboard</h2>
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Pending Exits</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $pending_exits; ?></h5>
                <a href="exit_requests.php?status=Pending" class="btn btn-light">View Details</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Completed Exits</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $completed_exits; ?></h5>
                <a href="exit_requests.php?status=Completed" class="btn btn-light">View Details</a>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        Recent Exit Requests
    </div>
    <div class="card-body">
        <table class="table table-striped">
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
                $stmt = $conn->query("SELECT er.id, e.name, er.request_date, er.last_working_day, er.status, er.reason 
                                     FROM exit_requests er 
                                     JOIN employees e ON er.employee_id = e.id 
                                     ORDER BY er.request_date DESC LIMIT 5");
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['request_date']}</td>
                            <td>{$row['last_working_day']}</td>
                            <td><span class='badge bg-".($row['status']=='Pending'?'warning':'success')."'>{$row['status']}</span></td>
                            <td>{$row['reason']}</td>
                            <td>
                                <a href='exit_requests.php?action=view&id={$row['id']}' class='btn btn-sm btn-info'>View</a>
                            </td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>