<?php
/**
 * Admin: Add New Scholarship
 */
require_once '../config/db.php';
require_once '../includes/auth_check.php';

require_admin();

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
    $required_documents = trim($_POST['required_documents'] ?? 'Aadhaar Card, Previous Academic Marksheet, Family Income Certificate, Bank Account Passbook Copy, Passport Size Photograph');
    $application_start = !empty($_POST['application_start']) ? $_POST['application_start'] : null;
    $deadline = $_POST['deadline'] ?? '';
    $application_url = trim($_POST['application_url'] ?? '#');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($title) || empty($provider) || empty($deadline) || $amount <= 0) {
        $error = "Please provide Title, Provider, Award Amount, and Submission Deadline.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO scholarships 
                (title, provider, source, application_portal, description, amount, education_level, course, minimum_percentage, maximum_income, gender_eligible, state, category, required_documents, application_start, deadline, application_url, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title, $provider, $source, $application_portal, $description, $amount, $education_level, $course, 
                $minimum_percentage, $maximum_income, $gender_eligible, $state, $category, 
                $required_documents, $application_start, $deadline, $application_url, $is_active
            ]);

            $success = "Scholarship published successfully!";
        } catch (Exception $e) {
            $error = "Failed to create scholarship: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Scholarship - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<nav class="navbar" style="border-bottom: 2px solid #ef4444;">
    <div class="container nav-container">
        <a href="index.php" class="nav-brand">🛡️ <span>Admin<strong>Portal</strong></span></a>
        <ul class="nav-links">
            <li><a href="index.php">Overview</a></li>
            <li><a href="manage_scholarships.php">Manage Scholarships</a></li>
            <li><a href="add_scholarship.php" class="active">+ Add New Scholarship</a></li>
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
            <h1 style="font-size: 1.8rem; font-weight: 800;">Add New Scholarship Scheme</h1>
            <a href="manage_scholarships.php" class="btn btn-sm btn-outline">← Back to List</a>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="add_scholarship.php">
            <div class="form-group">
                <label class="form-label">Scholarship Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Rajarshi Shahu Maharaj EBC Scholarship" required>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Provider / Ministry / Foundation *</label>
                    <input type="text" name="provider" class="form-control" placeholder="e.g. Directorate of Higher Education, Govt of Maharashtra" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Application Portal *</label>
                    <input type="text" name="application_portal" class="form-control" placeholder="e.g. MahaDBT, National Scholarship Portal (NSP)" required>
                </div>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Grant Amount (₹ / Year) *</label>
                    <input type="number" step="100" min="0" name="amount" class="form-control" placeholder="e.g. 50000" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Official Source Name</label>
                    <input type="text" name="source" class="form-control" placeholder="e.g. MahaDBT Official Portal, Tata Trusts Official" value="Official Verified Source">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Description & Scope</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Overview of the grant, eligibility details, and student benefits..."></textarea>
            </div>

            <h3 style="font-size: 1.1rem; font-weight: 800; margin: 24px 0 14px; color: var(--primary);">Eligibility Rules</h3>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Standardized Education Level</label>
                    <select name="education_level" class="form-control">
                        <option value="Undergraduate">Undergraduate Degree</option>
                        <option value="School (Class 1–10)">School (Class 1–10)</option>
                        <option value="Class 11">Class 11 (Junior College)</option>
                        <option value="Class 12">Class 12 (Higher Secondary)</option>
                        <option value="Diploma / Polytechnic">Diploma / Polytechnic</option>
                        <option value="Postgraduate">Postgraduate Degree</option>
                        <option value="PhD / Research">PhD / Research</option>
                        <option value="Vocational / Skill Courses">Vocational / Skill Courses</option>
                        <option value="All">All Education Levels</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Eligible Courses / Streams</label>
                    <input type="text" name="course" class="form-control" value="All" placeholder="e.g. BCA, B.Tech, Medical or All">
                </div>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Minimum Academic Score (%)</label>
                    <input type="number" step="0.1" name="minimum_percentage" class="form-control" value="0.0" placeholder="e.g. 60">
                </div>

                <div class="form-group">
                    <label class="form-label">Maximum Family Income Ceiling (₹)</label>
                    <input type="number" step="1000" name="maximum_income" class="form-control" placeholder="Leave empty for no limit">
                </div>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Social Category</label>
                    <select name="category" class="form-control">
                        <option value="All">All Categories</option>
                        <option value="General / Open">General / Open</option>
                        <option value="OBC">OBC</option>
                        <option value="SC">SC</option>
                        <option value="ST">ST</option>
                        <option value="EWS">EWS</option>
                        <option value="VJ/NT">VJ/NT</option>
                        <option value="SBC">SBC</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">State / Domicile</label>
                    <select name="state" class="form-control">
                        <option value="All India">All India (National)</option>
                        <option value="Maharashtra">Maharashtra</option>
                        <option value="Karnataka">Karnataka</option>
                        <option value="Delhi">Delhi (NCT)</option>
                        <option value="Uttar Pradesh">Uttar Pradesh</option>
                        <option value="West Bengal">West Bengal</option>
                        <option value="Tamil Nadu">Tamil Nadu</option>
                        <option value="Gujarat">Gujarat</option>
                        <option value="Madhya Pradesh">Madhya Pradesh</option>
                        <option value="Rajasthan">Rajasthan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender_eligible" class="form-control">
                        <option value="all">All Genders</option>
                        <option value="female">Female Only</option>
                        <option value="male">Male Only</option>
                    </select>
                </div>
            </div>

            <h3 style="font-size: 1.1rem; font-weight: 800; margin: 24px 0 14px; color: var(--primary);">Required Documents & Application Schedule</h3>

            <div class="form-group">
                <label class="form-label">Required Documents Checklist (Comma separated)</label>
                <textarea name="required_documents" class="form-control" rows="3">Aadhaar Card, Previous Academic Marksheet, Family Income Certificate, Bank Account Passbook Copy, Passport Size Photograph</textarea>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Opening Date</label>
                    <input type="date" name="application_start" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">Submission Deadline *</label>
                    <input type="date" name="deadline" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Official Application URL</label>
                <input type="url" name="application_url" class="form-control" placeholder="https://mahadbt.maharashtra.gov.in" value="https://mahadbt.maharashtra.gov.in">
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="is_active" id="is_active" checked style="width: 18px; height: 18px;">
                <label for="is_active" style="font-weight: 600; cursor: pointer;">Publish as Active immediately</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="padding: 14px; font-size: 1.05rem; margin-top: 16px;">
                Save & Publish Scholarship
            </button>
        </form>
    </div>
</div>

</body>
</html>
