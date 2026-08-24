<?php
/**
 * ScholarFind — Homepage (Matches Design Mockup with Lightweight Visual Graphics)
 */
require_once 'config/db.php';
$page_title = 'ScholarFind - Find Scholarships You\'re Eligible For';
require_once 'includes/header.php';

$total_sch = $pdo->query("SELECT COUNT(*) FROM scholarships WHERE is_active = 1")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();

// Fetch featured scholarships
$featured = $pdo->query("
    SELECT * FROM scholarships 
    WHERE is_active = 1 AND deadline >= CURDATE() 
    ORDER BY amount DESC 
    LIMIT 6
")->fetchAll();

$saved_ids = [];
if (is_logged_in()) {
    $s_stmt = $pdo->prepare("SELECT scholarship_id FROM saved_scholarships WHERE user_id = ?");
    $s_stmt->execute([$_SESSION['user_id']]);
    $saved_ids = $s_stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!-- ScholarFind Hero Section with Lightweight Vector Graphic -->
<section class="scholarfind-hero">
    <div class="container">
        <div class="hero-grid">
            <!-- Left Hero Content -->
            <div class="hero-content">
                <h1>Find Scholarships You're <span class="highlight">Eligible For</span></h1>
                <p>Smart discovery and eligibility checker connecting Indian students with verified Government of Maharashtra (MahaDBT), Central NSP schemes, Tata Trusts, and corporate foundation grants.</p>
                
                <div class="hero-cta-group">
                    <?php if (is_logged_in()): ?>
                        <a href="dashboard.php" class="btn btn-primary btn-pill">
                            📊 Go to My Dashboard →
                        </a>
                        <a href="scholarships.php" class="btn btn-outline btn-pill">
                            🔍 Find Scholarships
                        </a>
                    <?php else: ?>
                        <a href="scholarships.php" class="btn btn-primary btn-pill">
                            🔍 Find Scholarships
                        </a>
                        <a href="eligibility_checker.php" class="btn btn-outline btn-pill">
                            🛡️ Check Eligibility
                        </a>
                    <?php endif; ?>
                </div>

                <!-- 3 Trust Points Row -->
                <div class="hero-trust-row">
                    <div class="trust-item">
                        <div class="trust-icon">🔍</div>
                        <div>
                            <h4>Smart Search</h4>
                            <p>Find scholarships matching your stream & state</p>
                        </div>
                    </div>

                    <div class="trust-item">
                        <div class="trust-icon">🛡️</div>
                        <div>
                            <h4>Eligibility Check</h4>
                            <p>Get instant eligibility scores with clear reasons</p>
                        </div>
                    </div>

                    <div class="trust-item">
                        <div class="trust-icon">🔔</div>
                        <div>
                            <h4>Deadline Alerts</h4>
                            <p>Track closing dates and document checklists</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Hero Graphic & Floating Support Card -->
            <div class="hero-graphic-wrap">
                <!-- Clean Lightweight Education SVG Vector Graphic -->
                <div style="text-align: center; margin-bottom: 20px;">
                    <svg viewBox="0 0 400 180" fill="none" xmlns="http://www.w3.org/2000/svg" style="max-width: 320px; width: 100%; height: auto;">
                        <circle cx="200" cy="90" r="75" fill="var(--primary-light)" opacity="0.5"/>
                        <circle cx="310" cy="50" r="25" fill="#ecfdf5" opacity="0.8"/>
                        <circle cx="90" cy="130" r="20" fill="#fffbeb" opacity="0.8"/>
                        <!-- Cap Illustration -->
                        <path d="M200 45L120 85L200 125L280 85L200 45Z" fill="var(--primary)" />
                        <path d="M150 100V135C150 148 172 158 200 158C228 158 250 148 250 135V100L200 125L150 100Z" fill="var(--primary-hover)" />
                        <path d="M280 85V135" stroke="#f59e0b" stroke-width="4" stroke-linecap="round"/>
                        <circle cx="280" cy="138" r="6" fill="#f59e0b"/>
                        <!-- Stars/Badges -->
                        <path d="M310 40L313 47L320 48L315 53L316 60L310 56L304 60L305 53L300 48L307 47L310 40Z" fill="#10b981"/>
                    </svg>
                </div>

                <div class="floating-support-card">
                    <h3>Your Future, Our Support</h3>
                    <p class="sub">Unlock verified opportunities. Achieve your dreams.</p>

                    <ul class="support-feature-list">
                        <li>
                            <div class="support-icon-pill">⭐</div>
                            <div>
                                <strong>35+ Verified Schemes</strong>
                                <span>MahaDBT, Central NSP, Tata & Corporate CSR</span>
                            </div>
                        </li>
                        <li>
                            <div class="support-icon-pill">🛡️</div>
                            <div>
                                <strong>Simplified Discovery</strong>
                                <span>Fast filters, eligibility matching, and official portal links</span>
                            </div>
                        </li>
                        <li>
                            <div class="support-icon-pill">📋</div>
                            <div>
                                <strong>Trusted Platform</strong>
                                <span>Transparent eligibility criteria and verified deadlines</span>
                            </div>
                        </li>
                    </ul>

                    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border); text-align: center;">
                        <?php if (is_logged_in()): ?>
                            <a href="dashboard.php" class="btn btn-primary btn-block">Go to Student Dashboard →</a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary btn-block">Get Started for Free →</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Scholarships Section -->
<section class="container" style="margin-bottom: 75px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="font-size: 2rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.5px; margin-bottom: 4px;">Top Recommended Opportunities</h2>
            <p style="color: var(--text-muted); font-size: 0.98rem; margin-bottom: 0;">Explore high-value grants with active application windows</p>
        </div>
        <a href="scholarships.php" class="btn btn-outline btn-sm">View All (<?= $total_sch ?>) →</a>
    </div>

    <div class="scholarship-grid">
        <?php foreach ($featured as $sch): 
            $is_saved = in_array($sch['scholarship_id'], $saved_ids);
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
                        <span class="card-chip" style="color: var(--success); font-weight: 800;">✓ Verified</span>
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
                        <?php if (is_logged_in()): ?>
                            <button class="btn btn-sm btn-save-scholarship <?= $is_saved ? 'btn-primary' : 'btn-outline' ?>" data-id="<?= $sch['scholarship_id'] ?>">
                                <?= $is_saved ? '★ Saved' : '☆ Save' ?>
                            </button>
                        <?php endif; ?>
                        <a href="scholarship_detail.php?id=<?= $sch['scholarship_id'] ?>" class="btn btn-sm btn-primary">Details & Docs →</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Dynamic Bottom Call to Action Section -->
<section style="background: var(--card-bg); padding: 55px 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); margin-bottom: 40px;">
    <div class="container" style="text-align: center;">
        <?php if (is_logged_in()): ?>
            <div style="max-width: 650px; margin: 0 auto;">
                <div style="display: inline-flex; align-items: center; gap: 8px; background: var(--success-light); color: var(--success); padding: 6px 16px; border-radius: var(--radius-pill); font-weight: 700; font-size: 0.88rem; margin-bottom: 16px;">
                    ✔ Student Account Active: <?= htmlspecialchars($_SESSION['name']) ?>
                </div>
                <h2 style="font-size: 2.1rem; font-weight: 800; margin-bottom: 8px; letter-spacing: -0.5px;">Ready to check your matched scholarships?</h2>
                <p style="color: var(--text-muted); margin-bottom: 26px; font-size: 1.02rem;">
                    Jump right into your personalized dashboard to see live match percentages and manage your bookmarks.
                </p>
                <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
                    <a href="dashboard.php" class="btn btn-primary btn-pill" style="padding: 13px 32px; font-size: 1rem;">
                        📊 Open Student Dashboard →
                    </a>
                    <a href="scholarships.php" class="btn btn-outline btn-pill" style="padding: 13px 26px;">
                        🔍 Browse All Schemes
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div style="max-width: 650px; margin: 0 auto;">
                <h2 style="font-size: 2.1rem; font-weight: 800; margin-bottom: 8px; letter-spacing: -0.5px;">Ready to find your scholarship?</h2>
                <p style="color: var(--text-muted); margin-bottom: 26px; font-size: 1.02rem;">
                    Create your free student account to generate your unique ScholarFind ID and unlock instant match calculations.
                </p>
                <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
                    <a href="register.php" class="btn btn-primary btn-pill" style="padding: 13px 32px; font-size: 1rem;">
                        Create Free Student Account
                    </a>
                    <a href="eligibility_checker.php" class="btn btn-outline btn-pill" style="padding: 13px 26px;">
                        Test Match Calculator
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
