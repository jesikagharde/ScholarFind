<?php
/**
 * Admin Panel Dashboard & Analytics
 */
require_once '../config/db.php';
require_once '../includes/auth_check.php';

require_admin();

$total_sch = $pdo->query("SELECT COUNT(*) FROM scholarships")->fetchColumn();
$active_sch = $pdo->query("SELECT COUNT(*) FROM scholarships WHERE is_active = 1 AND deadline >= CURDATE()")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$total_bookmarks = $pdo->query("SELECT COUNT(*) FROM saved_scholarships")->fetchColumn();
$total_fund_val = $pdo->query("SELECT SUM(amount) FROM scholarships WHERE is_active = 1")->fetchColumn();

$recent_scholarships = $pdo->query("
    SELECT * FROM scholarships 
    ORDER BY created_at DESC 
    LIMIT 6
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ScholarFind</title>
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
            <li><a href="index.php" class="active">Overview</a></li>
            <li><a href="manage_scholarships.php">Manage Scholarships</a></li>
            <li><a href="add_scholarship.php">+ Add New Scholarship</a></li>
            <li><a href="../index.php" target="_blank">View Live Website ↗</a></li>
        </ul>
        <div class="nav-actions">
            <span style="font-weight: 600; font-size: 0.9rem;">Admin: <?= htmlspecialchars($_SESSION['name']) ?></span>
            <a href="../logout.php" class="btn btn-sm btn-outline">Sign Out</a>
        </div>
    </div>
</nav>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 800; margin-bottom: 4px;">System Analytics Overview</h1>
            <p style="color: var(--text-muted);">Monitor active scholarship schemes, student adoption, and bookmarked opportunities.</p>
        </div>
        <a href="add_scholarship.php" class="btn btn-primary">+ Add New Scholarship</a>
    </div>

    <!-- Metric Stat Cards -->
    <div class="hero-stats" style="margin-top: 0; margin-bottom: 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
        <div style="background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 2rem; font-weight: 800; color: var(--primary);"><?= number_format($total_sch) ?></h3>
            <p style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600; margin: 0;">Total Scholarships (<?= $active_sch ?> Active)</p>
        </div>
        <div style="background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 2rem; font-weight: 800; color: #0284c7;"><?= number_format($total_students) ?></h3>
            <p style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600; margin: 0;">Registered Students</p>
        </div>
        <div style="background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 2rem; font-weight: 800; color: var(--success);"><?= number_format($total_bookmarks) ?></h3>
            <p style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600; margin: 0;">Total Student Bookmarks</p>
        </div>
        <div style="background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow-sm);">
            <h3 style="font-size: 2rem; font-weight: 800; color: #8b5cf6;">₹<?= number_format(($total_fund_val ?? 0) / 100000, 1) ?>L</h3>
            <p style="color: var(--text-muted); font-size: 0.88rem; font-weight: 600; margin: 0;">Total Disbursable Aid</p>
        </div>
    </div>

    <!-- Recently Published Schemes Table -->
    <div style="background: var(--card-bg); padding: 28px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow-card);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 1.25rem; font-weight: 800; margin: 0;">Recently Published Schemes</h3>
            <a href="manage_scholarships.php" class="btn btn-sm btn-outline">View All (<?= $total_sch ?>) →</a>
        </div>
        
        <?php if (empty($recent_scholarships)): ?>
            <p style="color: var(--text-muted);">No scholarships recorded in the system yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="custom-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border); text-align: left;">
                            <th style="padding: 12px 8px;">Scheme Title</th>
                            <th style="padding: 12px 8px;">Provider & Portal</th>
                            <th style="padding: 12px 8px;">Grant Amount</th>
                            <th style="padding: 12px 8px;">State & Category</th>
                            <th style="padding: 12px 8px;">Deadline</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_scholarships as $s): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 14px 8px;">
                                    <strong><?= htmlspecialchars($s['title']) ?></strong><br>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($s['education_level']) ?></span>
                                </td>
                                <td style="padding: 14px 8px;">
                                    <?= htmlspecialchars($s['provider']) ?><br>
                                    <span style="font-size: 0.78rem; color: var(--primary); font-weight: 700;">🌐 <?= htmlspecialchars($s['application_portal'] ?? 'Official Portal') ?></span>
                                </td>
                                <td style="padding: 14px 8px; font-weight: 800; color: var(--success);">₹<?= number_format($s['amount'], 0) ?></td>
                                <td style="padding: 14px 8px; font-size: 0.85rem;">
                                    📍 <?= htmlspecialchars($s['state']) ?><br>
                                    🏷️ <?= htmlspecialchars($s['category']) ?>
                                </td>
                                <td style="padding: 14px 8px; font-size: 0.85rem;"><?= date('d M, Y', strtotime($s['deadline'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
