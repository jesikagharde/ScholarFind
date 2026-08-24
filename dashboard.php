<?php
/**
 * ScholarFind — Interactive Student Dashboard
 * Full-width 3-column workspace with live scholarship data
 */
require_once 'config/db.php';
require_once 'includes/auth_check.php';
require_once 'includes/eligibility_engine.php';

require_login();
validate_session_user($pdo);

if (is_admin()) {
    header("Location: admin/index.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$profile = get_student_profile($pdo, $user_id);

$is_profile_incomplete = empty($profile['education_level']) ||
    empty($profile['state']) ||
    (float)($profile['percentage'] ?? 0) <= 0;

$display_profile = $profile ?: [
    'education_level' => '',
    'course' => '',
    'year' => 1,
    'percentage' => 0.0,
    'family_income' => 0.0,
    'state' => '',
    'category' => '',
    'gender' => 'all'
];

$all_scholarships = $pdo->query("
    SELECT *
    FROM scholarships
    WHERE is_active = 1
    ORDER BY deadline ASC, amount DESC
")->fetchAll();

$total_scholarships = count($all_scholarships);
$eligible_list = [];
$possible_list = [];
$incomplete_list = [];
$not_eligible_list = [];

foreach ($all_scholarships as $sch) {
    $eval = calculate_eligibility($display_profile, $sch);
    $item = [
        'sch' => $sch,
        'eval' => $eval
    ];

    if ($eval['is_incomplete'] ?? false) {
        $incomplete_list[] = $item;
    } elseif (($eval['is_eligible'] ?? false) || ($eval['score'] ?? 0) >= 90) {
        $eligible_list[] = $item;
    } elseif (($eval['score'] ?? 0) >= 50) {
        $possible_list[] = $item;
    } else {
        $not_eligible_list[] = $item;
    }
}

$saved_stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM saved_scholarships
    WHERE user_id = ?
");
$saved_stmt->execute([$user_id]);
$saved_count = (int)$saved_stmt->fetchColumn();

$verified_schemes_count = $total_scholarships;
$scholarfind_id = $_SESSION['scholarfind_id'] ?? '';
$page_title = 'ScholarFind Dashboard - Welcome ' . htmlspecialchars(explode(' ', $_SESSION['name'])[0]);
require_once 'includes/header.php';
?>

<div class="container">
    <div class="scholarfind-dashboard-main">
        <div class="dash-greeting-block">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h1 style="margin: 0;">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?></h1>
                    <?php if (!empty($scholarfind_id)): ?>
                        <span class="badge badge-info" style="font-size: 0.85rem; padding: 5px 14px; border-radius: var(--radius-pill); font-weight: 700;">
                            ScholarFind ID: @<?= htmlspecialchars($scholarfind_id) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <p style="margin-top: 6px;">Explore scholarship opportunities from government portals, foundations, universities and other organizations.</p>
            </div>
            <a href="profile.php" class="btn btn-outline btn-pill" style="padding: 10px 22px;">
                👤 Edit Student Profile
            </a>
        </div>

        <?php if ($is_profile_incomplete): ?>
            <div style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.12) 0%, rgba(79, 70, 229, 0.06) 100%); border: 1.5px solid rgba(99, 102, 241, 0.3); border-radius: var(--radius-xl); padding: 22px 28px; margin-bottom: 28px; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        📝
                    </div>
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin-bottom: 3px;">Set up your academic details</h3>
                        <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0;">Complete your Academic Qualification, Score, Income and State in your profile to get more accurate eligibility results.</p>
                    </div>
                </div>
                <a href="profile.php" class="btn btn-primary btn-pill" style="padding: 11px 26px; font-size: 0.92rem;">
                    Complete Profile Now →
                </a>
            </div>
        <?php endif; ?>

        <div class="metrics-pills-container">
            <div class="metric-pill-box metric-blue">
                <div class="number"><?= $total_scholarships ?></div>
                <div class="label">Scholarships Found</div>
            </div>
            <div class="metric-pill-box metric-green">
                <div class="number"><?= count($eligible_list) ?></div>
                <div class="label">Strong Matches</div>
            </div>
            <div class="metric-pill-box metric-orange">
                <div class="number"><?= count($possible_list) ?></div>
                <div class="label">Possible Matches</div>
            </div>
            <div class="metric-pill-box metric-pink">
                <div class="number"><?= $saved_count ?></div>
                <div class="label">Saved Bookmarks</div>
            </div>
            <div class="metric-pill-box metric-indigo">
                <div class="number"><?= $verified_schemes_count ?></div>
                <div class="label">Available Schemes</div>
            </div>
        </div>

        <div class="workspace-columns-grid">
            <div>
                <div class="dash-card-panel" style="margin-bottom: 24px;">
                    <div class="panel-title-bar">
                        <h2>⭐ Recommended For You</h2>
                        <a href="scholarships.php" class="link-action">View All (<?= $total_scholarships ?>) →</a>
                    </div>

                    <div class="recommended-list">
                        <?php
                        $rec_items = array_slice($eligible_list, 0, 3);
                        if (empty($rec_items)) {
                            $rec_items = array_slice($possible_list, 0, 3);
                        }
                        if (empty($rec_items)) {
                            foreach (array_slice($all_scholarships, 0, 3) as $sch) {
                                $rec_items[] = [
                                    'sch' => $sch,
                                    'eval' => calculate_eligibility($display_profile, $sch)
                                ];
                            }
                        }

                        foreach ($rec_items as $item):
                            $sch = $item['sch'];
                            $eval = $item['eval'];
                        ?>
                            <div class="rec-mini-card">
                                <div class="rec-top">
                                    <h3><?= htmlspecialchars($sch['title']) ?></h3>
                                    <span class="badge <?= htmlspecialchars($eval['badge_class'] ?? 'badge-info') ?>">
                                        <?= htmlspecialchars($eval['status_label'] ?? 'Available') ?>
                                    </span>
                                </div>
                                <p class="provider-text">🏛️ <?= htmlspecialchars($sch['provider']) ?></p>

                                <div class="rec-info-grid">
                                    <div>
                                        <span>Grant Value</span>
                                        <strong style="color: var(--success);">₹<?= number_format((float)$sch['amount'], 0) ?></strong>
                                    </div>
                                    <div>
                                        <span>Deadline</span>
                                        <strong><?= !empty($sch['deadline']) ? date('d M Y', strtotime($sch['deadline'])) : 'Check portal' ?></strong>
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                                    <span style="font-size: 0.76rem; color: var(--primary); font-weight: 700;">
                                        🌐 <?= htmlspecialchars($sch['application_portal'] ?? 'Official Portal') ?>
                                    </span>
                                    <a href="scholarship_detail.php?id=<?= (int)$sch['scholarship_id'] ?>" style="font-size: 0.84rem; font-weight: 700; color: var(--primary);">
                                        View Details →
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="deadline-alert-panel">
                    <div class="panel-title-bar" style="margin-bottom: 8px;">
                        <h2 style="color: var(--danger); display: flex; align-items: center; gap: 6px;">
                            <span>🔔</span> Urgent Deadline Alerts
                        </h2>
                        <a href="scholarships.php?sort=deadline" class="link-action">View All</a>
                    </div>

                    <?php
                    $closing_soon = $pdo->query("
                        SELECT *
                        FROM scholarships
                        WHERE is_active = 1
                        AND deadline >= CURDATE()
                        ORDER BY deadline ASC
                        LIMIT 2
                    ")->fetchAll();

                    if (!empty($closing_soon)):
                        foreach ($closing_soon as $cs):
                            $days_left = (int)ceil((strtotime($cs['deadline']) - strtotime(date('Y-m-d'))) / 86400);
                    ?>
                            <div class="deadline-pill-row" style="margin-bottom: 8px;">
                                <div>
                                    <div class="name"><?= htmlspecialchars($cs['title']) ?></div>
                                    <div style="font-size: 0.76rem; color: var(--text-muted); margin-top: 2px;">
                                        🏛️ <?= htmlspecialchars($cs['provider']) ?>
                                    </div>
                                </div>
                                <span class="time-badge">
                                    ⏳ <?= $days_left > 1 ? $days_left . ' days left' : ($days_left === 1 ? '1 day left' : 'Closing today') ?>
                                </span>
                            </div>
                    <?php
                        endforeach;
                    else:
                    ?>
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin: 8px 0 0;">
                            No upcoming scholarship deadlines found.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="dash-card-panel">
                    <div class="panel-title-bar">
                        <h2>⚡ Instant Eligibility Matcher</h2>
                    </div>
                    <p class="checker-subtext">
                        Test your details against available scholarship schemes.
                    </p>

                    <form id="dash-checker-form">
                        <div class="form-group">
                            <label class="form-label">Academic Qualification</label>
                            <select id="dash-edu" class="form-control">
                                <option value="Undergraduate" <?= ($display_profile['education_level'] ?? '') === 'Undergraduate' ? 'selected' : '' ?>>Undergraduate (BCA, B.Tech, B.Sc, MBBS)</option>
                                <option value="School" <?= ($display_profile['education_level'] ?? '') === 'School' ? 'selected' : '' ?>>School (Class 1–10)</option>
                                <option value="Class 11" <?= ($display_profile['education_level'] ?? '') === 'Class 11' ? 'selected' : '' ?>>Class 11</option>
                                <option value="Class 12" <?= ($display_profile['education_level'] ?? '') === 'Class 12' ? 'selected' : '' ?>>Class 12</option>
                                <option value="Diploma" <?= ($display_profile['education_level'] ?? '') === 'Diploma' ? 'selected' : '' ?>>Diploma / Polytechnic</option>
                                <option value="Postgraduate" <?= ($display_profile['education_level'] ?? '') === 'Postgraduate' ? 'selected' : '' ?>>Postgraduate (MCA, M.Tech, MBA)</option>
                                <option value="PhD" <?= ($display_profile['education_level'] ?? '') === 'PhD' ? 'selected' : '' ?>>Ph.D. / Research</option>
                                <option value="Vocational" <?= ($display_profile['education_level'] ?? '') === 'Vocational' ? 'selected' : '' ?>>Vocational / Skill Courses</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Course / Stream</label>
                            <input type="text" id="dash-course" class="form-control" value="<?= htmlspecialchars($display_profile['course'] ?? '') ?>" placeholder="e.g. BCA, Computer Science">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Qualifying Percentage (%)</label>
                            <input type="number" step="0.1" min="0" max="100" id="dash-percentage" class="form-control" value="<?= (float)($display_profile['percentage'] ?? 0) > 0 ? (float)$display_profile['percentage'] : '75.0' ?>" placeholder="e.g. 75.0">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Annual Family Income (₹)</label>
                            <input type="number" step="5000" min="0" id="dash-income" class="form-control" value="<?= (float)($display_profile['family_income'] ?? 0) > 0 ? (float)$display_profile['family_income'] : '250000' ?>" placeholder="e.g. 250000">
                            <span style="font-size: 0.76rem; color: var(--text-muted);">Enter your annual household income</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Home State / Domicile</label>
                            <select id="dash-state" class="form-control">
                                <option value="Maharashtra" <?= ($display_profile['state'] ?? '') === 'Maharashtra' ? 'selected' : '' ?>>Maharashtra</option>
                                <option value="Karnataka" <?= ($display_profile['state'] ?? '') === 'Karnataka' ? 'selected' : '' ?>>Karnataka</option>
                                <option value="Delhi" <?= ($display_profile['state'] ?? '') === 'Delhi' ? 'selected' : '' ?>>Delhi (NCT)</option>
                                <option value="Uttar Pradesh" <?= ($display_profile['state'] ?? '') === 'Uttar Pradesh' ? 'selected' : '' ?>>Uttar Pradesh</option>
                                <option value="West Bengal" <?= ($display_profile['state'] ?? '') === 'West Bengal' ? 'selected' : '' ?>>West Bengal</option>
                                <option value="Tamil Nadu" <?= ($display_profile['state'] ?? '') === 'Tamil Nadu' ? 'selected' : '' ?>>Tamil Nadu</option>
                                <option value="Gujarat" <?= ($display_profile['state'] ?? '') === 'Gujarat' ? 'selected' : '' ?>>Gujarat</option>
                                <option value="Madhya Pradesh" <?= ($display_profile['state'] ?? '') === 'Madhya Pradesh' ? 'selected' : '' ?>>Madhya Pradesh</option>
                                <option value="Rajasthan" <?= ($display_profile['state'] ?? '') === 'Rajasthan' ? 'selected' : '' ?>>Rajasthan</option>
                                <option value="All India" <?= ($display_profile['state'] ?? '') === 'All India' || ($display_profile['state'] ?? '') === 'All' ? 'selected' : '' ?>>All India (Other)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Social / Caste Category</label>
                            <select id="dash-category" class="form-control">
                                <option value="General / Open" <?= ($display_profile['category'] ?? '') === 'General / Open' || ($display_profile['category'] ?? '') === 'General' ? 'selected' : '' ?>>General / Open</option>
                                <option value="OBC" <?= ($display_profile['category'] ?? '') === 'OBC' ? 'selected' : '' ?>>OBC</option>
                                <option value="SC" <?= ($display_profile['category'] ?? '') === 'SC' ? 'selected' : '' ?>>SC</option>
                                <option value="ST" <?= ($display_profile['category'] ?? '') === 'ST' ? 'selected' : '' ?>>ST</option>
                                <option value="EWS" <?= ($display_profile['category'] ?? '') === 'EWS' ? 'selected' : '' ?>>EWS</option>
                                <option value="VJ/NT" <?= ($display_profile['category'] ?? '') === 'VJ/NT' || ($display_profile['category'] ?? '') === 'VJNT' ? 'selected' : '' ?>>VJ/NT</option>
                                <option value="SBC" <?= ($display_profile['category'] ?? '') === 'SBC' ? 'selected' : '' ?>>SBC</option>
                            </select>
                        </div>

                        <button type="button" id="btn-dash-check" class="btn btn-primary btn-block" style="padding: 13px; font-size: 1rem; margin-top: 14px;">
                            🛡️ Check Live Eligibility
                        </button>
                    </form>
                </div>
            </div>

            <div>
                <div class="dash-card-panel">
                    <div class="panel-title-bar">
                        <h2>Scholarships Found (<?= $total_scholarships ?>)</h2>
                    </div>

                    <div class="filter-tabs-bar">
                        <button class="filter-tab-btn active" onclick="filterFeed('all', this)">All (<?= $total_scholarships ?>)</button>
                        <button class="filter-tab-btn" onclick="filterFeed('eligible', this)">Strong (<?= count($eligible_list) ?>)</button>
                        <button class="filter-tab-btn" onclick="filterFeed('possible', this)">Possible (<?= count($possible_list) ?>)</button>
                        <button class="filter-tab-btn" onclick="filterFeed('not-eligible', this)">Low Match (<?= count($not_eligible_list) ?>)</button>
                    </div>

                    <div class="found-feed-stack" id="found-feed-container">
                        <?php
                        $feed_all = array_merge(
                            $eligible_list,
                            $possible_list,
                            $incomplete_list,
                            $not_eligible_list
                        );

                        foreach ($feed_all as $item):
                            $sch = $item['sch'];
                            $eval = $item['eval'];
                            if ($eval['is_incomplete'] ?? false) {
                                $group_type = 'incomplete';
                            } elseif (($eval['is_eligible'] ?? false) || ($eval['score'] ?? 0) >= 90) {
                                $group_type = 'eligible';
                            } elseif (($eval['score'] ?? 0) >= 50) {
                                $group_type = 'possible';
                            } else {
                                $group_type = 'not-eligible';
                            }
                        ?>
                            <div class="feed-item-card" data-group="<?= $group_type ?>">
                                <div class="card-head">
                                    <div class="head-left">
                                        <div class="icon-capsule">🎓</div>
                                        <div>
                                            <h4><?= htmlspecialchars($sch['title']) ?></h4>
                                            <p class="provider">🏛️ <?= htmlspecialchars($sch['provider']) ?></p>
                                        </div>
                                    </div>
                                    <span class="badge <?= htmlspecialchars($eval['badge_class'] ?? 'badge-info') ?>">
                                        <?= htmlspecialchars($eval['status_label'] ?? 'Available') ?>
                                    </span>
                                </div>

                                <div class="tag-chips">
                                    <span><?= htmlspecialchars($sch['education_level']) ?></span>
                                    <span><?= htmlspecialchars($sch['category']) ?></span>
                                    <span><?= htmlspecialchars($sch['state']) ?></span>
                                    <span style="color: var(--primary); font-weight: 700;">
                                        🌐 <?= htmlspecialchars($sch['application_portal'] ?? 'Official Portal') ?>
                                    </span>
                                </div>

                                <div class="card-foot">
                                    <span class="amount">₹<?= number_format((float)$sch['amount'], 0) ?></span>
                                    <a href="scholarship_detail.php?id=<?= (int)$sch['scholarship_id'] ?>" style="font-weight: 700; font-size: 0.84rem; color: var(--primary);">
                                        View Details →
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterFeed(group, btn) {
    document.querySelectorAll('.filter-tab-btn').forEach(button => {
        button.classList.remove('active');
    });
    btn.classList.add('active');

    document.querySelectorAll('.feed-item-card').forEach(card => {
        const cardGroup = card.getAttribute('data-group');
        if (group === 'all' || cardGroup === group) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

document.getElementById('btn-dash-check')?.addEventListener('click', () => {
    const params = new URLSearchParams({
        education_level: document.getElementById('dash-edu').value,
        course: document.getElementById('dash-course').value.trim(),
        percentage: document.getElementById('dash-percentage').value || 0,
        family_income: document.getElementById('dash-income').value || 0,
        state: document.getElementById('dash-state').value,
        category: document.getElementById('dash-category').value
    });
    window.location.href = 'eligibility.php?' + params.toString();
});
</script>

<?php require_once 'includes/footer.php'; ?>