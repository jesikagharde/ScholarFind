<?php
/**
 * Smart Eligibility & Weighted Match Engine
 * Evaluates student profiles against scholarship criteria with incomplete profile intelligence.
 */

function normalize_category_for_match($cat) {
    $c = strtolower(trim($cat ?? 'all'));
    if ($c === 'general / open' || $c === 'general' || $c === 'open') return 'general';
    if ($c === 'vj/nt' || $c === 'vjnt' || $c === 'vj' || $c === 'nt') return 'vjnt';
    if ($c === 'sbc' || $c === 'special backward class') return 'sbc';
    if ($c === 'ews' || $c === 'economically weaker section') return 'ews';
    if ($c === 'obc') return 'obc';
    if ($c === 'sc') return 'sc';
    if ($c === 'st') return 'st';
    if ($c === 'minority') return 'minority';
    return $c;
}

function calculate_eligibility($student, $scholarship) {
    // 0. Check if student profile is substantially incomplete
    $missing_fields = [];
    if (empty($student['education_level']) || $student['education_level'] === 'All') {
        $missing_fields[] = 'Education Level';
    }
    if (empty($student['state']) || $student['state'] === 'All') {
        $missing_fields[] = 'Home State / Domicile';
    }
    if (empty($student['category']) || $student['category'] === 'All') {
        $missing_fields[] = 'Social Category';
    }
    if (!isset($student['percentage']) || (float)$student['percentage'] <= 0) {
        $missing_fields[] = 'Academic Percentage / Score';
    }
    if (!isset($student['family_income']) || (float)$student['family_income'] <= 0) {
        $missing_fields[] = 'Annual Family Income';
    }

    $is_incomplete = count($missing_fields) >= 3;

    $total_criteria = 0;
    $passed_criteria_count = 0;

    $passed_details = [];
    $failed_details = [];
    $pending_details = [];

    // 1. Education Level Check
    $total_criteria++;
    $sch_edu = strtolower(trim($scholarship['education_level'] ?? 'all'));
    $stu_edu = strtolower(trim($student['education_level'] ?? ''));
    if ($sch_edu === 'all' || $sch_edu === 'all education levels' || $sch_edu === $stu_edu) {
        $passed_criteria_count++;
        $passed_details[] = "Education Level: Meets requirement (" . htmlspecialchars($scholarship['education_level']) . ")";
    } elseif (empty($stu_edu)) {
        $pending_details[] = "Education Level: Requires " . htmlspecialchars($scholarship['education_level']) . " (Set in Profile)";
    } else {
        $failed_details[] = "Education Level: Requires " . htmlspecialchars($scholarship['education_level']) . " (Your profile: " . htmlspecialchars($student['education_level']) . ")";
    }

    // 2. Minimum Academic Percentage Check
    $total_criteria++;
    $min_pct = (float)($scholarship['minimum_percentage'] ?? 0);
    $stu_pct = (float)($student['percentage'] ?? 0);
    if ($min_pct <= 0) {
        $passed_criteria_count++;
        $passed_details[] = "Academic Score: No minimum percentage cutoff required";
    } elseif ($stu_pct > 0 && $stu_pct >= $min_pct) {
        $passed_criteria_count++;
        $passed_details[] = "Academic Score: Your {$stu_pct}% meets the required minimum of {$min_pct}%";
    } elseif ($stu_pct <= 0) {
        $pending_details[] = "Academic Score: Requires minimum {$min_pct}% (Enter your marks in Profile)";
    } else {
        $failed_details[] = "Academic Score: Requires minimum {$min_pct}% (Your profile: {$stu_pct}%)";
    }

    // 3. Family Income Ceiling Check
    $total_criteria++;
    $max_inc = !empty($scholarship['maximum_income']) ? (float)$scholarship['maximum_income'] : null;
    $stu_inc = (float)($student['family_income'] ?? 0);
    if ($max_inc === null) {
        $passed_criteria_count++;
        $passed_details[] = "Annual Income: No income ceiling for this scheme";
    } elseif ($stu_inc > 0 && $stu_inc <= $max_inc) {
        $passed_criteria_count++;
        $passed_details[] = "Annual Income: ₹" . number_format($stu_inc, 0) . " is within ceiling of ₹" . number_format($max_inc, 0);
    } elseif ($stu_inc <= 0) {
        $pending_details[] = "Annual Income: Requires family income ≤ ₹" . number_format($max_inc, 0) . " (Enter income in Profile)";
    } else {
        $failed_details[] = "Annual Income: Exceeds maximum ceiling of ₹" . number_format($max_inc, 0) . " (Your income: ₹" . number_format($stu_inc, 0) . ")";
    }

    // 4. Social Category / Reservation Check
    $total_criteria++;
    $sch_cat_norm = normalize_category_for_match($scholarship['category'] ?? 'all');
    $stu_cat_norm = normalize_category_for_match($student['category'] ?? '');
    
    // Check if scholarship allows multiple (e.g. SC, VJ/NT, SBC)
    $sch_cats = array_map('normalize_category_for_match', explode(',', $scholarship['category'] ?? 'all'));

    if ($sch_cat_norm === 'all' || in_array('all', $sch_cats) || (!empty($stu_cat_norm) && in_array($stu_cat_norm, $sch_cats))) {
        $passed_criteria_count++;
        $passed_details[] = "Category: Open / Eligible under " . htmlspecialchars($scholarship['category']) . " category";
    } elseif (empty($stu_cat_norm)) {
        $pending_details[] = "Category: Reserved for " . htmlspecialchars($scholarship['category']) . " (Select category in Profile)";
    } else {
        $failed_details[] = "Category: Reserved for " . htmlspecialchars($scholarship['category']) . " (Your category: " . htmlspecialchars($student['category'] ?? 'General / Open') . ")";
    }

    // 5. State / Region Domicile Check
    $total_criteria++;
    $sch_state = strtolower(trim($scholarship['state'] ?? 'all india'));
    $stu_state = strtolower(trim($student['state'] ?? ''));
    if ($sch_state === 'all india' || $sch_state === 'all' || $sch_state === 'all states' || (!empty($stu_state) && $sch_state === $stu_state)) {
        $passed_criteria_count++;
        $passed_details[] = "State Domicile: Valid for students residing in " . htmlspecialchars($scholarship['state']);
    } elseif (empty($stu_state)) {
        $pending_details[] = "State Domicile: Specific to " . htmlspecialchars($scholarship['state']) . " (Select State in Profile)";
    } else {
        $failed_details[] = "State Domicile: Reserved for residents of " . htmlspecialchars($scholarship['state']) . " (Your state: " . htmlspecialchars($student['state']) . ")";
    }

    // 6. Gender Eligibility Check
    $total_criteria++;
    $sch_gen = strtolower(trim($scholarship['gender_eligible'] ?? 'all'));
    $stu_gen = strtolower(trim($student['gender'] ?? 'all'));
    if ($sch_gen === 'all' || $sch_gen === $stu_gen || $stu_gen === 'all') {
        $passed_criteria_count++;
        $passed_details[] = "Gender: " . ucfirst($scholarship['gender_eligible']) . " candidates eligible";
    } else {
        $failed_details[] = "Gender: Specifically reserved for " . ucfirst($scholarship['gender_eligible']) . " candidates";
    }

    // Calculate Match Percentage Score
    $match_percentage = round(($passed_criteria_count / $total_criteria) * 100);
    $is_fully_eligible = (count($failed_details) === 0 && count($pending_details) === 0);

    // Classify verdict intelligently
    if ($is_incomplete && count($pending_details) >= 2) {
        $badge_class = 'badge-match-incomplete';
        $status_label = 'Profile Incomplete';
        $is_fully_eligible = false;
    } elseif ($match_percentage >= 90) {
        $badge_class = 'badge-match-100';
        $status_label = 'Strong Match • 100%';
    } elseif ($match_percentage >= 50) {
        $badge_class = 'badge-match-medium';
        $status_label = "Possible Match • {$match_percentage}%";
    } else {
        $badge_class = 'badge-match-low';
        $status_label = "Low Match • {$match_percentage}%";
    }

    return [
        'is_eligible'      => $is_fully_eligible,
        'is_incomplete'    => $is_incomplete,
        'score'            => $match_percentage,
        'status_label'     => $status_label,
        'badge_class'      => $badge_class,
        'passed_count'     => $passed_criteria_count,
        'total_count'      => $total_criteria,
        'passed_details'   => $passed_details,
        'failed_details'   => $failed_details,
        'pending_details'  => $pending_details,
        'missing_fields'   => $missing_fields,
    ];
}
