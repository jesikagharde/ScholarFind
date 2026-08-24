<?php
/**
 * ScholarFind — Smart Interactive Eligibility Calculator
 */
require_once 'config/db.php';
require_once 'includes/auth_check.php';
require_once 'includes/eligibility_engine.php';

$page_title = 'Smart Eligibility Calculator - ScholarFind';
require_once 'includes/header.php';

$student_profile = null;
if (is_logged_in()) {
    $student_profile = get_student_profile($pdo, $_SESSION['user_id']);
}

$default_profile = $student_profile ?: [
    'education_level' => '',
    'course' => '',
    'percentage' => '',
    'family_income' => '',
    'state' => '',
    'category' => '',
    'gender' => ''
];

$states = [
    'Andhra Pradesh',
    'Arunachal Pradesh',
    'Assam',
    'Bihar',
    'Chhattisgarh',
    'Goa',
    'Gujarat',
    'Haryana',
    'Himachal Pradesh',
    'Jharkhand',
    'Karnataka',
    'Kerala',
    'Madhya Pradesh',
    'Maharashtra',
    'Manipur',
    'Meghalaya',
    'Mizoram',
    'Nagaland',
    'Odisha',
    'Punjab',
    'Rajasthan',
    'Sikkim',
    'Tamil Nadu',
    'Telangana',
    'Tripura',
    'Uttar Pradesh',
    'Uttarakhand',
    'West Bengal',
    'Andaman and Nicobar Islands',
    'Chandigarh',
    'Dadra and Nagar Haveli and Daman and Diu',
    'Delhi',
    'Jammu and Kashmir',
    'Ladakh',
    'Lakshadweep',
    'Puducherry'
];

$all_scholarships = $pdo->query("SELECT * FROM scholarships WHERE is_active = 1 ORDER BY amount DESC")->fetchAll();
?>

<style>
.calc-page-header {
    text-align: center;
    margin: 40px auto 32px;
    max-width: 700px;
}
.calc-page-header h1 {
    font-size: 2.4rem;
    font-weight: 800;
    color: var(--text-main);
    letter-spacing: -0.8px;
    margin-bottom: 8px;
}
.calc-page-header p {
    color: var(--text-muted);
    font-size: 1.02rem;
    line-height: 1.6;
}
.calc-workspace-grid {
    display: grid;
    grid-template-columns: 460px 1fr;
    gap: 32px;
    margin-bottom: 70px;
    align-items: flex-start;
}
@media (max-width: 1024px) {
    .calc-workspace-grid {
        grid-template-columns: 1fr;
    }
}
.calc-form-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 32px;
    box-shadow: var(--shadow-card);
    position: sticky;
    top: 95px;
    max-height: calc(100vh - 115px);
    overflow-y: auto;
}
.calc-form-card h2 {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--text-main);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.calc-form-card p.sub {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-bottom: 22px;
}
.calc-form-card form {
    padding-bottom: 60px;
}
.calc-submit-area {
    position: sticky;
    bottom: -32px;
    background: var(--card-bg);
    padding: 14px 0 0;
    margin-top: 8px;
    border-top: 1px solid var(--border);
}
.calc-results-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 32px;
    box-shadow: var(--shadow-card);
}
.results-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1px solid var(--border);
    padding-bottom: 16px;
}
.results-header-bar h2 {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--text-main);
}
.calc-filter-pills {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.calc-pill-btn {
    padding: 6px 14px;
    border-radius: var(--radius-pill);
    font-size: 0.8rem;
    font-weight: 700;
    border: 1px solid var(--border);
    background: var(--bg-main);
    color: var(--text-muted);
    cursor: pointer;
    transition: var(--transition);
}
.calc-pill-btn:hover,
.calc-pill-btn.active {
    background: var(--primary);
    color: #ffffff !important;
    border-color: var(--primary);
}
.calc-cards-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.calc-match-item {
    background: var(--bg-main);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 22px;
    transition: var(--transition);
}
.calc-match-item:hover {
    background: var(--card-bg);
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-card-hover);
}
.calc-item-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 10px;
}
.calc-item-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--text-main);
    line-height: 1.35;
}
.calc-item-provider {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 2px;
}
.calc-item-meta-chips {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin: 12px 0 16px;
}
.calc-item-meta-chips span {
    font-size: 0.76rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: var(--radius-pill);
    background: var(--card-bg);
    border: 1px solid var(--border);
    color: var(--text-muted);
}
.calc-item-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 14px;
    border-top: 1px solid var(--border);
}
</style>

<div class="container">
    <div class="calc-page-header">
        <h1>⚡ Smart Eligibility Calculator</h1>
        <p>Check your academic and financial criteria across Government, NSP, state and corporate scholarship programs.</p>
    </div>

    <div class="calc-workspace-grid">
        <div class="calc-form-card">
            <h2><span>🎯</span> Set Your Profile Criteria</h2>
            <p class="sub">Enter your details to calculate your scholarship matches.</p>

            <form id="eligibility-calc-form">
                <div class="form-group">
                    <label class="form-label">Academic Qualification / Education Level</label>
                    <select id="calc-edu" class="form-control">
                        <option value="">Select Education Level</option>
                        <option value="Class 1–5" <?= ($default_profile['education_level'] ?? '') === 'Class 1–5' ? 'selected' : '' ?>>Class 1–5</option>
                        <option value="Class 6–8" <?= ($default_profile['education_level'] ?? '') === 'Class 6–8' ? 'selected' : '' ?>>Class 6–8</option>
                        <option value="Class 9–10" <?= ($default_profile['education_level'] ?? '') === 'Class 9–10' ? 'selected' : '' ?>>Class 9–10</option>
                        <option value="Class 11" <?= ($default_profile['education_level'] ?? '') === 'Class 11' ? 'selected' : '' ?>>Class 11</option>
                        <option value="Class 12" <?= ($default_profile['education_level'] ?? '') === 'Class 12' ? 'selected' : '' ?>>Class 12</option>
                        <option value="Diploma / Polytechnic" <?= ($default_profile['education_level'] ?? '') === 'Diploma / Polytechnic' ? 'selected' : '' ?>>Diploma / Polytechnic</option>
                        <option value="Undergraduate" <?= ($default_profile['education_level'] ?? '') === 'Undergraduate' ? 'selected' : '' ?>>Undergraduate</option>
                        <option value="Postgraduate" <?= ($default_profile['education_level'] ?? '') === 'Postgraduate' ? 'selected' : '' ?>>Postgraduate</option>
                        <option value="PhD / Research" <?= ($default_profile['education_level'] ?? '') === 'PhD / Research' ? 'selected' : '' ?>>PhD / Research</option>
                        <option value="Vocational / Skill Courses" <?= ($default_profile['education_level'] ?? '') === 'Vocational / Skill Courses' ? 'selected' : '' ?>>Vocational / Skill Courses</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Course / Stream</label>
                    <input type="text" id="calc-course" class="form-control" value="<?= htmlspecialchars($default_profile['course'] ?? '') ?>" placeholder="e.g. BCA, Computer Science, Engineering">
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Social Category</label>
                        <select id="calc-category" class="form-control">
                            <option value="">Select Category</option>
                            <option value="General / Open" <?= ($default_profile['category'] ?? '') === 'General / Open' ? 'selected' : '' ?>>General / Open</option>
                            <option value="OBC" <?= ($default_profile['category'] ?? '') === 'OBC' ? 'selected' : '' ?>>OBC</option>
                            <option value="SC" <?= ($default_profile['category'] ?? '') === 'SC' ? 'selected' : '' ?>>SC</option>
                            <option value="ST" <?= ($default_profile['category'] ?? '') === 'ST' ? 'selected' : '' ?>>ST</option>
                            <option value="EWS" <?= ($default_profile['category'] ?? '') === 'EWS' ? 'selected' : '' ?>>EWS</option>
                            <option value="VJ/NT" <?= ($default_profile['category'] ?? '') === 'VJ/NT' ? 'selected' : '' ?>>VJ/NT</option>
                            <option value="SBC" <?= ($default_profile['category'] ?? '') === 'SBC' ? 'selected' : '' ?>>SBC</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Home State / Domicile</label>
                        <select id="calc-state" class="form-control">
                            <option value="">Select State / UT</option>
                            <?php foreach ($states as $state): ?>
                                <option value="<?= htmlspecialchars($state) ?>" <?= ($default_profile['state'] ?? '') === $state ? 'selected' : '' ?>><?= htmlspecialchars($state) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select id="calc-gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="all" <?= ($default_profile['gender'] ?? '') === 'all' ? 'selected' : '' ?>>All Candidates</option>
                        <option value="female" <?= ($default_profile['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="male" <?= ($default_profile['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Qualifying Percentage / Score</label>
                    <input type="number" id="calc-percentage" class="form-control" min="0" max="100" step="0.01" value="<?= ($default_profile['percentage'] ?? '') !== '' ? htmlspecialchars($default_profile['percentage']) : '' ?>" placeholder="Enter your percentage">
                </div>

                <div class="form-group">
                    <label class="form-label">Annual Family Income (₹)</label>
                    <input type="number" step="1" min="0" id="calc-income" class="form-control" value="<?= ($default_profile['family_income'] ?? '') !== '' ? htmlspecialchars($default_profile['family_income']) : '' ?>" placeholder="e.g. 350000" oninput="updateIncomeDisplay(this.value)">
                    <span id="income-preview-badge" style="display: <?= ($default_profile['family_income'] ?? '') !== '' ? 'block' : 'none' ?>; font-size: 0.8rem; color: var(--primary); font-weight: 700; margin-top: 4px;"></span>
                </div>

                <div class="calc-submit-area">
                    <button type="button" class="btn btn-primary btn-block" id="btn-run-match" style="padding: 14px; font-size: 1rem;">
                        ⚡ Recalculate Live Matches
                    </button>
                </div>
            </form>
        </div>

        <div class="calc-results-card">
            <div class="results-header-bar">
                <div>
                    <h2>Matched Opportunities (<span id="total-matches-count"><?= count($all_scholarships) ?></span>)</h2>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Ranked by compatibility percentage</p>
                </div>
            </div>

            <div class="calc-filter-pills">
                <button class="calc-pill-btn active" onclick="filterCalcFeed('all', this)">All Opportunities</button>
                <button class="calc-pill-btn" onclick="filterCalcFeed('eligible', this)">Strong Match (100%)</button>
                <button class="calc-pill-btn" onclick="filterCalcFeed('possible', this)">Possible Matches (&gt;50%)</button>
            </div>

            <div class="calc-cards-list" id="calc-feed-container">
                <?php foreach ($all_scholarships as $sch):
                    $eval = calculate_eligibility($default_profile, $sch);
                    $group = ($eval['is_eligible'] || $eval['score'] >= 90) ? 'eligible' : (($eval['score'] >= 50) ? 'possible' : 'not-eligible');
                ?>
                    <div class="calc-match-item" data-group="<?= $group ?>">
                        <div class="calc-item-top">
                            <div>
                                <h3 class="calc-item-title"><?= htmlspecialchars($sch['title']) ?></h3>
                                <div class="calc-item-provider">🏛️ <?= htmlspecialchars($sch['provider']) ?></div>
                            </div>
                            <span class="badge <?= $eval['badge_class'] ?>" style="font-size: 0.82rem; padding: 6px 14px;">
                                <?= $eval['status_label'] ?>
                            </span>
                        </div>

                        <div class="calc-item-meta-chips">
                            <span>🎓 <?= htmlspecialchars($sch['education_level']) ?></span>
                            <span>🏷️ <?= htmlspecialchars($sch['category']) ?></span>
                            <span>📍 <?= htmlspecialchars($sch['state']) ?></span>
                            <span style="color: var(--primary); font-weight: 700;">🌐 <?= htmlspecialchars($sch['application_portal'] ?? 'Official Portal') ?></span>
                        </div>

                        <div class="calc-item-foot">
                            <strong style="color: var(--success); font-size: 1.25rem;">₹<?= number_format($sch['amount'], 0) ?> <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: normal;">/ year</span></strong>
                            <a href="scholarship_detail.php?id=<?= $sch['scholarship_id'] ?>" class="btn btn-sm btn-primary" style="padding: 7px 18px;">
                                View Details & Docs →
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function updateIncomeDisplay(val) {
    const num = parseFloat(val) || 0;
    const badge = document.getElementById('income-preview-badge');

    if (val === '' || num <= 0) {
        badge.style.display = 'none';
        return;
    }

    badge.style.display = 'block';
    badge.innerText = `₹${num.toLocaleString('en-IN')} / year (${(num / 100000).toFixed(2)} Lakhs)`;
}

document.addEventListener('DOMContentLoaded', () => {
    const income = document.getElementById('calc-income');
    if (income && income.value !== '') {
        updateIncomeDisplay(income.value);
    }
});

function filterCalcFeed(group, btn) {
    document.querySelectorAll('.calc-pill-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    let visibleCount = 0;

    document.querySelectorAll('.calc-match-item').forEach(card => {
        if (group === 'all' || card.getAttribute('data-group') === group) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('total-matches-count').innerText = visibleCount;
}

document.getElementById('btn-run-match')?.addEventListener('click', async () => {
    const educationLevel = document.getElementById('calc-edu').value;
    const percentageValue = document.getElementById('calc-percentage').value;
    const incomeValue = document.getElementById('calc-income').value;

    if (!educationLevel || !percentageValue || !incomeValue) {
        showToast('Please enter your education level, percentage and family income.', 'warning');
        return;
    }

    const payload = {
        education_level: educationLevel,
        course: document.getElementById('calc-course').value,
        category: document.getElementById('calc-category').value,
        state: document.getElementById('calc-state').value,
        gender: document.getElementById('calc-gender').value,
        percentage: parseFloat(percentageValue),
        family_income: parseFloat(incomeValue)
    };

    showToast('Calculating matches across all rules...', 'info');

    try {
        const res = await fetch('api/check_eligibility.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data.success) {
            const container = document.getElementById('calc-feed-container');
            container.innerHTML = '';

            data.scholarships.forEach(item => {
                const s = item.scholarship;
                const e = item.eligibility;
                const grp = (e.is_eligible || e.score >= 90) ? 'eligible' : (e.score >= 50 ? 'possible' : 'not-eligible');
                const cardHtml = `
                    <div class="calc-match-item" data-group="${grp}">
                        <div class="calc-item-top">
                            <div>
                                <h3 class="calc-item-title">${escapeHtml(s.title)}</h3>
                                <div class="calc-item-provider">🏛️ ${escapeHtml(s.provider)}</div>
                            </div>
                            <span class="badge ${e.badge_class}" style="font-size: 0.82rem; padding: 6px 14px;">
                                ${e.status_label}
                            </span>
                        </div>
                        <div class="calc-item-meta-chips">
                            <span>🎓 ${escapeHtml(s.education_level)}</span>
                            <span>🏷️ ${escapeHtml(s.category)}</span>
                            <span>📍 ${escapeHtml(s.state)}</span>
                            <span style="color: var(--primary); font-weight: 700;">🌐 ${escapeHtml(s.application_portal || 'Official Portal')}</span>
                        </div>
                        <div class="calc-item-foot">
                            <strong style="color: var(--success); font-size: 1.25rem;">₹${Number(s.amount).toLocaleString('en-IN')} <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: normal;">/ year</span></strong>
                            <a href="scholarship_detail.php?id=${s.scholarship_id}" class="btn btn-sm btn-primary" style="padding: 7px 18px;">
                                View Details & Docs →
                            </a>
                        </div>
                    </div>
                `;
                container.innerHTML += cardHtml;
            });
            document.getElementById('total-matches-count').innerText = data.scholarships.length;
            showToast(`Calculated! Found ${data.scholarships.length} matched schemes.`, 'success');
        }
    } catch (err) {
        console.error(err);
        showToast('Something went wrong while calculating matches.', 'error');
    }
});

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>

<?php require_once 'includes/footer.php'; ?>