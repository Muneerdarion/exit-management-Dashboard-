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

<h2>Asset Return</h2>

<?php if($exit_id): 
    // Get exit request details
    $stmt = $conn->prepare("SELECT er.*, e.name as employee_name, e.employee_id 
                          FROM exit_requests er 
                          JOIN employees e ON er.employee_id = e.id 
                          WHERE er.id = ?");
    $stmt->execute([$exit_id]);
    $exit_request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if asset return already recorded
    $stmt = $conn->prepare("SELECT * FROM asset_returns WHERE exit_request_id = ?");
    $stmt->execute([$exit_id]);
    $asset_return = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="card mt-4">
    <div class="card-header">
        Asset Return for <?php echo $exit_request['employee_name']; ?> (ID: <?php echo $exit_request['employee_id']; ?>)
    </div>
    <div class="card-body">
        <?php if($asset_return): ?>
            <div class="alert alert-info">
                Asset return already recorded on <?php echo $asset_return['return_date']; ?>
            </div>
            <ul class="list-group mb-3">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Laptop Returned
                    <span class="badge bg-<?php echo $asset_return['laptop_returned']?'success':'danger'; ?>">
                        <?php echo $asset_return['laptop_returned']?'Yes':'No'; ?>
                    </span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    ID Card Returned
                    <span class="badge bg-<?php echo $asset_return['id_card_returned']?'success':'danger'; ?>">
                        <?php echo $asset_return['id_card_returned']?'Yes':'No'; ?>
                    </span>
                </li>
                <?php if($asset_return['other_assets']): ?>
                <li class="list-group-item">
                    Other Assets: <?php echo $asset_return['other_assets']; ?>
                </li>
                <?php endif; ?>
            </ul>
        <?php else: ?>
            <form action="processes/process_assets.php" method="POST">
                <input type="hidden" name="exit_request_id" value="<?php echo $exit_id; ?>">
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="laptop_returned" name="laptop_returned">
                    <label class="form-check-label" for="laptop_returned">Laptop Returned</label>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="id_card_returned" name="id_card_returned">
                    <label class="form-check-label" for="id_card_returned">ID Card Returned</label>
                </div>
                <div class="mb-3">
                    <label for="other_assets" class="form-label">Other Assets (if any)</label>
                    <textarea class="form-control" id="other_assets" name="other_assets" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="return_date" class="form-label">Return Date</label>
                    <input type="date" class="form-control" id="return_date" name="return_date" required>
                </div>
                <button type="submit" class="btn btn-primary" name="submit_assets">Submit Asset Return</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>

<div class="card mt-4">
    <div class="card-header">
        Pending Asset Returns
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee</th>
                    <th>Last Working Day</th>
                    <th>Assets Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT er.id, e.name as employee_name, er.last_working_day, 
                                     ar.id as asset_id, ar.return_date
                                     FROM exit_requests er
                                     JOIN employees e ON er.employee_id = e.id
                                     LEFT JOIN asset_returns ar ON er.id = ar.exit_request_id
                                     WHERE er.status != 'Completed'
                                     ORDER BY er.last_working_day");
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $asset_status = $row['asset_id'] ? 
                        "<span class='badge bg-success'>Returned on {$row['return_date']}</span>" : 
                        "<span class='badge bg-warning'>Pending</span>";
                    
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['employee_name']}</td>
                            <td>{$row['last_working_day']}</td>
                            <td>{$asset_status}</td>
                            <td>
                                <a href='asset_return.php?exit_id={$row['id']}' class='btn btn-sm btn-info'>View/Update</a>
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