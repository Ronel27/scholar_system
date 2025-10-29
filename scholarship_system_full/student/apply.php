<?php
session_start();
require_once '../config/Database.php';

// Ensure student is logged in
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student'){
    header('Location: ../index.php'); 
    exit;
}

$db = (new Database())->connect();

// Get student_id
$stmt = $db->prepare("SELECT student_id FROM students WHERE user_id=?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
$student_id = $student['student_id'];

$errors = [];
$success = '';

// Handle application submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $scholarship_id = $_POST['scholarship_id'] ?? null;
    $documents = $_POST['documents'] ?? '';
    $remarks = $_POST['remarks'] ?? '';

    if(!$scholarship_id){
        $errors[] = "Please select a scholarship to apply.";
    } else {
        // Check if student already applied for the scholarship
        $check = $db->prepare("SELECT * FROM applications WHERE scholarship_id=? AND student_id=?");
        $check->execute([$scholarship_id, $student_id]);
        if($check->fetch()){
            $errors[] = "You have already applied for this scholarship.";
        } else {
            // Insert application
            $stmt = $db->prepare("
                INSERT INTO applications (scholarship_id, student_id, documents, remarks)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$scholarship_id, $student_id, $documents, $remarks]);
            $success = "Application submitted successfully!";
        }
    }
}

// Fetch all open scholarships, DISTINCT to avoid duplicates
$scholarships = $db->query("SELECT DISTINCT scholarship_id, name, sponsor 
                             FROM scholarships 
                             WHERE status='Open' 
                             ORDER BY start_date DESC")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="container mt-4">
    <h3>Apply for Scholarship</h3>

    <?php if($success) echo '<div class="alert alert-success">'.$success.'</div>'; ?>
    <?php foreach($errors as $e) echo '<div class="alert alert-danger">'.$e.'</div>'; ?>

    <form method="post">
        <div class="mb-2">
            <label for="scholarship_id">Select Scholarship</label>
            <select name="scholarship_id" id="scholarship_id" class="form-control" required>
                <option value="">-- Select Scholarship --</option>
                <?php foreach($scholarships as $s): ?>
                    <option value="<?= $s['scholarship_id'] ?>" 
                        <?= isset($_POST['scholarship_id']) && $_POST['scholarship_id']==$s['scholarship_id'] ? 'selected':'' ?>>
                        <?= htmlspecialchars($s['name']) ?> (Sponsor: <?= htmlspecialchars($s['sponsor']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-2">
            <label for="documents">Documents / Requirements</label>
            <textarea name="documents" id="documents" class="form-control" placeholder="Enter document names or file details"><?= isset($_POST['documents']) ? htmlspecialchars($_POST['documents']) : '' ?></textarea>
        </div>
        <div class="mb-2">
            <label for="remarks">Remarks (optional)</label>
            <textarea name="remarks" id="remarks" class="form-control"><?= isset($_POST['remarks']) ? htmlspecialchars($_POST['remarks']) : '' ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Application</button>
    </form>

    <!-- Back button using HTTP_REFERER -->
    <?php if(isset($_SERVER['HTTP_REFERER'])): ?>
        <a href="<?= $_SERVER['HTTP_REFERER'] ?>" class="btn btn-secondary mt-3">Back</a>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
