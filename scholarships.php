<?php
/**
 * ScholarFind — Scholarship Directory, Live Search & Multi-Filter Engine
 * Responsive Mobile Drawer, Full Indian States/UTs, Standardized Education & Portal Badges
 */
require_once 'config/db.php';
require_once 'includes/auth_check.php';
$page_title = 'Find Scholarships - ScholarFind';
require_once 'includes/header.php';

$search = trim($_GET['search'] ?? '');
$edu_level = trim($_GET['edu_level'] ?? 'All');
$category = trim($_GET['category'] ?? 'All');
$state = trim($_GET['state'] ?? 'All');
$provider_type = trim($_GET['provider_type'] ?? 'All');
$gender = trim($_GET['gender'] ?? 'all');
$sort = trim($_GET['sort'] ?? 'deadline');

$query = "SELECT * FROM scholarships WHERE is_active = 1";
$params = [];

// 1. Search Query (Title, Provider, Source, Application Portal, Description, Course, Category)
if (!empty($search)) {
    $query .= " AND (title LIKE ? OR provider LIKE ? OR source LIKE ? OR application_portal LIKE ? OR description LIKE ? OR course LIKE ? OR category LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// 2. Education Level Filter (Standardized)
if ($edu_level !== 'All' && !empty($edu_level)) {
    $query .= " AND (education_level = 'All' OR education_level LIKE ?)";
    $params[] = "%$edu_level%";
}

// 3. Category Filter
if ($category !== 'All' && !empty($category)) {
    if ($category === 'General / Open' || $category === 'General') {
        $query .= " AND (category = 'All' OR category LIKE '%General%' OR category LIKE '%Open%' OR category LIKE '%EWS%')";
    } elseif ($category === 'VJ/NT' || $category === 'VJNT') {
        $query .= " AND (category = 'All' OR category LIKE '%VJ%' OR category LIKE '%NT%' OR category LIKE '%SBC%')";
    } else {
        $query .= " AND (category = 'All' OR category LIKE ?)";
        $params[] = "%$category%";
    }
}

// 4. State Filter (Maharashtra is a State, MahaDBT is a Portal)
if ($state !== 'All' && !empty($state)) {
    if ($state === 'Maharashtra') {
        $query .= " AND (state = 'All India' OR state = 'All' OR state = 'Maharashtra')";
    } else {
        $query .= " AND (state = 'All India' OR state = 'All' OR state LIKE ?)";
        $params[] = "%$state%";
    }
}

// 5. Provider / Scheme Type Filter
if ($provider_type !== 'All' && !empty($provider_type)) {
    if ($provider_type === 'govt') {
        $query .= " AND (provider LIKE '%Govt%' OR provider LIKE '%Government%' OR provider LIKE '%Ministry%' OR provider LIKE '%Department%' OR source LIKE '%MahaDBT%' OR source LIKE '%NSP%' OR application_portal LIKE '%MahaDBT%' OR application_portal LIKE '%NSP%')";
    } elseif ($provider_type === 'tata') {
        $query .= " AND (title LIKE '%Tata%' OR provider LIKE '%Tata%' OR source LIKE '%Tata%' OR application_portal LIKE '%Tata%')";
    } elseif ($provider_type === 'corporate') {
        $query .= " AND (provider LIKE '%Foundation%' OR provider LIKE '%CSR%' OR provider LIKE '%Reliance%' OR provider LIKE '%HDFC%' OR provider LIKE '%Aditya%' OR provider LIKE '%Infosys%' OR provider LIKE '%Kotak%' OR provider LIKE '%L’Oréal%' OR provider LIKE '%Colgate%' OR provider LIKE '%Wipro%')";
    }
}

// 6. Gender Filter
if ($gender !== 'all' && !empty($gender)) {
    $query .= " AND (gender_eligible = 'all' OR gender_eligible = ?)";
    $params[] = $gender;
}

// 7. Sorting Options
switch ($sort) {
    case 'amount_desc':
        $query .= " ORDER BY amount DESC";
        break;
    case 'amount_asc':
        $query .= " ORDER BY amount ASC";
        break;
    case 'newest':
        $query .= " ORDER BY created_at DESC";
        break;
    case 'alpha':
        $query .= " ORDER BY title ASC";
        break;
    case 'deadline':
    default:
        $query .= " ORDER BY deadline ASC";
        break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$scholarships = $stmt->fetchAll();

$saved_ids = [];
if (is_logged_in()) {
    $save_stmt = $pdo->prepare("SELECT scholarship_id FROM saved_scholarships WHERE user_id = ?");
    $save_stmt->execute([$_SESSION['user_id']]);
    $saved_ids = $save_stmt->fetchAll(PDO::FETCH_COLUMN);
}

$has_active_filters = (!empty($search) || $edu_level !== 'All' || $category !== 'All' || $state !== 'All' || $provider_type !== 'All' || $gender !== 'all');
?>

<style>
.search-results-layout {
    display: grid;
    grid-template-columns: 290px 1fr;
    gap: 32px;
    align-items: flex-start;
}

/* Mobile Filter Toggle Button */
.mobile-filter-bar {
    display: none;
    margin-bottom: 20px;
}

@media (max-width: 992px) {
    .search-results-layout {
        grid-template-columns: 1fr;
    }
    
    .filter-sidebar-card {
        display: none;
        margin-bottom: 24px;
    }

    .filter-sidebar-card.show-mobile {
        display: block;
        animation: fadeIn 0.25s ease;
    }

    .mobile-filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--card-bg);
        border: 1px solid var(--border);
        padding: 12px 18px;
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
    }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="container" style="margin-top: 36px; margin-bottom: 70px;">
    <!-- Page Header & Search Bar -->
    <div style="margin-bottom: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px; margin-bottom: 20px;">
            <div>
                <h1 style="font-size: 2.3rem; font-weight: 800; margin-bottom: 6px; letter-spacing: -0.6px;">Explore Verified Scholarships</h1>
                <p style="color: var(--text-muted); font-size: 1.02rem; margin: 0;">Search across Government of Maharashtra (MahaDBT), Central NSP schemes, Tata Trusts, and premier corporate CSR grants.</p>
            </div>
            <?php if ($has_active_filters): ?>
                <a href="scholarships.php" class="btn btn-sm btn-outline" style="font-weight: 700;">✕ Reset All Filters</a>
            <?php endif; ?>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="scholarships.php" style="background: var(--card-bg); border: 1.5px solid var(--border); border-radius: var(--radius-xl); padding: 8px 12px; display: flex; align-items: center; gap: 12px; box-shadow: var(--shadow-card);">
            <span style="font-size: 1.3rem; margin-left: 8px; color: var(--text-muted);">🔍</span>
            <input type="text" name="search" class="form-control" placeholder="Search by scholarship name, portal (e.g. 'MahaDBT', 'Tata', 'Reliance', 'SC', 'BCA')..." value="<?= htmlspecialchars($search) ?>" style="border: none; background: transparent; font-size: 1rem; box-shadow: none; padding: 10px;">
            
            <?php if (!empty($edu_level) && $edu_level !== 'All'): ?>
                <input type="hidden" name="edu_level" value="<?= htmlspecialchars($edu_level) ?>">
            <?php endif; ?>
            <?php if (!empty($category) && $category !== 'All'): ?>
                <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <?php endif; ?>
            <?php if (!empty($state) && $state !== 'All'): ?>
                <input type="hidden" name="state" value="<?= htmlspecialchars($state) ?>">
            <?php endif; ?>
            <?php if (!empty($provider_type) && $provider_type !== 'All'): ?>
                <input type="hidden" name="provider_type" value="<?= htmlspecialchars($provider_type) ?>">
            <?php endif; ?>
            <?php if (!empty($sort) && $sort !== 'deadline'): ?>
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <?php endif; ?>

            <?php if (!empty($search)): ?>
                <a href="scholarships.php" style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-decoration: none; padding: 0 8px;">✕ Clear</a>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary btn-pill" style="padding: 10px 26px; font-weight: 700; white-space: nowrap;">
                Search Schemes
            </button>
        </form>
    </div>

    <!-- Mobile Filter Bar Toggle -->
    <div class="mobile-filter-bar">
        <span style="font-weight: 700; font-size: 0.95rem;">Filter Options (<?= count($scholarships) ?> found)</span>
        <button type="button" class="btn btn-sm btn-outline" onclick="toggleMobileFilters()" id="mobile-filter-toggle-btn">
            ⚡ Open Filters
        </button>
    </div>

    <!-- Layout: Sidebar Filters + Main Grid -->
    <div class="search-results-layout">
        
        <!-- Filter Sidebar -->
        <div>
            <form method="GET" action="scholarships.php" id="filter-sidebar-form" class="filter-sidebar-card" style="background: var(--card-bg); padding: 26px; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-card); position: sticky; top: 95px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
                    <h3 style="font-size: 1.15rem; font-weight: 800;">Filter Options</h3>
                    <a href="scholarships.php" style="font-size: 0.85rem; color: var(--primary); font-weight: 700;">Reset All</a>
                </div>

                <?php if (!empty($search)): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <?php endif; ?>

                <!-- State / Domicile Filter (Expanded to all Indian States/UTs) -->
                <div class="form-group">
                    <label class="form-label">State / Domicile</label>
                    <select name="state" class="form-control" onchange="this.form.submit()">
                        <option value="All" <?= $state === 'All' ? 'selected' : '' ?>>All India (All States)</option>
                        <optgroup label="States">
                            <option value="Andhra Pradesh" <?= $state === 'Andhra Pradesh' ? 'selected' : '' ?>>Andhra Pradesh</option>
                            <option value="Arunachal Pradesh" <?= $state === 'Arunachal Pradesh' ? 'selected' : '' ?>>Arunachal Pradesh</option>
                            <option value="Assam" <?= $state === 'Assam' ? 'selected' : '' ?>>Assam</option>
                            <option value="Bihar" <?= $state === 'Bihar' ? 'selected' : '' ?>>Bihar</option>
                            <option value="Chhattisgarh" <?= $state === 'Chhattisgarh' ? 'selected' : '' ?>>Chhattisgarh</option>
                            <option value="Goa" <?= $state === 'Goa' ? 'selected' : '' ?>>Goa</option>
                            <option value="Gujarat" <?= $state === 'Gujarat' ? 'selected' : '' ?>>Gujarat</option>
                            <option value="Haryana" <?= $state === 'Haryana' ? 'selected' : '' ?>>Haryana</option>
                            <option value="Himachal Pradesh" <?= $state === 'Himachal Pradesh' ? 'selected' : '' ?>>Himachal Pradesh</option>
                            <option value="Jharkhand" <?= $state === 'Jharkhand' ? 'selected' : '' ?>>Jharkhand</option>
                            <option value="Karnataka" <?= $state === 'Karnataka' ? 'selected' : '' ?>>Karnataka</option>
                            <option value="Kerala" <?= $state === 'Kerala' ? 'selected' : '' ?>>Kerala</option>
                            <option value="Madhya Pradesh" <?= $state === 'Madhya Pradesh' ? 'selected' : '' ?>>Madhya Pradesh</option>
                            <option value="Maharashtra" <?= $state === 'Maharashtra' ? 'selected' : '' ?>>Maharashtra</option>
                            <option value="Manipur" <?= $state === 'Manipur' ? 'selected' : '' ?>>Manipur</option>
                            <option value="Meghalaya" <?= $state === 'Meghalaya' ? 'selected' : '' ?>>Meghalaya</option>
                            <option value="Mizoram" <?= $state === 'Mizoram' ? 'selected' : '' ?>>Mizoram</option>
                            <option value="Nagaland" <?= $state === 'Nagaland' ? 'selected' : '' ?>>Nagaland</option>
                            <option value="Odisha" <?= $state === 'Odisha' ? 'selected' : '' ?>>Odisha</option>
                            <option value="Punjab" <?= $state === 'Punjab' ? 'selected' : '' ?>>Punjab</option>
                            <option value="Rajasthan" <?= $state === 'Rajasthan' ? 'selected' : '' ?>>Rajasthan</option>
                            <option value="Sikkim" <?= $state === 'Sikkim' ? 'selected' : '' ?>>Sikkim</option>
                            <option value="Tamil Nadu" <?= $state === 'Tamil Nadu' ? 'selected' : '' ?>>Tamil Nadu</option>
                            <option value="Telangana" <?= $state === 'Telangana' ? 'selected' : '' ?>>Telangana</option>
                            <option value="Tripura" <?= $state === 'Tripura' ? 'selected' : '' ?>>Tripura</option>
                            <option value="Uttar Pradesh" <?= $state === 'Uttar Pradesh' ? 'selected' : '' ?>>Uttar Pradesh</option>
                            <option value="Uttarakhand" <?= $state === 'Uttarakhand' ? 'selected' : '' ?>>Uttarakhand</option>
                            <option value="West Bengal" <?= $state === 'West Bengal' ? 'selected' : '' ?>>West Bengal</option>
                        </optgroup>
                        <optgroup label="Union Territories">
                            <option value="Delhi" <?= $state === 'Delhi' ? 'selected' : '' ?>>Delhi (NCT)</option>
                            <option value="Jammu and Kashmir" <?= $state === 'Jammu and Kashmir' ? 'selected' : '' ?>>Jammu and Kashmir</option>
                            <option value="Ladakh" <?= $state === 'Ladakh' ? 'selected' : '' ?>>Ladakh</option>
                            <option value="Chandigarh" <?= $state === 'Chandigarh' ? 'selected' : '' ?>>Chandigarh</option>
                            <option value="Puducherry" <?= $state === 'Puducherry' ? 'selected' : '' ?>>Puducherry</option>
                        </optgroup>
                    </select>
                </div>

                <!-- Social Category Filter -->
                <div class="form-group">
                    <label class="form-label">Social / Caste Category</label>
                    <select name="category" class="form-control" onchange="this.form.submit()">
                        <option value="All" <?= $category === 'All' ? 'selected' : '' ?>>All Categories</option>
                        <option value="General / Open" <?= $category === 'General / Open' || $category === 'General' ? 'selected' : '' ?>>General / Open</option>
                        <option value="OBC" <?= $category === 'OBC' ? 'selected' : '' ?>>OBC (Other Backward Class)</option>
                        <option value="SC" <?= $category === 'SC' ? 'selected' : '' ?>>SC (Scheduled Caste)</option>
                        <option value="ST" <?= $category === 'ST' ? 'selected' : '' ?>>ST (Scheduled Tribe)</option>
                        <option value="EWS" <?= $category === 'EWS' ? 'selected' : '' ?>>EWS (Economically Weaker Section)</option>
                        <option value="VJ/NT" <?= $category === 'VJ/NT' || $category === 'VJNT' ? 'selected' : '' ?>>VJ/NT (Vimukta Jati / NT)</option>
                        <option value="SBC" <?= $category === 'SBC' ? 'selected' : '' ?>>SBC (Special Backward Class)</option>
                    </select>
                </div>

                <!-- Education Level Filter (Standardized) -->
                <div class="form-group">
                    <label class="form-label">Education Level</label>
                    <select name="edu_level" class="form-control" onchange="this.form.submit()">
                        <option value="All" <?= $edu_level === 'All' ? 'selected' : '' ?>>All Education Levels</option>
                        <option value="School" <?= $edu_level === 'School' ? 'selected' : '' ?>>School (Class 1–10)</option>
                        <option value="Class 11" <?= $edu_level === 'Class 11' ? 'selected' : '' ?>>Class 11</option>
                        <option value="Class 12" <?= $edu_level === 'Class 12' ? 'selected' : '' ?>>Class 12</option>
                        <option value="Diploma" <?= $edu_level === 'Diploma' ? 'selected' : '' ?>>Diploma / Polytechnic</option>
                        <option value="Undergraduate" <?= $edu_level === 'Undergraduate' ? 'selected' : '' ?>>Undergraduate (BCA, B.Tech, B.Sc, MBBS)</option>
                        <option value="Postgraduate" <?= $edu_level === 'Postgraduate' ? 'selected' : '' ?>>Postgraduate (MCA, M.Tech, MBA)</option>
                        <option value="PhD" <?= $edu_level === 'PhD' ? 'selected' : '' ?>>PhD / Research</option>
                        <option value="Vocational" <?= $edu_level === 'Vocational' ? 'selected' : '' ?>>Vocational / Skill Courses</option>
                    </select>
                </div>

                <!-- Provider / Grant Type Filter -->
                <div class="form-group">
                    <label class="form-label">Provider / Type</label>
                    <select name="provider_type" class="form-control" onchange="this.form.submit()">
                        <option value="All" <?= $provider_type === 'All' ? 'selected' : '' ?>>All Providers</option>
                        <option value="govt" <?= $provider_type === 'govt' ? 'selected' : '' ?>>Government Schemes (MahaDBT & Central)</option>
                        <option value="tata" <?= $provider_type === 'tata' ? 'selected' : '' ?>>Tata Trusts & Group</option>
                        <option value="corporate" <?= $provider_type === 'corporate' ? 'selected' : '' ?>>Corporate & Foundation CSR</option>
                    </select>
                </div>

                <!-- Gender Preference Filter -->
                 <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control" onchange="this.form.submit()">
                        <option value="all" <?= $gender === 'all' ? 'selected' : '' ?>>All Candidates</option>
                        <option value="female" <?= $gender === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="male" <?= $gender === 'male' ? 'selected' : '' ?>>Male</option>
                    </select>
                </div>

                <!-- Sort Order Filter -->
                <div class="form-group">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-control" onchange="this.form.submit()">
                        <option value="deadline" <?= $sort === 'deadline' ? 'selected' : '' ?>>Deadline: Closing Soonest</option>
                        <option value="amount_desc" <?= $sort === 'amount_desc' ? 'selected' : '' ?>>Grant: Highest First</option>
                        <option value="amount_asc" <?= $sort === 'amount_asc' ? 'selected' : '' ?>>Grant: Lowest First</option>
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Recently Added</option>
                        <option value="alpha" <?= $sort === 'alpha' ? 'selected' : '' ?>>Alphabetical (A - Z)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="padding: 12px; margin-top: 10px;">Apply Filters</button>
            </form>
        </div>

        <!-- Results Column -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <span style="font-weight: 800; color: var(--text-main); font-size: 1.05rem;">
                    Found <?= count($scholarships) ?> <?= count($scholarships) === 1 ? 'Scholarship' : 'Scholarships' ?>
                    <?php if (!empty($search)): ?>
                        for "<em><?= htmlspecialchars($search) ?></em>"
                    <?php endif; ?>
                </span>
            </div>

            <?php if (empty($scholarships)): ?>
                <!-- Clean Empty State Illustration & Content -->
                <div style="text-align: center; padding: 65px 24px; background: var(--card-bg); border-radius: var(--radius-xl); border: 1px solid var(--border); box-shadow: var(--shadow-card);">
                    <div style="max-width: 140px; margin: 0 auto 16px;">
                        <svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 100%; height: auto;">
                            <circle cx="60" cy="60" r="54" fill="var(--primary-light)" opacity="0.6"/>
                            <path d="M40 70C40 58.9543 48.9543 50 60 50C71.0457 50 80 58.9543 80 70V80H40V70Z" fill="var(--primary)" opacity="0.2"/>
                            <circle cx="54" cy="46" r="18" stroke="var(--primary)" stroke-width="4" fill="none"/>
                            <line x1="68" y1="60" x2="88" y2="80" stroke="var(--primary)" stroke-width="5" stroke-linecap="round"/>
                            <circle cx="54" cy="46" r="6" fill="var(--primary)"/>
                        </svg>
                    </div>
                    <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 8px;">No Scholarships Found</h2>
                    
                    <?php if (!empty($search)): ?>
                        <p style="color: var(--text-muted); max-width: 520px; margin: 0 auto 16px; font-size: 0.98rem; line-height: 1.6;">
                            Sorry, we couldn't find any scholarships matching "<strong><?= htmlspecialchars($search) ?></strong>" in our current database.
                        </p>
                    <?php else: ?>
                        <p style="color: var(--text-muted); max-width: 520px; margin: 0 auto 16px; font-size: 0.98rem; line-height: 1.6;">
                            No scholarships match the selected combination of filters in our current database.
                        </p>
                    <?php endif; ?>

                    <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 24px;">
                        Try checking the spelling, using a different keyword (e.g. <em>MahaDBT</em>, <em>Tata</em>, <em>Engineering</em>), changing your filters, or browsing all available scholarships.
                    </p>

                    <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                        <?php if (!empty($search)): ?>
                            <a href="scholarships.php" class="btn btn-outline btn-pill">Clear Search</a>
                        <?php endif; ?>
                        <a href="scholarships.php" class="btn btn-primary btn-pill">View All Scholarships →</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="scholarship-grid" style="margin-top: 0;">
                    <?php foreach ($scholarships as $sch): 
                        $is_saved = in_array($sch['scholarship_id'], $saved_ids);
                        $days_left = round((strtotime($sch['deadline']) - time()) / (60 * 60 * 24));
                        $portal_label = !empty($sch['application_portal']) ? $sch['application_portal'] : 'Official Portal';
                    ?>
                        <div class="scholarship-card">
                            <div>
                                <div class="card-top-row">
                                    <div class="card-provider-badge">
                                        <div class="provider-icon-circle">🏛️</div>
                                        <span><?= htmlspecialchars($sch['provider']) ?></span>
                                    </div>
                                    <span class="badge badge-info"><?= htmlspecialchars($sch['education_level']) ?></span>
                                </div>

                                <h3 class="card-title"><?= htmlspecialchars($sch['title']) ?></h3>

                                <div class="card-amount-box">
                                    <span class="amount-val">₹<?= number_format($sch['amount'], 0) ?></span>
                                    <span class="amount-label">/ academic year</span>
                                </div>

                                <div class="card-chips-row">
                                    <span class="card-chip">🏷️ <?= htmlspecialchars($sch['category']) ?></span>
                                    <span class="card-chip">📍 <?= htmlspecialchars($sch['state']) ?></span>
                                    <span class="card-chip" style="color: var(--primary); font-weight: 700;">🌐 <?= htmlspecialchars($portal_label) ?></span>
                                    <span class="card-chip" style="color: var(--success); font-weight: 800;">✓ Verified</span>
                                </div>

                                <p class="card-desc">
                                    <?= htmlspecialchars(substr($sch['description'], 0, 125)) ?>...
                                </p>
                            </div>

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
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleMobileFilters() {
    const sidebar = document.getElementById('filter-sidebar-form');
    const btn = document.getElementById('mobile-filter-toggle-btn');
    if (!sidebar) return;
    
    if (sidebar.classList.contains('show-mobile')) {
        sidebar.classList.remove('show-mobile');
        btn.innerText = '⚡ Open Filters';
    } else {
        sidebar.classList.add('show-mobile');
        btn.innerText = '✕ Close Filters';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
