<?php
/**
 * API: Check Eligibility (AJAX Endpoint)
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/eligibility_engine.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT * FROM scholarships WHERE is_active = 1 ORDER BY amount DESC");
    $scholarships = $stmt->fetchAll();

    $results = [];

    foreach ($scholarships as $sch) {
        $eligibility = calculate_eligibility($input, $sch);
        $results[] = [
            'scholarship' => $sch,
            'eligibility' => $eligibility,
        ];
    }

    usort($results, function($a, $b) {
        if ($b['eligibility']['score'] === $a['eligibility']['score']) {
            return $b['scholarship']['amount'] <=> $a['scholarship']['amount'];
        }
        return $b['eligibility']['score'] <=> $a['eligibility']['score'];
    });

    echo json_encode(['success' => true, 'scholarships' => $results]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
