<?php
/**
 * ScholarFind — Global Navigation Header
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/auth_check.php';
if (isset($pdo)) {
    validate_session_user($pdo);
}
$user = current_user();

$current_page = basename($_SERVER['PHP_SELF']);

$saved_count = 0;
if (is_logged_in() && isset($pdo)) {
    $c_stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_scholarships WHERE user_id = ?");
    $c_stmt->execute([$_SESSION['user_id']]);
    $saved_count = (int)$c_stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light" data-color="indigo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'ScholarFind - Find Scholarships You\'re Eligible For' ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

<!-- ScholarFind Navigation Bar -->
<nav class="navbar">
    <div class="container nav-container">
        <a href="index.php" class="nav-brand">
            <div class="brand-icon-box">🎓</div>
            <div>
                <span>Scholar<span class="highlight">Find</span></span>
                <span class="brand-sub">Find. Check. Achieve.</span>
            </div>
        </a>

        <ul class="nav-links">
            <li><a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Home</a></li>
            <li><a href="scholarships.php" class="<?= $current_page === 'scholarships.php' ? 'active' : '' ?>">Find Scholarships</a></li>
            <li><a href="eligibility_checker.php" class="<?= $current_page === 'eligibility_checker.php' ? 'active' : '' ?>">Eligibility Checker</a></li>
            
            <?php if (is_logged_in()): ?>
                <?php if (is_admin()): ?>
                    <li><a href="admin/index.php" style="color: #ef4444; font-weight: 700;">⚙️ Admin Panel</a></li>
                <?php else: ?>
                    <li>
                        <a href="saved.php" class="<?= $current_page === 'saved.php' ? 'active' : '' ?>">
                            ⭐ Saved <span class="badge-count" id="nav-saved-count"><?= $saved_count ?></span>
                        </a>
                    </li>
                    <li><a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>

        <div class="nav-actions">
            <!-- Theme & Color Switcher Box -->
            <div class="theme-switcher-box">
                <button type="button" id="theme-toggle-btn" class="theme-toggle-btn" title="Toggle Dark/Light Mode">🌙</button>
                <div class="color-dots">
                    <span class="color-dot dot-indigo" data-color="indigo" title="Royal Indigo"></span>
                    <span class="color-dot dot-purple" data-color="purple" title="Royal Purple"></span>
                    <span class="color-dot dot-blue" data-color="blue" title="Ocean Blue"></span>
                    <span class="color-dot dot-emerald" data-color="emerald" title="Emerald Green"></span>
                    <span class="color-dot dot-sunset" data-color="sunset" title="Sunset Orange"></span>
                    <span class="color-dot dot-pink" data-color="pink" title="Pastel Rose"></span>
                </div>
            </div>

            <div class="auth-buttons" style="display: flex; align-items: center; gap: 8px;">
                <?php if (is_logged_in()): ?>
                    <a href="profile.php" class="btn btn-sm btn-outline" style="display: flex; align-items: center; gap: 6px; font-weight: 700;">
                        <span>👤</span> <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>
                        <?php if (!empty($user['scholarfind_id'])): ?>
                            <span style="font-size: 0.72rem; opacity: 0.8; font-weight: normal;">(@<?= htmlspecialchars($user['scholarfind_id']) ?>)</span>
                        <?php endif; ?>
                    </a>
                    <a href="logout.php" class="btn btn-sm btn-outline">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-sm btn-outline">Login</a>
                    <a href="register.php" class="btn btn-sm btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="container" style="margin-top: 20px;">
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="container" style="margin-top: 20px;">
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
    </div>
<?php endif; ?>
