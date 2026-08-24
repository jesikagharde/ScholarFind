<?php
/**
 * ScholarFind — My Saved Scholarships Hub
 * Ultra-Modern SaaS Bookmark Collection Page with Clean Visual Hierarchy
 */
require_once 'config/db.php';
require_once 'includes/auth_check.php';

require_login();
validate_session_user($pdo);

$user_id = $_SESSION['user_id'];

// Fetch all saved scholarships for this user
$stmt = $pdo->prepare("
    SELECT s.*, ss.saved_at 
    FROM saved_scholarships ss
    JOIN scholarships s ON ss.scholarship_id = s.scholarship_id
    WHERE ss.user_id = ?
    ORDER BY ss.saved_at DESC
");
$stmt->execute([$user_id]);
$saved_scholarships = $stmt->fetchAll();

$page_title = 'My Saved Scholarships - ScholarFind';
require_once 'includes/header.php';
?>

<div class="container" style="margin-top: 36px; margin-bottom: 70px;">
    <!-- Page Title Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 28px; border-bottom: 1px solid var(--border); padding-bottom: 22px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="font-size: 2rem;">⭐</span>
                <h1 style="font-size: 2.3rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.6px; margin: 0;">My Saved Scholarships</h1>
                <span class="badge badge-info" style="font-size: 0.9rem; padding: 6px 14px; margin-left: 6px;">
                    <?= count($saved_scholarships) ?> Saved
                </span>
            </div>
            <p style="color: var(--text-muted); font-size: 1.02rem; margin: 0;">Quick access to all opportunities you have bookmarked for tracking and portal application.</p>
        </div>
        <a href="scholarships.php" class="btn btn-primary btn-pill" style="padding: 12px 24px;">+ Browse More Schemes</a>
    </div>

    <?php if (empty($saved_scholarships)): ?>
        <!-- Modern Empty State with Vector SVG Illustration -->
        <div style="text-align: center; padding: 75px 24px; background: var(--card-bg); border-radius: var(--radius-xl); border: 1px solid var(--border); box-shadow: var(--shadow-card); max-width: 680px; margin: 40px auto;">
            <div style="max-width: 140px; margin: 0 auto 18px;">
                <svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 100%; height: auto;">
                    <circle cx="60" cy="60" r="54" fill="var(--primary-light)" opacity="0.6"/>
                    <path d="M42 34C42 30.6863 44.6863 28 48 28H72C75.3137 28 78 30.6863 78 34V92L60 76L42 92V34Z" fill="var(--primary)" opacity="0.25"/>
                    <path d="M46 38C46 35.7909 47.7909 34 50 34H70C72.2091 34 74 35.7909 74 38V84L60 71L46 84V38Z" stroke="var(--primary)" stroke-width="4" stroke-linejoin="round" fill="none"/>
                    <path d="M60 46L63.09 52.26L70 53.27L65 58.14L66.18 65.02L60 61.77L53.82 65.02L55 58.14L50 53.27L56.91 52.26L60 46Z" fill="var(--primary)"/>
                </svg>
            </div>
            <h2 style="font-size: 1.7rem; font-weight: 800; color: var(--text-main); margin-bottom: 8px;">No Saved Scholarships Yet</h2>
            <p style="color: var(--text-muted); max-width: 480px; margin: 0 auto 28px; font-size: 0.98rem; line-height: 1.6;">
                You haven't bookmarked any scholarships yet. Explore our directory of 35 verified government and corporate schemes and click <strong>"☆ Save"</strong> to bookmark them here.
            </p>
            <a href="scholarships.php" class="btn btn-primary btn-pill" style="padding: 13px 32px; font-size: 1rem;">
                🔍 Explore All Scholarships →
            </a>
        </div>
    <?php else: ?>
        <div style="margin-bottom: 22px; font-weight: 700; color: var(--text-muted); font-size: 0.95rem;">
            Showing <?= count($saved_scholarships) ?> bookmarked <?= count($saved_scholarships) === 1 ? 'opportunity' : 'opportunities' ?>
        </div>

        <div class="scholarship-grid" style="margin-top: 0;">
            <?php foreach ($saved_scholarships as $sch): 
                $days_left = round((strtotime($sch['deadline']) - time()) / (60 * 60 * 24));
            ?>
                <div class="scholarship-card">
                    <div>
                        <!-- Top Provider Badge & Tag -->
                        <div class="card-top-row">
                            <div class="card-provider-badge">
                                <div class="provider-icon-circle">🏛️</div>
                                <span><?= htmlspecialchars($sch['provider']) ?></span>
                            </div>
                            <span class="badge badge-info"><?= htmlspecialchars($sch['education_level']) ?></span>
                        </div>

                        <!-- Title -->
                        <h3 class="card-title"><?= htmlspecialchars($sch['title']) ?></h3>

                        <!-- Amount Box -->
                        <div class="card-amount-box">
                            <span class="amount-val">₹<?= number_format($sch['amount'], 0) ?></span>
                            <span class="amount-label">/ academic year</span>
                        </div>

                        <!-- Chips Meta Row -->
                        <div class="card-chips-row">
                            <span class="card-chip">🏷️ <?= htmlspecialchars($sch['category']) ?></span>
                            <span class="card-chip">📍 <?= htmlspecialchars($sch['state']) ?></span>
                            <span class="card-chip" style="color: var(--primary); font-weight: 700;">🌐 <?= htmlspecialchars($sch['application_portal'] ?? 'Official Portal') ?></span>
                        </div>

                        <!-- Description -->
                        <p class="card-desc">
                            <?= htmlspecialchars(substr($sch['description'], 0, 115)) ?>...
                        </p>
                    </div>

                    <!-- Footer Action Row -->
                    <div class="card-footer-action">
                        <span class="deadline-pill <?= $days_left <= 7 ? 'urgent' : '' ?>">
                            📅 <?= $days_left > 0 ? "$days_left days left" : "Deadline: " . date('d M, Y', strtotime($sch['deadline'])) ?>
                        </span>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn btn-sm btn-primary btn-save-scholarship" data-id="<?= $sch['scholarship_id'] ?>" title="Click to remove from bookmarks">
                                ★ Saved
                            </button>
                            <a href="scholarship_detail.php?id=<?= $sch['scholarship_id'] ?>" class="btn btn-sm btn-outline">Details & Docs →</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
