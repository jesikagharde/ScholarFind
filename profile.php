<?php
/**
 * ScholarFind — Student Profile Hub & Eligibility Settings
 * All Indian States/UTs, Standardized Education Levels, Accurate Completeness & ScholarFind ID
 */
require_once 'config/db.php';
require_once 'includes/auth_check.php';

require_login();
validate_session_user($pdo);

$user_id = $_SESSION['user_id'];
$profile = get_student_profile($pdo, $user_id);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $gender = trim($_POST['gender'] ?? 'all');
    $state = trim($_POST['state'] ?? '');
    $education_level = trim($_POST['education_level'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $percentage = (float)($_POST['percentage'] ?? 0);
    $family_income = (float)($_POST['family_income'] ?? 0);
    $category = trim($_POST['category'] ?? '');

    if (empty($name)) {
        $error = "Name cannot be blank.";
    } elseif (empty($education_level)) {
        $error = "Academic Qualification / Education Level is required.";
    } else {
        try {
            $u_stmt = $pdo->prepare("UPDATE users SET name = ? WHERE user_id = ?");
            $u_stmt->execute([$name, $user_id]);
            $_SESSION['name'] = $name;

            if ($profile) {
                $p_stmt = $pdo->prepare("
                    UPDATE student_profiles SET 
                    gender = ?, state = ?, education_level = ?, course = ?, percentage = ?, family_income = ?, category = ?
                    WHERE user_id = ?
                ");
                $p_stmt->execute([$gender, $state, $education_level, $course, $percentage, $family_income, $category, $user_id]);
            } else {
                $p_stmt = $pdo->prepare("
                    INSERT INTO student_profiles (user_id, gender, state, education_level, course, percentage, family_income, category)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $p_stmt->execute([$user_id, $gender, $state, $education_level, $course, $percentage, $family_income, $category]);
            }

            $success = "🎉 Profile updated successfully! Your scholarship recommendations and eligibility scores have been recalculated.";
            $profile = get_student_profile($pdo, $user_id);
        } catch (Exception $e) {
            $error = "Failed to update profile: " . $e->getMessage();
        }
    }
}

// Generate initials for avatar
$name_parts = explode(' ', $_SESSION['name'] ?? 'Student User');
$initials = strtoupper(substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : ''));

// Calculate genuine completeness (no fake defaults)
$filled_fields = 0;
$total_fields = 8;
$missing = [];

if (!empty($_SESSION['name'])) $filled_fields++; else $missing[] = 'Full Name';
if (!empty($_SESSION['email'])) $filled_fields++; else $missing[] = 'Email Address';
if (!empty($profile['state'])) $filled_fields++; else $missing[] = 'State / Domicile';
if (!empty($profile['education_level'])) $filled_fields++; else $missing[] = 'Education Level';
if (!empty($profile['course'])) $filled_fields++; else $missing[] = 'Course / Stream';
if (!empty($profile['percentage']) && (float)$profile['percentage'] > 0) $filled_fields++; else $missing[] = 'Academic Percentage / Score';
if (!empty($profile['family_income']) && (float)$profile['family_income'] > 0) $filled_fields++; else $missing[] = 'Annual Family Income';
if (!empty($profile['category'])) $filled_fields++; else $missing[] = 'Social / Caste Category';

$completeness = round(($filled_fields / $total_fields) * 100);

$scholarfind_id = $_SESSION['scholarfind_id'] ?? '';
if (empty($scholarfind_id)) {
    $scholarfind_id = generate_scholarfind_id($pdo, $_SESSION['name']);
    $u_up = $pdo->prepare("UPDATE users SET scholarfind_id = ? WHERE user_id = ?");
    $u_up->execute([$scholarfind_id, $user_id]);
    $_SESSION['scholarfind_id'] = $scholarfind_id;
}

$page_title = 'My Profile - ScholarFind';
require_once 'includes/header.php';
?>

<style>
.profile-page-wrap {
    margin: 32px 0 70px;
}

/* Hero Avatar Card */
.profile-hero-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 32px;
    box-shadow: var(--shadow-card);
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
    position: relative;
    overflow: hidden;
}

.profile-hero-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--primary-gradient);
}

.profile-avatar-row {
    display: flex;
    align-items: center;
    gap: 20px;
}

.avatar-large-circle {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    background: var(--primary-gradient);
    color: #ffffff;
    font-size: 1.8rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 20px var(--primary-glow);
    flex-shrink: 0;
}

.profile-info h1 {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--text-main);
    letter-spacing: -0.5px;
    margin-bottom: 4px;
}

.profile-meta-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
    margin-top: 8px;
}

.meta-pill {
    background: var(--bg-main);
    border: 1px solid var(--border);
    padding: 4px 12px;
    border-radius: var(--radius-pill);
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-muted);
}

.meta-pill.id-pill {
    background: var(--primary-light);
    color: var(--primary);
    border-color: var(--primary-glow);
}

/* Completeness Box */
.completeness-box {
    background: var(--bg-main);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 18px 22px;
    min-width: 280px;
}

.completeness-top {
    display: flex;
    justify-content: space-between;
    font-size: 0.84rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.completeness-bar-bg {
    width: 100%;
    height: 8px;
    background: var(--border);
    border-radius: 4px;
    overflow: hidden;
}

.completeness-bar-fill {
    height: 100%;
    background: var(--success);
    border-radius: 4px;
    transition: width 0.6s ease;
}

/* 2-Column Form Layout */
.profile-grid-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 30px;
    align-items: flex-start;
}

@media (max-width: 992px) {
    .profile-grid-layout {
        grid-template-columns: 1fr;
    }
}

/* Left Quick Links Menu */
.profile-sidebar-nav {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 20px;
    box-shadow: var(--shadow-card);
    position: sticky;
    top: 95px;
}

.nav-item-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: var(--radius-sm);
    color: var(--text-muted);
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    transition: var(--transition);
    margin-bottom: 4px;
}

.nav-item-link:hover, .nav-item-link.active {
    background: var(--primary-light);
    color: var(--primary);
}

/* Main Form Card */
.profile-main-form-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 36px;
    box-shadow: var(--shadow-card);
}

.form-section-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--text-main);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-section-desc {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-bottom: 20px;
}

.form-section-divider {
    border: 0;
    height: 1px;
    background: var(--border);
    margin: 32px 0 28px;
}
</style>

<div class="container">
    <div class="profile-page-wrap">
        
        <!-- Hero Avatar Banner -->
        <div class="profile-hero-card">
            <div class="profile-avatar-row">
                <div class="avatar-large-circle">
                    <?= $initials ?>
                </div>
                <div class="profile-info">
                    <h1><?= htmlspecialchars($_SESSION['name'] ?? 'Student') ?></h1>
                    <div class="profile-meta-pills">
                        <span class="meta-pill id-pill">ScholarFind ID: <strong>@<?= htmlspecialchars($scholarfind_id) ?></strong></span>
                        <span class="badge badge-success">Verified Student</span>
                        <span class="meta-pill"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></span>
                        <?php if (!empty($profile['education_level'])): ?>
                            <span class="meta-pill">🎓 <?= htmlspecialchars($profile['education_level']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($profile['state'])): ?>
                            <span class="meta-pill">📍 <?= htmlspecialchars($profile['state']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Profile Completeness Meter -->
            <div class="completeness-box">
                <div class="completeness-top">
                    <span>Profile Completeness</span>
                    <strong style="color: <?= $completeness >= 80 ? 'var(--success)' : ($completeness >= 50 ? '#f59e0b' : '#ef4444') ?>;"><?= $completeness ?>%</strong>
                </div>
                <div class="completeness-bar-bg">
                    <div class="completeness-bar-fill" style="width: <?= $completeness ?>%; background: <?= $completeness >= 80 ? 'var(--success)' : ($completeness >= 50 ? '#f59e0b' : '#ef4444') ?>;"></div>
                </div>
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 8px;">
                    <?php if ((int)$completeness === 100): ?>
                        ✨ <strong>Profile Complete!</strong> Full eligibility calculation unlocked.
                    <?php else: ?>
                        ⚠️ <strong>Incomplete</strong> (Pending: <em><?= implode(', ', array_slice($missing, 0, 3)) ?></em>)
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 2-Column Workspace Grid -->
        <div class="profile-grid-layout">
            
            <!-- Left Navigation & Tips -->
            <div>
                <div class="profile-sidebar-nav">
                    <a href="profile.php" class="nav-item-link active">
                        <span>👤</span> Profile Settings
                    </a>
                    <a href="dashboard.php" class="nav-item-link">
                        <span>📊</span> Student Dashboard
                    </a>
                    <a href="saved.php" class="nav-item-link">
                        <span>⭐</span> Saved Scholarships
                    </a>
                    <a href="eligibility_checker.php" class="nav-item-link">
                        <span>⚡</span> Match Calculator
                    </a>
                </div>

                <div style="background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius-xl); padding: 22px; margin-top: 20px; box-shadow: var(--shadow-card);">
                    <div style="font-size: 0.95rem; font-weight: 800; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <span>💡</span> Why Complete Profile?
                    </div>
                    <p style="font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                        ScholarFind calculates eligibility dynamically based on your Academic Qualification, Score, Income, State, and Social Category. Complete all required fields to see 100% accurate match percentages!
                    </p>
                </div>
            </div>

            <!-- Right Form Settings -->
            <div>
                <div class="profile-main-form-card">
                    
                    <?php if (!empty($success)): ?>
                        <div style="background: var(--success-light); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.3); padding: 14px 18px; border-radius: var(--radius-sm); font-size: 0.92rem; font-weight: 700; margin-bottom: 24px;">
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div style="background: var(--danger-light); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.3); padding: 14px 18px; border-radius: var(--radius-sm); font-size: 0.92rem; font-weight: 700; margin-bottom: 24px;">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="profile.php">
                        
                        <!-- Section 1: Personal & Account Details -->
                        <div class="form-section-title">
                            <span>👤</span> 1. Personal & Account Details
                        </div>
                        <p class="form-section-desc">Your basic profile identity and auto-generated platform ID.</p>

                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_SESSION['name'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">ScholarFind ID (Unique Platform Identity)</label>
                                <input type="text" class="form-control" value="@<?= htmlspecialchars($scholarfind_id) ?>" disabled style="background: var(--bg-main); cursor: not-allowed; font-weight: 700; color: var(--primary);">
                            </div>
                        </div>

                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Email Address (Login ID)</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" disabled style="background: var(--bg-main); cursor: not-allowed;">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Gender *</label>
                                <select name="gender" class="form-control" required>
                                    <option value="female" <?= ($profile['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="male" <?= ($profile['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="other" <?= ($profile['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other / Prefer not to say</option>
                                    <option value="all" <?= ($profile['gender'] ?? '') === 'all' || empty($profile['gender']) ? 'selected' : '' ?>>All Candidates</option>
                                </select>
                            </div>
                        </div>

                        <hr class="form-section-divider">

                        <!-- Section 2: Academic Qualifications (MANDATORY) -->
                        <div class="form-section-title">
                            <span>🎓</span> 2. Academic Qualifications (Required for Eligibility)
                        </div>
                        <p class="form-section-desc">Select your current education level and enter your course and marks.</p>

                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Academic Qualification / Education Level *</label>
                                <select name="education_level" class="form-control" required>
                                    <option value="" disabled <?= empty($profile['education_level']) ? 'selected' : '' ?>>-- Select Education Level --</option>
                                    <option value="Class 1–5" <?= ($profile['education_level'] ?? '') === 'Class 1–5' ? 'selected' : '' ?>>Class 1–5</option>
                                    <option value="Class 6–8" <?= ($profile['education_level'] ?? '') === 'Class 6–8' ? 'selected' : '' ?>>Class 6–8</option>
                                    <option value="Class 9–10" <?= ($profile['education_level'] ?? '') === 'Class 9–10' ? 'selected' : '' ?>>Class 9–10</option>
                                    <option value="Class 11" <?= ($profile['education_level'] ?? '') === 'Class 11' ? 'selected' : '' ?>>Class 11</option>
                                    <option value="Class 12" <?= ($profile['education_level'] ?? '') === 'Class 12' ? 'selected' : '' ?>>Class 12</option>
                                    <option value="Diploma / Polytechnic" <?= ($profile['education_level'] ?? '') === 'Diploma / Polytechnic' ? 'selected' : '' ?>>Diploma / Polytechnic</option>
                                    <option value="Undergraduate" <?= ($profile['education_level'] ?? '') === 'Undergraduate' ? 'selected' : '' ?>>Undergraduate</option>
                                    <option value="Postgraduate" <?= ($profile['education_level'] ?? '') === 'Postgraduate' ? 'selected' : '' ?>>Postgraduate</option>
                                    <option value="PhD / Research" <?= ($profile['education_level'] ?? '') === 'PhD / Research' ? 'selected' : '' ?>>PhD / Research</option>
                                    <option value="Vocational / Skill Course" <?= ($profile['education_level'] ?? '') === 'Vocational / Skill Course' ? 'selected' : '' ?>>Vocational / Skill Course</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Course / Stream / Field *</label>
                                <input type="text" name="course" class="form-control" value="<?= htmlspecialchars($profile['course'] ?? '') ?>" placeholder="e.g. BCA, Computer Engineering, B.Sc Chemistry, MBBS" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Qualifying Academic Percentage / CGPA (%) *</label>
                            <input type="number" step="0.01" min="0" max="100" name="percentage" class="form-control" value="<?= (float)($profile['percentage'] ?? 0) > 0 ? (float)$profile['percentage'] : '' ?>" placeholder="e.g. 78.5" required>
                            <span style="font-size: 0.78rem; color: var(--text-muted);">Enter your percentage from your most recent qualifying examination.</span>
                        </div>

                        <hr class="form-section-divider">

                        <!-- Section 3: Financial & Category Quota -->
                        <div class="form-section-title">
                            <span>💰</span> 3. Financial & Category Quota (Required for Schemes)
                        </div>
                        <p class="form-section-desc">Required for government fee reimbursements, EBC, and foundation grants.</p>

                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label class="form-label">Annual Family Income (₹) *</label>
                                <input type="number" step="1000" min="0" name="family_income" class="form-control" value="<?= (float)($profile['family_income'] ?? 0) > 0 ? (float)$profile['family_income'] : '' ?>" placeholder="e.g. 250000" required>
                                <span style="font-size: 0.78rem; color: var(--text-muted);">Enter total annual household income as shown in your income certificate.</span>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Social / Caste Category *</label>
                                <?php $current_cat = $profile['category'] ?? ''; ?>
                                <select name="category" class="form-control" required>
                                    <option value="" disabled <?= empty($current_cat) ? 'selected' : '' ?>>-- Select Social Category --</option>
                                    <option value="General / Open" <?= $current_cat === 'General / Open' || $current_cat === 'General' ? 'selected' : '' ?>>General / Open</option>
                                    <option value="OBC" <?= $current_cat === 'OBC' ? 'selected' : '' ?>>OBC (Other Backward Class)</option>
                                    <option value="SC" <?= $current_cat === 'SC' ? 'selected' : '' ?>>SC (Scheduled Caste)</option>
                                    <option value="ST" <?= $current_cat === 'ST' ? 'selected' : '' ?>>ST (Scheduled Tribe)</option>
                                    <option value="EWS" <?= $current_cat === 'EWS' ? 'selected' : '' ?>>EWS (Economically Weaker Section)</option>
                                    <option value="VJ/NT" <?= $current_cat === 'VJ/NT' || $current_cat === 'VJNT' ? 'selected' : '' ?>>VJ/NT (Vimukta Jati / Nomadic Tribes)</option>
                                    <option value="SBC" <?= $current_cat === 'SBC' ? 'selected' : '' ?>>SBC (Special Backward Class)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Home State / Domicile *</label>
                            <input list="states-list" name="state" class="form-control" placeholder="Select or search your State..." value="<?= htmlspecialchars($profile['state'] ?? '') ?>" required>
                            <datalist id="states-list">
                                <!-- 28 Indian States -->
                                <option value="Andhra Pradesh">
                                <option value="Arunachal Pradesh">
                                <option value="Assam">
                                <option value="Bihar">
                                <option value="Chhattisgarh">
                                <option value="Goa">
                                <option value="Gujarat">
                                <option value="Haryana">
                                <option value="Himachal Pradesh">
                                <option value="Jharkhand">
                                <option value="Karnataka">
                                <option value="Kerala">
                                <option value="Madhya Pradesh">
                                <option value="Maharashtra">
                                <option value="Manipur">
                                <option value="Meghalaya">
                                <option value="Mizoram">
                                <option value="Nagaland">
                                <option value="Odisha">
                                <option value="Punjab">
                                <option value="Rajasthan">
                                <option value="Sikkim">
                                <option value="Tamil Nadu">
                                <option value="Telangana">
                                <option value="Tripura">
                                <option value="Uttar Pradesh">
                                <option value="Uttarakhand">
                                <option value="West Bengal">
                                <!-- 8 Union Territories -->
                                <option value="Andaman and Nicobar Islands">
                                <option value="Chandigarh">
                                <option value="Dadra and Nagar Haveli and Daman and Diu">
                                <option value="Delhi">
                                <option value="Jammu and Kashmir">
                                <option value="Ladakh">
                                <option value="Lakshadweep">
                                <option value="Puducherry">
                            </datalist>
                        </div>

                        <div style="margin-top: 32px; display: flex; gap: 14px; align-items: center;">
                            <button type="submit" class="btn btn-primary btn-pill" style="padding: 13px 32px; font-size: 1rem;">
                                💾 Save Profile Details →
                            </button>
                            <a href="dashboard.php" class="btn btn-outline btn-pill" style="padding: 13px 22px;">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
