<?php
/**
 * API: Toggle Save / Bookmark Scholarship
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/auth_check.php';

if (!is_logged_in()) {
    echo json_encode(['status' => 'unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$scholarship_id = $input['scholarship_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$scholarship_id) {
    echo json_encode(['status' => 'error', 'message' => 'Scholarship ID required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT save_id FROM saved_scholarships WHERE user_id = ? AND scholarship_id = ?");
    $stmt->execute([$user_id, $scholarship_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        $del = $pdo->prepare("DELETE FROM saved_scholarships WHERE user_id = ? AND scholarship_id = ?");
        $del->execute([$user_id, $scholarship_id]);
        echo json_encode(['status' => 'removed']);
    } else {
        $ins = $pdo->prepare("INSERT INTO saved_scholarships (user_id, scholarship_id) VALUES (?, ?)");
        $ins->execute([$user_id, $scholarship_id]);
        echo json_encode(['status' => 'saved']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
