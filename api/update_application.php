<?php
/**
 * API: Update or Create Application Tracking Entry
 */

header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/auth_check.php';

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$scholarship_id = $input['scholarship_id'] ?? null;
$status = $input['status'] ?? 'applied';
$notes = $input['notes'] ?? '';
$user_id = $_SESSION['user_id'];

if (!$scholarship_id) {
    echo json_encode(['success' => false, 'message' => 'Scholarship ID required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO applications (user_id, scholarship_id, status, application_date, notes)
        VALUES (?, ?, ?, CURDATE(), ?)
        ON DUPLICATE KEY UPDATE status = VALUES(status), notes = VALUES(notes)
    ");
    $stmt->execute([$user_id, $scholarship_id, $status, $notes]);

    echo json_encode(['success' => true, 'status' => $status]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
