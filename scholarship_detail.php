<?php
/**
 * ScholarFind — Detailed Scholarship View & Eligibility Breakdown
 * Official Verified Badges, Transparent Source Citations & Guidance
 */
require_once 'config/db.php';
require_once 'includes/auth_check.php';
require_once 'includes/eligibility_engine.php';

$scholarship_id = (int)($_GET['id'] ?? 0);

if ($scholarship_id <= 0) {
    header("Location: scholarships.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM scholarships WHERE scholarship_id = ? AND is_active = 1");
$stmt->execute([$scholarship_id]);
$sch = $stmt->fetch();

if (!$sch) {
    die("Scholarship not found or inactive. <a href='scholarships.php'>Return to directory</a>");
}

$page_title = htmlspecialchars($sch['title']) . ' - ScholarFind Details & Documents';
require_once 'includes/header.php';

$student_profile = null;
$eligibility_result = null;
$is_saved = false;

if (is_logged_in()) {
    $student_profile = get_student_profile($pdo, $_SESSION['user_id']);
    if ($student_profile) {
        $eligibility_result = calculate_eligibility($student_profile, $sch);
    }
    
    $save_stmt = $pdo->prepare("SELECT save_id FROM saved_scholarships WHERE user_id = ? AND scholarship_id = ?");
    $save_stmt->execute([$_SESSION['user_id'], $scholarship_id]);
    $is_saved = (bool)$save_stmt->fetch();
}

$docs_raw = $sch['required_documents'] ?? 'Aadhaar Card, Previous Academic Marksheet, Family Income Certificate, Bank Account Passbook Copy, Passport Size Photograph';
$doc_list = array_filter(array_map('trim', explode(',', $docs_raw)));
$days_left = round((strtotime($sch['deadline']) - time()) / (60 * 60 * 24));
$source_name = !empty($sch['source']) ? $sch['source'] : 'Official Scholarship Portal';
$portal_name = !empty($sch['application_portal']) ? $sch['application_portal'] : 'Authorized Application Portal';
?>

<style>
.detail-layout-grid {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 32px;
    margin-top: 32px;
    margin-bottom: 60px;
    align-items: flex-start;
}

@media (max-width: 1024px) {
    .detail-layout-grid {
        grid-template-columns: 1fr;
    }
}

.detail-main-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 36px;
    box-shadow: var(--shadow-card);
    margin-bottom: 28px;
}

.detail-header-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--primary-light);
    color: var(--primary);
    padding: 6px 14px;
    border-radius: var(--radius-pill);
    font-size: 0.85rem;
    font-weight: 700;
    margin-bottom: 14px;
}

.verified-source-badge {
    background: var(--success-light);
    color: var(--success);
    padding: 5px 12px;
    border-radius: var(--radius-pill);
    font-size: 0.8rem;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.detail-title {
    font-size: 2.2rem;
    font-weight: 800;
    color: var(--text-main);
    letter-spacing: -0.8px;
    line-height: 1.25;
    margin-bottom: 18px;
}

/* Big Vibrant Grant Box */
.detail-grant-callout {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.12) 0%, rgba(5, 150, 105, 0.05) 100%);
    border: 1.5px solid rgba(16, 185, 129, 0.3);
    border-radius: var(--radius-lg);
    padding: 22px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.detail-grant-callout .amount-number {
    font-size: 2.4rem;
    font-weight: 800;
    color: #059669;
    letter-spacing: -0.5px;
    line-height: 1.1;
}

.detail-grant-callout .amount-desc {
    color: var(--text-muted);
    font-size: 0.95rem;
    font-weight: 600;
}

/* Criteria Visual Grid Matrix */
.criteria-matrix-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin: 20px 0 32px;
}

.criteria-tile {
    background: var(--bg-main);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 18px;
    transition: var(--transition);
}

.criteria-tile:hover {
    background: var(--card-bg);
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.criteria-tile .tile-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: var(--primary-light);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    margin-bottom: 10px;
}

.criteria-tile .tile-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}

.criteria-tile .tile-val {
    font-size: 0.98rem;
    font-weight: 800;
    color: var(--text-main);
}

/* Visual Required Documents Box */
.detail-docs-panel {
    background: var(--bg-main);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 26px;
    margin-top: 28px;
}

.doc-grid-flow {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 12px;
    margin-top: 16px;
}

.doc-pill-item {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--text-main);
    box-shadow: var(--shadow-xs);
}

.doc-pill-item .check-bullet {
    color: var(--success);
    font-size: 1rem;
}

/* Sticky Action Sidebar */
.detail-sidebar-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 28px;
    box-shadow: var(--shadow-card);
    position: sticky;
    top: 95px;
}

.deadline-countdown-banner {
    background: var(--danger-light);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: var(--radius);
    padding: 16px;
    text-align: center;
    margin-bottom: 22px;
}

.deadline-countdown-banner .days-text {
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--danger);
    line-height: 1.2;
}

.deadline-countdown-banner .date-sub {
    font-size: 0.82rem;
    color: #991b1b;
    font-weight: 600;
}

.portal-trust-box {
    margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid var(--border);
    font-size: 0.82rem;
    color: var(--text-muted);
    line-height: 1.5;
}
</style>

<div class="container">
    <!-- Breadcrumb Bar -->
    <div style="margin-top: 24px; font-size: 0.88rem; color: var(--text-muted);">
        <a href="index.php">Home</a> &nbsp;›&nbsp; <a href="scholarships.php">Scholarships</a> &nbsp;›&nbsp; <span style="color: var(--text-main); font-weight: 700;"><?= htmlspecialchars($sch['title']) ?></span>
    </div>

    <div class="detail-layout-grid">
        <!-- Left Column: Rich Overview, Criteria Tiles & Documents -->
        <div>
            <div class="detail-main-card">
                <!-- Provider, Portal & Official Verification Badges -->
                <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 14px;">
                    <div class="detail-header-badge" style="margin-bottom: 0;">
                        <span>🏛️ <?= htmlspecialchars($sch['provider']) ?></span>
                    </div>
                    <div class="verified-source-badge">
                        <span>🌐 Application Portal: <?= htmlspecialchars($portal_name) ?></span>
                    </div>
                    <div class="verified-source-badge" style="background: var(--primary-light); color: var(--primary);">
                        <span>✓ Source: <?= htmlspecialchars($source_name) ?></span>
                    </div>
                </div>

                <!-- Main Title -->
                <h1 class="detail-title"><?= htmlspecialchars($sch['title']) ?></h1>

                <!-- Big Vibrant Grant Callout Box -->
                <div class="detail-grant-callout">
                    <div>
                        <div class="amount-number">₹<?= number_format($sch['amount'], 0) ?></div>
                        <div class="amount-desc">Direct Financial Aid / Tuition Fee Reimbursement per Academic Year</div>
                    </div>
                    <span class="badge badge-info" style="font-size: 0.85rem; padding: 6px 14px;">
                        🎓 <?= htmlspecialchars($sch['education_level']) ?>
                    </span>
                </div>

                <!-- Program Overview -->
                <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 10px;">Program Overview</h3>
                <p style="color: var(--text-main); line-height: 1.8; font-size: 1.02rem; opacity: 0.9; margin-bottom: 30px;">
                    <?= nl2br(htmlspecialchars($sch['description'])) ?>
                </p>

                <!-- Eligibility Criteria Matrix (Visual 6-Tile Grid) -->
                <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">Eligibility Criteria Matrix</h3>
                <p style="font-size: 0.88rem; color: var(--text-muted);">Verified requirements sourced from official government and foundation guidelines:</p>

                <div class="criteria-matrix-grid">
                    <div class="criteria-tile">
                        <div class="tile-icon">🎓</div>
                        <div class="tile-label">Education Level</div>
                        <div class="tile-val"><?= htmlspecialchars($sch['education_level']) ?></div>
                    </div>

                    <div class="criteria-tile">
                        <div class="tile-icon">📚</div>
                        <div class="tile-label">Eligible Courses</div>
                        <div class="tile-val"><?= htmlspecialchars($sch['course']) ?></div>
                    </div>

                    <div class="criteria-tile">
                        <div class="tile-icon">🎯</div>
                        <div class="tile-label">Academic Cutoff</div>
                        <div class="tile-val"><?= (float)$sch['minimum_percentage'] > 0 ? (float)$sch['minimum_percentage'] . "% Min" : "No Cutoff" ?></div>
                    </div>

                    <div class="criteria-tile">
                        <div class="tile-icon">💰</div>
                        <div class="tile-label">Family Income</div>
                        <div class="tile-val"><?= !empty($sch['maximum_income']) ? "≤ ₹" . number_format($sch['maximum_income'] / 100000, 1) . " Lakhs" : "No Upper Limit" ?></div>
                    </div>

                    <div class="criteria-tile">
                        <div class="tile-icon">🏷️</div>
                        <div class="tile-label">Category / Quota</div>
                        <div class="tile-val"><?= htmlspecialchars($sch['category']) ?></div>
                    </div>

                    <div class="criteria-tile">
                        <div class="tile-icon">📍</div>
                        <div class="tile-label">State / Domicile</div>
                        <div class="tile-val"><?= htmlspecialchars($sch['state']) ?></div>
                    </div>
                </div>

                <!-- Required Documents Checklist Box -->
                <div class="detail-docs-panel">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <span style="font-size: 1.4rem;">📑</span>
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin: 0;">Required Documents Checklist</h3>
                    </div>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">
                        Keep scanned PDF / JPEG copies of the following documents ready before applying on <strong><?= htmlspecialchars($portal_name) ?></strong>:
                    </p>

                    <div class="doc-grid-flow">
                        <?php foreach ($doc_list as $doc): ?>
                            <div class="doc-pill-item">
                                <span class="check-bullet">✔</span>
                                <span><?= htmlspecialchars($doc) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Official Advisory Disclaimer -->
                <div style="background: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius); padding: 18px 22px; margin-top: 28px; display: flex; gap: 14px; align-items: flex-start;">
                    <span style="font-size: 1.4rem; color: var(--primary);">ℹ️</span>
                    <div style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.6;">
                        <strong style="color: var(--text-main);">Official Platform Notice:</strong><br>
                        Scholarship eligibility criteria, deadlines, benefits, and application requirements may change. Always verify the latest information on the official scholarship website before applying.
                    </div>
                </div>
            </div>

            <!-- Personalized Live Match Result for Logged-In Student -->
            <?php if (is_logged_in() && $student_profile): ?>
                <div style="background: var(--card-bg); padding: 30px; border-radius: var(--radius-xl); border: 2px solid <?= ($eligibility_result['is_incomplete'] ?? false) ? '#6366f1' : ($eligibility_result['is_eligible'] ? '#10b981' : '#f59e0b') ?>; box-shadow: var(--shadow-card);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                        <h3 style="font-size: 1.25rem; font-weight: 800;">Your Eligibility Verdict</h3>
                        <span class="badge <?= $eligibility_result['badge_class'] ?>" style="font-size: 0.88rem; padding: 6px 14px;">
                            <?= $eligibility_result['status_label'] ?>
                        </span>
                    </div>

                    <?php if (!empty($eligibility_result['is_incomplete'])): ?>
                        <div style="background: var(--primary-light); color: var(--primary); padding: 14px 18px; border-radius: var(--radius-sm); font-weight: 600; font-size: 0.95rem; margin-bottom: 16px;">
                            ⚠️ <strong>Profile Incomplete:</strong> Complete your State, Social Category, Education details, and Family Income in your Profile to receive a 100% accurate eligibility result.
                            <div style="margin-top: 10px;">
                                <a href="profile.php" class="btn btn-sm btn-primary">Complete My Profile Now →</a>
                            </div>
                        </div>
                    <?php elseif ($eligibility_result['is_eligible']): ?>
                        <div style="background: var(--success-light); color: #065f46; padding: 14px 18px; border-radius: var(--radius-sm); font-weight: 600; font-size: 0.95rem; margin-bottom: 16px;">
                            🎉 <strong>Great news, <?= htmlspecialchars(explode(' ', $_SESSION['name'])[0]) ?>!</strong> You meet 100% of the verified criteria required for this scholarship!
                        </div>
                    <?php else: ?>
                        <div style="background: var(--warning-light); color: #92400e; padding: 14px 18px; border-radius: var(--radius-sm); font-weight: 600; font-size: 0.95rem; margin-bottom: 16px;">
                            ⚠️ <strong>Match Summary:</strong> You meet <?= $eligibility_result['passed_count'] ?> of <?= $eligibility_result['total_count'] ?> criteria for this opportunity.
                        </div>
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <?php foreach ($eligibility_result['passed_details'] as $p): ?>
                            <div style="font-size: 0.85rem; color: #059669; font-weight: 600;">✔ <?= htmlspecialchars($p) ?></div>
                        <?php endforeach; ?>
                        <?php foreach ($eligibility_result['pending_details'] as $pd): ?>
                            <div style="font-size: 0.85rem; color: var(--primary); font-weight: 600;">⏳ <?= htmlspecialchars($pd) ?></div>
                        <?php endforeach; ?>
                        <?php foreach ($eligibility_result['failed_details'] as $f): ?>
                            <div style="font-size: 0.85rem; color: #dc2626; font-weight: 600;">✖ <?= htmlspecialchars($f) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Clean Action & Deadline Sidebar -->
        <div>
            <div class="detail-sidebar-card">
                <!-- Deadline Countdown Banner -->
                <div class="deadline-countdown-banner">
                    <div class="days-text">
                        <?= $days_left > 0 ? "⏳ $days_left Days Left" : "⚠️ Deadline Passed" ?>
                    </div>
                    <div class="date-sub">
                        Closes on <?= date('d M, Y', strtotime($sch['deadline'])) ?>
                    </div>
                </div>

                <!-- Primary Action Buttons -->
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="<?= htmlspecialchars($sch['application_url']) ?>" target="_blank" class="btn btn-primary btn-block" style="padding: 14px; font-size: 1rem; text-align: center;">
                        🔗 Visit Official Scholarship Website ↗
                    </a>

                    <?php if (is_logged_in()): ?>
                        <button class="btn btn-block btn-save-scholarship <?= $is_saved ? 'btn-primary' : 'btn-outline' ?>" data-id="<?= $sch['scholarship_id'] ?>" style="padding: 12px;">
                            <?= $is_saved ? '★ Saved in Bookmarks' : '☆ Save to Bookmarks' ?>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Official Portal Trust & Application Note -->
                <div class="portal-trust-box">
                    <div style="font-weight: 700; color: var(--text-main); margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <span>🛡️</span> Application Process Guidance
                    </div>
                    <p style="margin-bottom: 8px;">
                        ScholarFind helps you discover scholarships and check your eligibility. Scholarship applications are processed through their respective official portals.
                    </p>
                    <p style="margin-bottom: 0; font-size: 0.78rem; color: var(--text-muted);">
                        To apply and check the latest status of your application, please visit <strong><?= htmlspecialchars($portal_name) ?></strong> directly.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
