<?php
session_start();
require_once '../config/Database.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header('Location: ../index.php'); 
    exit;
}

$db = (new Database())->connect();
$success = '';
$errors = [];

// Handle status update (Approve / Reject)
if (isset($_POST['action_type']) && isset($_POST['application_id'])) {
    $application_id = $_POST['application_id'];
    $status = $_POST['action_type'] === 'approve' ? 'Approved' : 'Rejected';

    $stmt = $db->prepare("UPDATE applications SET status=? WHERE application_id=?");
    $stmt->execute([$status, $application_id]);
    $success = "Application status updated to $status!";
}

// Handle edit/update from modal
if(isset($_POST['update_application'])){
    $application_id = $_POST['application_id'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    $stmt = $db->prepare("UPDATE applications SET status=?, remarks=? WHERE application_id=?");
    $stmt->execute([$status, $remarks, $application_id]);
    $success = "Application updated successfully!";
}

// Fetch applications
$applications = $db->query("
    SELECT a.application_id, st.first_name, st.last_name, sch.name AS scholarship_name,
           a.remarks, a.status, a.date_applied
    FROM applications a
    JOIN students st ON a.student_id = st.student_id
    JOIN scholarships sch ON a.scholarship_id = sch.scholarship_id
    ORDER BY a.date_applied DESC
")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="container mt-4">
    <h3>Manage Applications</h3>

    <?php if($success) echo '<div class="alert alert-success">'.$success.'</div>'; ?>
    <?php foreach($errors as $e) echo '<div class="alert alert-danger">'.$e.'</div>'; ?>

    <!-- Back button (go to Admin Dashboard) -->
    <button type="button" onclick="window.location.href='dashboard.php'" class="btn btn-secondary mb-3">← Back</button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Student</th>
                <th>Scholarship</th>
                <th>Remarks</th>
                <th>Status</th>
                <th>Date Applied</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($applications as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['first_name'].' '.$a['last_name']) ?></td>
            <td><?= htmlspecialchars($a['scholarship_name']) ?></td>
            <td><?= htmlspecialchars($a['remarks']) ?></td>
            <td>
                <?php 
                $badgeClass = match($a['status']) {
                    'Approved' => 'bg-success',
                    'Rejected' => 'bg-danger',
                    'For Interview' => 'bg-warning text-dark',
                    default => 'bg-secondary'
                };
                ?>
                <span class="badge <?= $badgeClass ?>"><?= $a['status'] ?></span>
            </td>
            <td><?= $a['date_applied'] ?></td>
            <td>
                <!-- Action Buttons -->
                <form method="post" class="d-inline">
                    <input type="hidden" name="application_id" value="<?= $a['application_id'] ?>">
                    
                    <?php if ($a['status'] == 'Pending' || $a['status'] == 'Rejected'): ?>
                        <button type="submit" name="action_type" value="approve" class="btn btn-success btn-sm">Approve</button>
                    <?php endif; ?>

                    <?php if ($a['status'] == 'Pending' || $a['status'] == 'Approved'): ?>
                        <button type="submit" name="action_type" value="reject" class="btn btn-danger btn-sm">Reject</button>
                    <?php endif; ?>
                    
                    <!-- Edit Button triggers modal -->
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $a['application_id'] ?>">Edit</button>
                </form>
            </td>
        </tr>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal<?= $a['application_id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Application</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="application_id" value="<?= $a['application_id'] ?>">
                            <div class="mb-2">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="Pending" <?= $a['status']=='Pending'?'selected':'' ?>>Pending</option>
                                    <option value="Approved" <?= $a['status']=='Approved'?'selected':'' ?>>Approved</option>
                                    <option value="Rejected" <?= $a['status']=='Rejected'?'selected':'' ?>>Rejected</option>
                                    <option value="For Interview" <?= $a['status']=='For Interview'?'selected':'' ?>>For Interview</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label>Remarks</label>
                                <textarea name="remarks" class="form-control"><?= htmlspecialchars($a['remarks']) ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="update_application" class="btn btn-primary">Update</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php include '../includes/footer.php'; ?>
