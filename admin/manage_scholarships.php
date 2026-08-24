<?php
/**
 * Admin: Manage Scholarships Listing
 */
require_once '../config/db.php';
require_once '../includes/auth_check.php';

require_admin();

$msg = '';

if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $del_stmt = $pdo->prepare("DELETE FROM scholarships WHERE scholarship_id = ?");
    $del_stmt->execute([$del_id]);
    $msg = "Scholarship ID #$del_id deleted successfully.";
}

$scholarships = $pdo->query("SELECT * FROM scholarships ORDER BY scholarship_id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Scholarships - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<nav class="navbar" style="border-bottom: 2px solid #ef4444;">
    <div class="container nav-container">
        <a href="index.php" class="nav-brand">
            🛡️ <span>Admin<strong>Portal</strong></span>
        </a>
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
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 4px;">Manage Scholarships (<?= count($scholarships) ?>)</h1>
            <p style="color: var(--text-muted); margin: 0;">Review, edit, and maintain verified central and state scholarship listings.</p>
        </div>
        <a href="add_scholarship.php" class="btn btn-primary">+ Create New Scheme</a>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="table-responsive" style="background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow-card);">
        <table class="custom-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                    <th style="padding: 12px 8px;">ID</th>
                    <th style="padding: 12px 8px;">Title & Provider</th>
                    <th style="padding: 12px 8px;">Portal & State</th>
                    <th style="padding: 12px 8px;">Grant Amount</th>
                    <th style="padding: 12px 8px;">Education Level</th>
                    <th style="padding: 12px 8px;">Deadline</th>
                    <th style="padding: 12px 8px;">Status</th>
                    <th style="padding: 12px 8px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($scholarships as $s): 
                    $is_expired = strtotime($s['deadline']) < time();
                ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px 8px; font-weight: 700;">#<?= $s['scholarship_id'] ?></td>
                        <td style="padding: 12px 8px;">
                            <strong><?= htmlspecialchars($s['title']) ?></strong><br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($s['provider']) ?></span>
                        </td>
                        <td style="padding: 12px 8px; font-size: 0.84rem;">
                            🌐 <strong><?= htmlspecialchars($s['application_portal'] ?? 'Official Portal') ?></strong><br>
                            📍 <?= htmlspecialchars($s['state']) ?>
                        </td>
                        <td style="padding: 12px 8px; font-weight: 800; color: var(--success);">
                            ₹<?= number_format($s['amount'], 0) ?>
                        </td>
                        <td style="padding: 12px 8px;">
                            <span class="meta-pill" style="font-size: 0.78rem; padding: 3px 8px;"><?= htmlspecialchars($s['education_level']) ?></span>
                        </td>
                        <td style="padding: 12px 8px;">
                            <span style="font-size: 0.84rem; color: <?= $is_expired ? 'var(--danger)' : 'inherit' ?>;">
                                <?= date('d M, Y', strtotime($s['deadline'])) ?>
                                <?= $is_expired ? '<br><strong style="color: var(--danger); font-size: 0.75rem;">(Expired)</strong>' : '' ?>
                            </span>
                        </td>
                        <td style="padding: 12px 8px;">
                            <span class="badge <?= $s['is_active'] ? 'badge-match-100' : 'badge-match-low' ?>">
                                <?= $s['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td style="padding: 12px 8px;">
                            <div style="display: flex; gap: 6px;">
                                <a href="edit_scholarship.php?id=<?= $s['scholarship_id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                                <a href="manage_scholarships.php?delete_id=<?= $s['scholarship_id'] ?>" class="btn btn-sm btn-outline" style="color: var(--danger) !important; border-color: rgba(239, 68, 68, 0.4);" onclick="return confirm('Are you sure you want to permanently delete this scholarship?');">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
