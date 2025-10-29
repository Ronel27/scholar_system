<?php
session_start();
require_once '../config/Database.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header('Location: ../index.php'); exit;
}

$db = (new Database())->connect();

// Count stats
$total_students = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_scholarships = $db->query("SELECT COUNT(*) FROM scholarships")->fetchColumn();
$total_applications = $db->query("SELECT COUNT(*) FROM applications")->fetchColumn();
$pending_applications = $db->query("SELECT COUNT(*) FROM applications WHERE status='Pending'")->fetchColumn();

include '../includes/header.php';
?>

<div class="container mt-4">
    <h3>Admin Dashboard</h3>
    <div class="row mt-3">
        <div class="col-md-3"><div class="card p-3 bg-primary text-white">Students<br><?= $total_students ?></div></div>
        <div class="col-md-3"><div class="card p-3 bg-success text-white">Scholarships<br><?= $total_scholarships ?></div></div>
        <div class="col-md-3"><div class="card p-3 bg-warning text-dark">Applications<br><?= $total_applications ?></div></div>
        <div class="col-md-3"><div class="card p-3 bg-danger text-white">Pending<br><?= $pending_applications ?></div></div>
    </div>
    <div class="mt-4">
        <a href="students.php" class="btn btn-info">Manage Students</a>
        <a href="scholarships.php" class="btn btn-success">Manage Scholarships</a>
        <a href="applications.php" class="btn btn-warning">Manage Applications</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
