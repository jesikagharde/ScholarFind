<?php
/**
 * Admin: Edit Existing Scholarship
 */
require_once '../config/db.php';
require_once '../includes/auth_check.php';

require_admin();

$scholarship_id = (int)($_GET['id'] ?? 0);
if ($scholarship_id <= 0) {
    header("Location: manage_scholarships.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM scholarships WHERE scholarship_id = ?");
$stmt->execute([$scholarship_id]);
$sch = $stmt->fetch();

if (!$sch) {
    die("Scholarship not found. <a href='manage_scholarships.php'>Return to listing</a>");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $provider = trim($_POST['provider'] ?? '');
    $source = trim($_POST['source'] ?? 'Official Verified Source');
    $application_portal = trim($_POST['application_portal'] ?? 'Official Portal');
    $description = trim($_POST['description'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $education_level = trim($_POST['education_level'] ?? 'Undergraduate');
    $course = trim($_POST['course'] ?? 'All');
    $minimum_percentage = (float)($_POST['minimum_percentage'] ?? 0);
    $maximum_income = !empty($_POST['maximum_income']) ? (float)$_POST['maximum_income'] : null;
    $gender_eligible = trim($_POST['gender_eligible'] ?? 'all');
    $state = trim($_POST['state'] ?? 'All India');
    $category = trim($_POST['category'] ?? 'All');
    $required_documents = trim($_POST['required_documents'] ?? '');
    $application_start = !empty($_POST['application_start']) ? $_POST['application_start'] : null;
    $deadline = $_POST['deadline'] ?? '';
    $application_url = trim($_POST['application_url'] ?? '#');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($title) || empty($provider) || empty($deadline) || $amount <= 0) {
        $error = "Please provide Title, Provider, Award Amount, and Submission Deadline.";
    } else {
        try {
            $upd = $pdo->prepare("
                UPDATE scholarships SET 
                title = ?, provider = ?, source = ?, application_portal = ?, description = ?, amount = ?, education_level = ?, course = ?,
                minimum_percentage = ?, maximum_income = ?, gender_eligible = ?, state = ?, category = ?,
                required_documents = ?, application_start = ?, deadline = ?, application_url = ?, is_active = ?
                WHERE scholarship_id = ?
            ");
            $upd->execute([
                $title, $provider, $source, $application_portal, $description, $amount, $education_level, $course, 
                $minimum_percentage, $maximum_income, $gender_eligible, $state, $category, 
                $required_documents, $application_start, $deadline, $application_url, $is_active, $scholarship_id
            ]);

            $success = "Scholarship updated successfully!";
            
            $stmt->execute([$scholarship_id]);
            $sch = $stmt->fetch();
        } catch (Exception $e) {
            $error = "Failed to update scholarship: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Scholarship #<?= $scholarship_id ?> - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<nav class="navbar" style="border-bottom: 2px solid #ef4444;">
    <div class="container nav-container">
        <a href="index.php" class="nav-brand">🛡️ <span>Admin<strong>Portal</strong></span></a>
        <ul class="nav-links">
            <li><a href="index.php">Overview</a></li>
            <li><a href="manage_scholarships.php" class="active">Manage Scholarships</a></li>
            <li><a href="add_scholarship.php">+ Add New Scholarship</a></li>
            <li><a href="../index.php" target="_blank">View Live Website ↗</a></li>
        </ul>
        <div class="nav-actions">
            <a href="../logout.php" class="btn btn-sm btn-outline">Sign Out</a>
        </div>
    </div>
</nav>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    <div style="max-width: 800px; margin: 0 auto; background: var(--card-bg); padding: 36px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-card);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h1 style="font-size: 1.8rem; font-weight: 800;">Edit Scholarship #<?= $scholarship_id ?></h1>
            <a href="manage_scholarships.php" class="btn btn-sm btn-outline">← Back to List</a>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="edit_scholarship.php?id=<?= $scholarship_id ?>">
            <div class="form-group">
                <label class="form-label">Scholarship Title *</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($sch['title']) ?>" required>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Provider / Organization *</label>
                    <input type="text" name="provider" class="form-control" value="<?= htmlspecialchars($sch['provider']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Application Portal *</label>
                    <input type="text" name="application_portal" class="form-control" value="<?= htmlspecialchars($sch['application_portal'] ?? 'Official Portal') ?>" required>
                </div>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Grant Amount (₹ / Year) *</label>
                    <input type="number" step="100" min="0" name="amount" class="form-control" value="<?= (float)$sch['amount'] ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Official Source Name</label>
                    <input type="text" name="source" class="form-control" value="<?= htmlspecialchars($sch['source'] ?? 'Official Verified Source') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description & Scope</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($sch['description']) ?></textarea>
            </div>

            <h3 style="font-size: 1.1rem; font-weight: 800; margin: 24px 0 14px; color: var(--primary);">Eligibility Rules</h3>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Standardized Education Level</label>
                    <select name="education_level" class="form-control">
                        <option value="Undergraduate" <?= $sch['education_level'] === 'Undergraduate' ? 'selected' : '' ?>>Undergraduate Degree</option>
                        <option value="School (Class 1–10)" <?= $sch['education_level'] === 'School (Class 1–10)' ? 'selected' : '' ?>>School (Class 1–10)</option>
                        <option value="Class 11" <?= $sch['education_level'] === 'Class 11' ? 'selected' : '' ?>>Class 11 (Junior College)</option>
                        <option value="Class 12" <?= $sch['education_level'] === 'Class 12' ? 'selected' : '' ?>>Class 12 (Higher Secondary)</option>
                        <option value="Diploma / Polytechnic" <?= $sch['education_level'] === 'Diploma / Polytechnic' ? 'selected' : '' ?>>Diploma / Polytechnic</option>
                        <option value="Postgraduate" <?= $sch['education_level'] === 'Postgraduate' ? 'selected' : '' ?>>Postgraduate Degree</option>
                        <option value="PhD / Research" <?= $sch['education_level'] === 'PhD / Research' ? 'selected' : '' ?>>PhD / Research</option>
                        <option value="Vocational / Skill Courses" <?= $sch['education_level'] === 'Vocational / Skill Courses' ? 'selected' : '' ?>>Vocational / Skill Courses</option>
                        <option value="All" <?= $sch['education_level'] === 'All' ? 'selected' : '' ?>>All Education Levels</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Eligible Courses / Streams</label>
                    <input type="text" name="course" class="form-control" value="<?= htmlspecialchars($sch['course']) ?>">
                </div>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Minimum Academic Score (%)</label>
                    <input type="number" step="0.1" name="minimum_percentage" class="form-control" value="<?= (float)$sch['minimum_percentage'] ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Maximum Family Income Ceiling (₹)</label>
                    <input type="number" step="1000" name="maximum_income" class="form-control" value="<?= $sch['maximum_income'] ?>">
                </div>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Social Category</label>
                    <select name="category" class="form-control">
                        <option value="All" <?= $sch['category'] === 'All' ? 'selected' : '' ?>>All Categories</option>
                        <option value="General / Open" <?= $sch['category'] === 'General / Open' || $sch['category'] === 'General' ? 'selected' : '' ?>>General / Open</option>
                        <option value="OBC" <?= $sch['category'] === 'OBC' ? 'selected' : '' ?>>OBC</option>
                        <option value="SC" <?= $sch['category'] === 'SC' ? 'selected' : '' ?>>SC</option>
                        <option value="ST" <?= $sch['category'] === 'ST' ? 'selected' : '' ?>>ST</option>
                        <option value="EWS" <?= $sch['category'] === 'EWS' ? 'selected' : '' ?>>EWS</option>
                        <option value="VJ/NT" <?= $sch['category'] === 'VJ/NT' || $sch['category'] === 'VJNT' ? 'selected' : '' ?>>VJ/NT</option>
                        <option value="SBC" <?= $sch['category'] === 'SBC' ? 'selected' : '' ?>>SBC</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">State / Domicile</label>
                    <select name="state" class="form-control">
                        <option value="All India" <?= $sch['state'] === 'All India' || $sch['state'] === 'All' ? 'selected' : '' ?>>All India</option>
                        <option value="Maharashtra" <?= $sch['state'] === 'Maharashtra' ? 'selected' : '' ?>>Maharashtra</option>
                        <option value="Karnataka" <?= $sch['state'] === 'Karnataka' ? 'selected' : '' ?>>Karnataka</option>
                        <option value="Delhi" <?= $sch['state'] === 'Delhi' ? 'selected' : '' ?>>Delhi (NCT)</option>
                        <option value="Uttar Pradesh" <?= $sch['state'] === 'Uttar Pradesh' ? 'selected' : '' ?>>Uttar Pradesh</option>
                        <option value="West Bengal" <?= $sch['state'] === 'West Bengal' ? 'selected' : '' ?>>West Bengal</option>
                        <option value="Tamil Nadu" <?= $sch['state'] === 'Tamil Nadu' ? 'selected' : '' ?>>Tamil Nadu</option>
                        <option value="Gujarat" <?= $sch['state'] === 'Gujarat' ? 'selected' : '' ?>>Gujarat</option>
                        <option value="Madhya Pradesh" <?= $sch['state'] === 'Madhya Pradesh' ? 'selected' : '' ?>>Madhya Pradesh</option>
                        <option value="Rajasthan" <?= $sch['state'] === 'Rajasthan' ? 'selected' : '' ?>>Rajasthan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Gender Eligibility</label>
                    <select name="gender_eligible" class="form-control">
                        <option value="all" <?= $sch['gender_eligible'] === 'all' ? 'selected' : '' ?>>All Genders</option>
                        <option value="female" <?= $sch['gender_eligible'] === 'female' ? 'selected' : '' ?>>Female Only</option>
                        <option value="male" <?= $sch['gender_eligible'] === 'male' ? 'selected' : '' ?>>Male Only</option>
                    </select>
                </div>
            </div>

            <h3 style="font-size: 1.1rem; font-weight: 800; margin: 24px 0 14px; color: var(--primary);">Required Documents & Application Schedule</h3>

            <div class="form-group">
                <label class="form-label">Required Documents Checklist (Comma separated)</label>
                <textarea name="required_documents" class="form-control" rows="3"><?= htmlspecialchars($sch['required_documents'] ?? 'Aadhaar Card, Previous Academic Marksheet, Family Income Certificate, Bank Account Passbook Copy, Passport Size Photograph') ?></textarea>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Opening Date</label>
                    <input type="date" name="application_start" class="form-control" value="<?= $sch['application_start'] ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Submission Deadline *</label>
                    <input type="date" name="deadline" class="form-control" value="<?= $sch['deadline'] ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Official Application Portal URL</label>
                <input type="url" name="application_url" class="form-control" value="<?= htmlspecialchars($sch['application_url']) ?>">
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="is_active" id="is_active" <?= $sch['is_active'] ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                <label for="is_active" style="font-weight: 600; cursor: pointer;">Active Listing</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 14px; font-size: 1.05rem; margin-top: 16px;">
                Save Changes
            </button>
        </form>
    </div>
</div>

</body>
</html>
